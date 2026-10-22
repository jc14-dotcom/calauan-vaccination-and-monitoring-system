<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VaccinationSchedule;
use App\Models\Patient;
use App\Models\Parents;
use App\Models\Barangay;
use App\Notifications\VaccinationScheduleCreated;
use App\Notifications\VaccinationScheduleCancelled;
use App\Services\Notification\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VaccinationScheduleController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    /**
     * Display vaccination schedule management page
     */
    public function index()
    {
        $healthWorker = $this->getHealthWorker();
        
        // Auto-complete past schedules that were not explicitly completed or cancelled
        // This ensures all past vaccination days show as "Completed" instead of "Past"
        // Logic: If the date has passed and status is still 'scheduled' or 'active', mark as 'completed'
        VaccinationSchedule::where('vaccination_date', '<', Carbon::today('Asia/Manila'))
            ->whereIn('status', ['scheduled', 'active'])
            ->update(['status' => 'completed']);
        
        // Auto-delete old schedules older than 3 months to keep the table clean
        // Only delete completed schedules (cancelled ones are kept for audit trail)
        $threeMonthsAgo = Carbon::today('Asia/Manila')->subMonths(3);
        VaccinationSchedule::where('vaccination_date', '<', $threeMonthsAgo)
            ->where('status', 'completed')
            ->delete();
        
        // Build query for upcoming schedules
        $upcomingQuery = VaccinationSchedule::with('healthWorker')
            ->where('vaccination_date', '>=', Carbon::today());
        
        // Build query for past schedules (only show last 3 months, paginated)
        $pastQuery = VaccinationSchedule::with('healthWorker')
            ->where('vaccination_date', '<', Carbon::today())
            ->where('vaccination_date', '>=', $threeMonthsAgo)
            // Only show completed schedules in past vaccination days table
            // To include cancelled schedules, comment out the line below
            ->where('status', 'completed');
            // DISABLED: Include cancelled schedules in past vaccination days
            // ->whereIn('status', ['completed', 'cancelled']);
        
        // For barangay workers, filter schedules differently:
        // - Upcoming: Show their barangay + RHU/All Barangays (so they see upcoming RHU events)
        // - Past: Show ONLY their barangay (they don't need to see past RHU events)
        if ($healthWorker && !$healthWorker->isRHU()) {
            $barangayName = $healthWorker->getAssignedBarangayName();
            // Upcoming schedules: include RHU/All Barangays and RHU (Health Center)
            $upcomingQuery->where(function($q) use ($barangayName) {
                $q->where('barangay', $barangayName)
                  ->orWhere('barangay', 'RHU/All Barangays')
                  ->orWhere('barangay', 'RHU (Health Center)');
            });
            // Past schedules: ONLY their barangay (no RHU)
            $pastQuery->where('barangay', $barangayName);
        }
        
        // Get upcoming schedules (earliest first)
        $upcomingSchedules = $upcomingQuery->orderBy('vaccination_date', 'asc')->get();

        // Get past schedules with pagination (latest first, 20 per page)
        $pastSchedules = $pastQuery->orderBy('vaccination_date', 'desc')->paginate(20);
        
        // Get schedulable barangays for dropdown (based on health worker access)
        $schedulableBarangays = $healthWorker ? $healthWorker->getSchedulableBarangays() : Barangay::getSchedulableNames();

        return view('health_worker.vaccination_schedule', compact('upcomingSchedules', 'pastSchedules', 'schedulableBarangays', 'healthWorker'));
    }

    /**
     * Store a new vaccination schedule
     */
    public function store(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        $request->validate([
            'vaccination_date' => 'required|date|after_or_equal:today',
            'barangay' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Verify health worker can create schedules for this barangay
        // RHU can create for any barangay, barangay workers only for their own
        if ($healthWorker && !$healthWorker->isRHU()) {
            $schedulableBarangays = $healthWorker->getSchedulableBarangays();
            if (!in_array($request->barangay, $schedulableBarangays) && $request->barangay !== 'RHU/All Barangays') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'barangay' => 'You do not have permission to create schedules for this barangay.'
                    ]);
            }
        }

        // Check for duplicate schedule (same date and barangay) that is not cancelled or deleted
        $existingSchedule = VaccinationSchedule::where('vaccination_date', $request->vaccination_date)
            ->where('barangay', $request->barangay)
            ->whereIn('status', ['scheduled', 'active', 'completed'])
            ->first();

        if ($existingSchedule) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'vaccination_date' => 'A vaccination schedule for ' . $request->barangay . ' on ' . Carbon::parse($request->vaccination_date)->format('M d, Y') . ' already exists. Please cancel or delete the existing schedule first.'
                ]);
        }

        // Determine initial status - if date is today, set as 'active', otherwise 'scheduled'
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $todayPHT = Carbon::today('Asia/Manila');
        $scheduleDatePHT = Carbon::parse($request->vaccination_date);
        $isToday = $scheduleDatePHT->isSameDay($todayPHT);
        $initialStatus = $isToday ? 'active' : 'scheduled';
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // $isToday = Carbon::parse($request->vaccination_date)->isToday();
        // $initialStatus = $isToday ? 'active' : 'scheduled';
        
        $schedule = VaccinationSchedule::create([
            'vaccination_date' => $request->vaccination_date,
            'barangay' => $request->barangay,
            'status' => $initialStatus,
            'notes' => $request->notes,
            'created_by' => Auth::guard('health_worker')->id(),
        ]);

        // Send notifications to parents ONLY if vaccination is today, tomorrow, or 3 days away
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $daysUntil = Carbon::today('Asia/Manila')->diffInDays($schedule->vaccination_date, false);
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // $daysUntil = Carbon::today()->diffInDays($schedule->vaccination_date, false);
        
        // DEBUG: Uncomment for debugging
        // Log::info("Schedule created, checking notification timing", [
        //     'schedule_id' => $schedule->id,
        //     'barangay' => $schedule->barangay,
        //     'vaccination_date' => $schedule->vaccination_date,
        //     'days_until' => $daysUntil
        // ]);
        
        if ($daysUntil == 0 || $daysUntil == 1 || $daysUntil == 3) {
            // Send immediate notification (today, 1 day before, or 3 days before)
            $this->notifyParentsAboutNewSchedule($schedule);
            return redirect()->route('vaccination_schedule.index')
                ->with('success', 'Vaccination schedule created successfully! Notifications sent to parents.');
        } else {
            // Don't send notification now - will be sent by scheduled command
            return redirect()->route('vaccination_schedule.index')
                ->with('success', 'Vaccination schedule created successfully! Reminders will be sent automatically 3 days and 1 day before the vaccination date.');
        }
    }

    /**
     * Cancel a vaccination schedule
     * Sends notifications to parents notifying them that the schedule has been cancelled
     */
    public function cancel(Request $request, $id)
    {
        $healthWorker = $this->getHealthWorker();
        
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $schedule = VaccinationSchedule::findOrFail($id);
        
        // Verify health worker can cancel this schedule
        if ($healthWorker && !$healthWorker->isRHU()) {
            if (!$healthWorker->canAccessBarangay($schedule->barangay) && $schedule->barangay !== 'RHU/All Barangays') {
                return redirect()->route('vaccination_schedule.index')
                    ->with('error', 'You do not have permission to cancel this schedule.');
            }
        }

        // Only allow cancelling if status is scheduled or active
        if (!$schedule->canBeCancelled()) {
            return redirect()->route('vaccination_schedule.index')
                ->with('error', 'This schedule cannot be cancelled.');
        }

        // Update schedule status to cancelled
        $schedule->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(),
        ]);

        // Send cancellation notifications to parents
        $this->notifyParentsAboutCancellation($schedule);
        
        return redirect()->route('vaccination_schedule.index')
            ->with('success', 'Vaccination schedule cancelled successfully. Notifications sent to affected parents.');
    }

    /**
     * Delete a vaccination schedule (permanent removal)
     * NOTE: This does NOT send notifications - use only for mistakes/duplicates
     * For actual cancellations that parents need to know about, use cancel() instead
     */
    public function destroy($id)
    {
        $healthWorker = $this->getHealthWorker();
        $schedule = VaccinationSchedule::findOrFail($id);
        
        // Verify health worker can delete this schedule
        if ($healthWorker && !$healthWorker->isRHU()) {
            if (!$healthWorker->canAccessBarangay($schedule->barangay) && $schedule->barangay !== 'RHU/All Barangays') {
                return redirect()->route('vaccination_schedule.index')
                    ->with('error', 'You do not have permission to delete this schedule.');
            }
        }

        // Treat delete as cancellation - notify parents before deleting
        if ($schedule->status !== 'cancelled') {
            // Set cancellation reason for notifications
            $schedule->cancellation_reason = 'Vaccination schedule has been removed by health center';
            
            // Send cancellation notifications to parents
            $this->notifyParentsAboutCancellation($schedule);
        }

        // Delete the schedule
        $schedule->delete();
        
        return redirect()->route('vaccination_schedule.index')
            ->with('success', 'Vaccination schedule deleted and parents notified!');
    }

    /**
     * Get today's active schedules (for vaccination status page)
     */
    public function getTodaySchedules()
    {
        $todaySchedules = VaccinationSchedule::today()
            ->whereIn('status', ['scheduled', 'active'])
            ->get();

        return $todaySchedules;
    }

    /**
     * Activate today's schedules (called automatically)
     */
    public function activateTodaySchedules()
    {
        VaccinationSchedule::today()
            ->where('status', 'scheduled')
            ->update(['status' => 'active']);
    }

    /**
     * Complete schedules (can be called at end of day)
     */
    public function completeSchedule($id)
    {
        $schedule = VaccinationSchedule::findOrFail($id);
        
        if ($schedule->status === 'active' || $schedule->isToday()) {
            $schedule->update(['status' => 'completed']);
            return redirect()->route('vaccination_schedule.index')
                ->with('success', 'Vaccination schedule marked as completed!');
        }

        return redirect()->route('vaccination_schedule.index')
            ->with('error', 'Only active or today\'s schedules can be completed.');
    }

    /**
     * Notify parents about new vaccination schedule
     * 
     * Uses Parents table directly for efficient querying and to avoid duplicate notifications.
     * Each parent receives ONE notification regardless of how many children they have.
     */
    protected function notifyParentsAboutNewSchedule(VaccinationSchedule $schedule)
    {
        try {
            // Check if it's for RHU (Health Center) or RHU/All Barangays - notify ALL parents
            $isRHU = in_array($schedule->barangay, ['RHU (Health Center)', 'RHU/All Barangays']);
            
            // Log notification attempt for debugging
            Log::info("Starting vaccination schedule notification", [
                'schedule_id' => $schedule->id,
                'barangay' => $schedule->barangay,
                'is_rhu' => $isRHU
            ]);
            
            // Query Parents table directly (indexed on barangay for performance)
            $query = Parents::query();
            
            // If NOT RHU, filter by specific barangay (case-insensitive)
            if (!$isRHU) {
                $query->whereRaw('LOWER(barangay) = ?', [strtolower($schedule->barangay)]);
            }
            
            $parents = $query->get();
            
            Log::info("Parents found for notification", [
                'schedule_id' => $schedule->id,
                'barangay' => $schedule->barangay,
                'parents_count' => $parents->count(),
                'is_rhu' => $isRHU,
                'parent_ids' => $parents->pluck('id')->toArray()
            ]);

            $smsService = app(SmsService::class);
            $notificationsSent = 0;
            $smsSent = 0;

            foreach ($parents as $parent) {
                // Send database notification (queued for async processing)
                $parent->notify(new VaccinationScheduleCreated($schedule));
                $notificationsSent++;

                // Send SMS if enabled and parent has contact number
                // Wrap in try-catch to prevent SMS errors from breaking other notifications
                if ($smsService->isEnabled() && config('sms.triggers.vaccination_schedule_created') && $parent->contact_number) {
                    try {
                        $notification = new VaccinationScheduleCreated($schedule);
                        $smsMessage = $notification->toSms($parent);
                        
                        $result = $smsService->send(
                            $parent->contact_number,
                            $smsMessage,
                            $parent,
                            $parent->notifications()->latest()->first()?->id
                        );

                        if ($result['success']) {
                            $smsSent++;
                        } else {
                            Log::warning("SMS sending returned failure for parent {$parent->id}", [
                                'contact' => $parent->contact_number,
                                'result' => $result
                            ]);
                        }
                    } catch (\Exception $smsError) {
                        Log::warning("SMS sending failed for parent {$parent->id}: " . $smsError->getMessage());
                        // Continue with other notifications even if SMS fails
                    }
                }
            }

            Log::info("Vaccination schedule notifications completed", [
                'schedule_id' => $schedule->id,
                'barangay' => $schedule->barangay,
                'notifications_sent' => $notificationsSent,
                'sms_sent' => $smsSent,
                'sms_enabled' => $smsService->isEnabled(),
                'sms_trigger_enabled' => config('sms.triggers.vaccination_schedule_created'),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send vaccination schedule notifications: " . $e->getMessage(), [
                'schedule_id' => $schedule->id ?? null,
                'exception' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Notify parents about schedule cancellation
     * 
     * Uses Parents table directly for efficient querying and to avoid duplicate notifications.
     * Each parent receives ONE notification regardless of how many children they have.
     */
    protected function notifyParentsAboutCancellation(VaccinationSchedule $schedule)
    {
        try {
            // Check if it's for RHU (Health Center) or RHU/All Barangays - notify ALL parents
            $isRHU = in_array($schedule->barangay, ['RHU (Health Center)', 'RHU/All Barangays']);
            
            Log::info("Starting vaccination cancellation notification", [
                'schedule_id' => $schedule->id,
                'barangay' => $schedule->barangay,
                'is_rhu' => $isRHU
            ]);
            
            // Query Parents table directly (indexed on barangay for performance)
            $query = Parents::query();
            
            // If NOT RHU, filter by specific barangay (case-insensitive)
            if (!$isRHU) {
                $query->whereRaw('LOWER(barangay) = ?', [strtolower($schedule->barangay)]);
            }
            
            $parents = $query->get();

            $smsService = app(SmsService::class);
            $notificationsSent = 0;
            $smsSent = 0;

            foreach ($parents as $parent) {
                // Send database notification (queued for async processing)
                $parent->notify(new VaccinationScheduleCancelled(
                    $schedule, 
                    $schedule->cancellation_reason
                ));
                $notificationsSent++;

                // Send SMS if enabled (cancellations are important, so check separate trigger)
                // Wrap in try-catch to prevent SMS errors from breaking other notifications
                if ($smsService->isEnabled() && config('sms.triggers.vaccination_schedule_cancelled') && $parent->contact_number) {
                    try {
                        $notification = new VaccinationScheduleCancelled(
                            $schedule, 
                            $schedule->cancellation_reason
                        );
                        $smsMessage = $notification->toSms($parent);
                        
                        $result = $smsService->send(
                            $parent->contact_number,
                            $smsMessage,
                            $parent,
                            $parent->notifications()->latest()->first()?->id
                        );

                        if ($result['success']) {
                            $smsSent++;
                        }
                    } catch (\Exception $smsError) {
                        Log::warning("SMS sending failed for parent {$parent->id}: " . $smsError->getMessage());
                        // Continue with other notifications even if SMS fails
                    }
                }
            }

            // DEBUG: Log::info("Schedule cancellation notifications sent", [
            //     'schedule_id' => $schedule->id,
            //     'barangay' => $schedule->barangay,
            //     'notifications_sent' => $notificationsSent,
            //     'sms_sent' => $smsSent,
            // ]);

        } catch (\Exception $e) {
            Log::error("Failed to send cancellation notifications: " . $e->getMessage());
        }
    }
}

