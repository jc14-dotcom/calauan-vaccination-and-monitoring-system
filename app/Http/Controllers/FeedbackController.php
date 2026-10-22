<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    public function show($id)
    {
        try {
            $healthWorker = $this->getHealthWorker();
            $feedback = Feedback::with('patient')->findOrFail($id);
            
            // Verify health worker can access this feedback's barangay
            if ($healthWorker && !$healthWorker->isRHU()) {
                if ($feedback->barangay && !$healthWorker->canAccessBarangay($feedback->barangay)) {
                    return response()->json(['error' => 'Access denied'], 403);
                }
            }
            
            return response()->json([
                'id' => $feedback->id,
                'patient_name' => $feedback->patient ? $feedback->patient->name : 'Unknown Patient',
                'content' => $feedback->content,
                'created_at' => $feedback->created_at->format('F j, Y')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Feedback not found'], 404);
        }
    }

    public function loadMore(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        $page = $request->input('page', 1);
        $barangay = $request->input('barangay');
        $order = $request->input('order', 'desc');

        // Get the feedbacks query with filters
        $query = Feedback::with('patient');
        
        // For barangay workers, always filter by their barangay
        if ($healthWorker && !$healthWorker->isRHU()) {
            $assignedBarangay = $healthWorker->getAssignedBarangayName();
            $query->where('barangay', $assignedBarangay);
        } elseif ($barangay) {
            // RHU admin can filter by any barangay
            $query->whereHas('patient', function ($q) use ($barangay) {
                $q->where('barangay', $barangay);
            });
        }

        // Apply sort order
        $query->orderBy('created_at', $order);

        // Calculate offset based on page
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get feedbacks for current page
        $feedbacks = $query->skip($offset)->take($perPage)->get();

        // Format date for each feedback
        $feedbacks->each(function ($feedback) {
            $feedback->formatted_date = $feedback->created_at->format('F j, Y');
        });

        // Check if there are more feedbacks to load
        $hasMore = $query->skip($offset + $perPage)->exists();

        return response()->json([
            'feedbacks' => $feedbacks,
            'hasMore' => $hasMore
        ]);
    }

    /**
     * Get feedback analytics for dashboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnalytics(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Get month and year from request
        $month = $request->input('month');
        $year = $request->input('year', now()->year);
        $selectedBarangay = $request->input('barangay');

        // Build query to get feedback from the specified year
        $query = Feedback::whereYear('created_at', $year);

        // Filter by month if provided (not "All Months")
        if ($month && $month !== '') {
            $query->whereMonth('created_at', $month);
        }

        // For barangay workers, always filter by their barangay
        if ($healthWorker && !$healthWorker->isRHU()) {
            $assignedBarangay = $healthWorker->getAssignedBarangayName();
            $query->where('barangay', $assignedBarangay);
        } elseif ($selectedBarangay) {
            // RHU admin can filter by any barangay
            $query->where('barangay', $selectedBarangay);
        }

        // Get feedback records
        $feedbacks = $query->get();

        // Initialize analysis structure for the 5 questions
        $analysis = [
            'Q1' => ['OO' => 0, 'HINDI' => 0],
            'Q2' => ['OO' => 0, 'HINDI' => 0],
            'Q3' => ['OO' => 0, 'HINDI' => 0],
            'Q4' => ['OO' => 0, 'HINDI' => 0],
            'Q5' => ['OO' => 0, 'HINDI' => 0],
        ];

        // Process each feedback entry
        foreach ($feedbacks as $feedback) {
            // Decode the content JSON
            $content = json_decode($feedback->content, true);

            if (is_array($content)) {
                // Count responses for each question
                if (isset($content['question1'])) {
                    $analysis['Q1'][$content['question1']]++;
                }

                if (isset($content['question2'])) {
                    $analysis['Q2'][$content['question2']]++;
                }

                if (isset($content['question3'])) {
                    $analysis['Q3'][$content['question3']]++;
                }

                if (isset($content['question4'])) {
                    $analysis['Q4'][$content['question4']]++;
                }

                if (isset($content['question5'])) {
                    $analysis['Q5'][$content['question5']]++;
                }
            }
        }

        // Calculate percentages for each question
        foreach ($analysis as $question => $responses) {
            $total = $responses['OO'] + $responses['HINDI'];
            if ($total > 0) {
                $analysis[$question]['OO_percent'] = round(($responses['OO'] / $total) * 100);
                $analysis[$question]['HINDI_percent'] = round(($responses['HINDI'] / $total) * 100);
            } else {
                $analysis[$question]['OO_percent'] = 0;
                $analysis[$question]['HINDI_percent'] = 0;
            }
            $analysis[$question]['total'] = $total;
        }

        // Get available months with feedback data for archiving
        $availableMonths = $this->getAvailableMonths();

        // Build current period text
        $currentPeriodText = '';
        if ($month && $month !== '') {
            $currentPeriodText = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        } else {
            $currentPeriodText = 'All Months ' . $year;
        }

        return response()->json([
            'analysis' => $analysis,
            'question_labels' => [
                'Q1' => 'MADALI BA NINYONG NAHANAP ANG HEALTH CENTER AT MAY NAKITA BA KAYONG KARATULA NG DIREKSYON?',
                'Q2' => 'MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?',
                'Q3' => 'MAGALANG, MABAIT, AT MAAYOS BA ANG SERBISYO NG HEALTH WORKER NA NAGBIGAY NG BAKUNA?',
                'Q4' => 'NAIPALIWANAG BA SA INYO NG HEALTH WORKER ANG TUNGKOL SA BAKUNA AT KAILAN ANG SUSUNOD NA SCHEDULE?',
                'Q5' => 'NASIYAHAN BA KAYO SA KABUUANG SERBISYO NG HEALTH CENTER SA ARAW NA ITO?'
            ],
            'current_month' => $currentPeriodText,
            'available_months' => $availableMonths,
            'total_responses' => $feedbacks->count()
        ]);
    }

    /**
     * Get months with available feedback data
     *
     * @return array
     */
    private function getAvailableMonths()
    {
        $months = Feedback::selectRaw('DISTINCT YEAR(created_at) as year, MONTH(created_at) as month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $date = \Carbon\Carbon::create($item->year, $item->month, 1);
                return [
                    'value' => $date->format('Y-m'),
                    'label' => $date->format('F Y')
                ];
            });

        return $months;
    }
}
