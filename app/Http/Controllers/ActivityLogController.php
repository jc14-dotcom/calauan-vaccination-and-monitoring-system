<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs for admin/health workers.
     */
    public function index(Request $request)
    {
        // Eager load causer and subject
        // Note: We can't eager load causer.barangay because the causer can be
        // different model types (HealthWorker, Parents) and only HealthWorker has 
        // the barangay relationship. The barangay will be loaded individually when needed.
        $query = Activity::with([
            'causer',
            'subject',
        ])
        // Exclude parent account logs (they should not appear in activity log)
        ->where('log_name', '!=', 'parent')
        ->orderBy('created_at', 'desc');

        // Filter by log name (model type)
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by causer (who performed the action)
        if ($request->filled('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }

        // Filter by specific causer ID (for viewing user's activity from Account Management)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
            // Also set causer_type to HealthWorker if coming from Account Management
            if (!$request->filled('causer_type')) {
                $query->where('causer_type', 'App\\Models\\HealthWorker');
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }

        // Search in description or properties
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%");
            });
        }

        // Optimized pagination - 50 records per page for better performance
        // Use simplePaginate for large datasets if total count is not needed
        $perPage = $request->input('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100]) ? $perPage : 50;
        
        $activities = $query->paginate($perPage)->withQueryString();

        // Cache unique log names and events (they rarely change)
        // Exclude 'parent' from log names as parent activities should not be logged
        $logNames = cache()->remember('activity_log_names', 3600, function () {
            return Activity::select('log_name')
                ->distinct()
                ->where('log_name', '!=', 'parent')
                ->pluck('log_name');
        });
        
        $events = cache()->remember('activity_log_events', 3600, function () {
            return Activity::select('event')->distinct()->whereNotNull('event')->pluck('event');
        });

        // Statistics - cache for longer and use optimized queries
        $stats = [
            'total' => cache()->remember('activity_stats_total', 300, function () {
                return Activity::count();
            }),
            'today' => cache()->remember('activity_stats_today_' . Carbon::today('Asia/Manila')->format('Y-m-d'), 60, function () {
                return Activity::whereDate('created_at', Carbon::today('Asia/Manila'))->count();
            }),
            'this_week' => cache()->remember('activity_stats_week_' . Carbon::now('Asia/Manila')->startOfWeek()->format('Y-m-d'), 300, function () {
                return Activity::whereBetween('created_at', [
                    Carbon::now('Asia/Manila')->startOfWeek(),
                    Carbon::now('Asia/Manila')->endOfWeek()
                ])->count();
            }),
        ];

        // Get the filtered user info if filtering by causer_id
        $filteredUser = null;
        if ($request->filled('causer_id')) {
            $filteredUser = \App\Models\HealthWorker::find($request->causer_id);
        }

        return view('health_worker.activity-log', compact('activities', 'logNames', 'events', 'stats', 'filteredUser'));
    }

    /**
     * Get activity log details via AJAX.
     */
    public function show($id)
    {
        $activity = Activity::with('causer', 'subject')->findOrFail($id);
        
        return response()->json([
            'id' => $activity->id,
            'description' => $activity->description,
            'log_name' => $activity->log_name,
            'event' => $activity->event,
            'causer' => $this->formatCauser($activity),
            'subject' => $this->formatSubject($activity),
            'properties' => $activity->properties->toArray(),
            'created_at' => $activity->created_at->timezone('Asia/Manila')->format('M d, Y h:i:s A'),
        ]);
    }

    /**
     * Format causer for display.
     */
    private function formatCauser(Activity $activity): string
    {
        if (!$activity->causer) {
            return 'System';
        }

        $causer = $activity->causer;
        $type = class_basename($activity->causer_type);
        
        // For health workers, show display name (name or username) + barangay
        if ($type === 'HealthWorker') {
            $name = $causer->name ?? $causer->username;
            if ($causer->barangay_id && $causer->barangay) {
                return "{$name} ({$causer->barangay->name})";
            }
            return "{$name} (RHU Admin)";
        }
        
        return $causer->name ?? $causer->username ?? "{$type} #{$causer->id}";
    }

    /**
     * Format subject for display.
     */
    private function formatSubject(Activity $activity): string
    {
        if (!$activity->subject) {
            // Try to get subject info from properties if the subject was deleted
            $properties = $activity->properties;
            if ($properties->has('old') && isset($properties['old']['name'])) {
                return $properties['old']['name'] . ' (deleted)';
            }
            return 'N/A';
        }

        $subject = $activity->subject;
        $type = class_basename($activity->subject_type);
        
        // Format based on model type
        switch ($type) {
            case 'Patient':
                return $subject->name;
            case 'Vaccine':
                return $subject->vaccine_name ?? "Vaccine #{$subject->id}";
            case 'VaccinationSchedule':
                $date = $subject->vaccination_date?->format('M d, Y') ?? 'Unknown date';
                $barangay = $subject->barangay ?? 'Unknown';
                return "Schedule: {$date} - {$barangay}";
            case 'PatientGrowthRecord':
                $patientName = $subject->patient?->name ?? 'Unknown patient';
                return "Growth record for {$patientName}";
            case 'VaccinationTransaction':
                $patientName = $subject->patient?->name ?? 'Unknown patient';
                $vaccineName = $subject->vaccine?->name ?? 'Unknown vaccine';
                return "{$patientName} - {$vaccineName}";
            case 'Barangay':
                return "Barangay: {$subject->name}";
            case 'HealthWorker':
                return "Health Worker: " . ($subject->name ?? $subject->username);
            default:
                return $subject->name ?? $subject->username ?? "{$type} #{$subject->id}";
        }
    }

    /**
     * Export activity logs to CSV.
     */
    public function export(Request $request)
    {
        $query = Activity::with('causer', 'subject')
            // Exclude parent account logs
            ->where('log_name', '!=', 'parent')
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }

        $activities = $query->limit(5000)->get();

        $filename = 'activity_log_' . Carbon::now('Asia/Manila')->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Date/Time', 'Event', 'Log Type', 'Description', 'User', 'Changes']);
            
            foreach ($activities as $activity) {
                $changes = '';
                if ($activity->properties->has('attributes')) {
                    $changes = json_encode($activity->properties->get('attributes'));
                }
                
                fputcsv($file, [
                    $activity->created_at->timezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    $activity->event ?? 'N/A',
                    $activity->log_name,
                    $activity->description,
                    $this->formatCauser($activity),
                    $changes,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Manually clean up old activity logs.
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 365);
        
        $deleted = Activity::where('created_at', '<', Carbon::now('Asia/Manila')->subDays($days))->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} activity log entries older than {$days} days.",
            'deleted_count' => $deleted,
        ]);
    }
}
