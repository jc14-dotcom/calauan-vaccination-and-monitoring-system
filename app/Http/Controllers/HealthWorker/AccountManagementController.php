<?php

namespace App\Http\Controllers\HealthWorker;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\HealthWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountManagementController extends Controller
{
    /**
     * Maximum number of active barangay workers per barangay.
     */
    const MAX_WORKERS_PER_BARANGAY = 5;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $worker = Auth::guard('health_worker')->user();
            if (!$worker || !$worker->isRHU()) {
                abort(403, 'Access restricted to RHU Admins only.');
            }
            return $next($request);
        });
    }

    /**
     * Display list of all health worker accounts.
     */
    public function index(Request $request)
    {
        $query = HealthWorker::with('barangay', 'creator')
            ->leftJoin('barangays', 'health_workers.barangay_id', '=', 'barangays.id')
            ->select('health_workers.*')
            // Sort: RHU Admins first, then Barangay Workers sorted by barangay name
            ->orderByRaw('CASE WHEN health_workers.barangay_id IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('CASE 
                WHEN health_workers.barangay_id IS NULL THEN LOWER(health_workers.name) 
                ELSE LOWER(barangays.name) 
            END ASC')
            ->orderByRaw('LOWER(health_workers.name) ASC');

        // Filter by account type
        if ($request->filled('type')) {
            if ($request->type === 'rhu') {
                $query->whereNull('barangay_id');
            } elseif ($request->type === 'barangay') {
                $query->whereNotNull('barangay_id');
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by barangay
        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('health_workers.username', 'like', "%{$search}%")
                    ->orWhere('health_workers.name', 'like', "%{$search}%")
                    ->orWhere('health_workers.email', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate(15)->withQueryString();
        $barangays = Barangay::active()->orderBy('name')->get();

        // Get worker counts per barangay for display
        $workerCounts = HealthWorker::whereNotNull('barangay_id')
            ->where('is_active', true)
            ->selectRaw('barangay_id, COUNT(*) as count')
            ->groupBy('barangay_id')
            ->pluck('count', 'barangay_id')
            ->toArray();

        // Statistics
        $stats = [
            'total' => HealthWorker::count(),
            'active' => HealthWorker::where('is_active', true)->count(),
            'rhu_admins' => HealthWorker::whereNull('barangay_id')->where('is_active', true)->count(),
            'barangay_workers' => HealthWorker::whereNotNull('barangay_id')->where('is_active', true)->count(),
        ];

        return view('health_worker.accounts.index', compact(
            'accounts',
            'barangays',
            'workerCounts',
            'stats'
        ));
    }

    /**
     * AJAX endpoint: return filtered accounts as HTML partials.
     */
    public function load(Request $request)
    {
        $query = HealthWorker::with('barangay', 'creator')
            ->leftJoin('barangays', 'health_workers.barangay_id', '=', 'barangays.id')
            ->select('health_workers.*')
            ->orderByRaw('CASE WHEN health_workers.barangay_id IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('CASE 
                WHEN health_workers.barangay_id IS NULL THEN LOWER(health_workers.name) 
                ELSE LOWER(barangays.name) 
            END ASC')
            ->orderByRaw('LOWER(health_workers.name) ASC');

        if ($request->filled('type')) {
            if ($request->type === 'rhu') {
                $query->whereNull('barangay_id');
            } elseif ($request->type === 'barangay') {
                $query->whereNotNull('barangay_id');
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', $request->barangay_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('health_workers.username', 'like', "%{$search}%")
                    ->orWhere('health_workers.name', 'like', "%{$search}%")
                    ->orWhere('health_workers.email', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate(15)->withQueryString();

        $tableHtml = view('health_worker.accounts._table_rows', compact('accounts'))->render();
        $paginationHtml = view('health_worker.accounts._pagination', compact('accounts'))->render();

        return response()->json([
            'tableHtml' => $tableHtml,
            'paginationHtml' => $paginationHtml,
            'total' => $accounts->total(),
        ]);
    }

    /**
     * Show form to create a new account.
     */
    public function create()
    {
        $barangays = Barangay::active()->orderBy('name')->get();

        // Get worker counts per barangay
        $workerCounts = HealthWorker::whereNotNull('barangay_id')
            ->where('is_active', true)
            ->selectRaw('barangay_id, COUNT(*) as count')
            ->groupBy('barangay_id')
            ->pluck('count', 'barangay_id')
            ->toArray();

        return view('health_worker.accounts.create', compact('barangays', 'workerCounts'));
    }

    /**
     * Store a new account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type' => 'required|in:rhu,barangay',
            'barangay_id' => 'required_if:account_type,barangay|nullable|exists:barangays,id',
            'username' => 'required|string|min:3|max:50|unique:health_workers,username|regex:/^[a-z0-9_]+$/',
            'email' => 'nullable|email|max:255|unique:health_workers,email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|regex:/^09\d{9}$/',
        ], [
            'username.regex' => 'Username can only contain lowercase letters, numbers, and underscores.',
            'email.regex' => 'Please enter a valid email address (e.g., name@gmail.com).',
            'contact_number.regex' => 'Contact number must start with 09 and be exactly 11 digits.',
        ]);

        // Check barangay worker limit
        if ($request->account_type === 'barangay' && $request->barangay_id) {
            $currentCount = HealthWorker::where('barangay_id', $request->barangay_id)
                ->where('is_active', true)
                ->count();

            if ($currentCount >= self::MAX_WORKERS_PER_BARANGAY) {
                $barangay = Barangay::find($request->barangay_id);
                return back()->withInput()->withErrors([
                    'barangay_id' => "Maximum limit of " . self::MAX_WORKERS_PER_BARANGAY . " active workers reached for {$barangay->name}. Please deactivate an existing account first.",
                ]);
            }
        }

        // Generate password
        $password = $this->generatePassword();

        // Create account
        $account = HealthWorker::create([
            'username' => $validated['username'],
            'password' => Hash::make($password),
            'email' => $validated['email'] ?? null,
            'name' => $validated['name'],
            'contact_number' => $validated['contact_number'] ?? null,
            'barangay_id' => $request->account_type === 'barangay' ? $validated['barangay_id'] : null,
            'is_active' => true,
            'created_by' => Auth::guard('health_worker')->id(),
        ]);

        // Store generated password in session for display
        session()->flash('generated_password', $password);
        session()->flash('created_username', $account->username);

        return redirect()->route('health_worker.accounts.index')
            ->with('success', "Account '{$account->username}' created successfully.");
    }

    /**
     * Show form to edit an account.
     */
    public function edit($id)
    {
        $account = HealthWorker::with('barangay')->findOrFail($id);
        $barangays = Barangay::active()->orderBy('name')->get();
        
        // Check if this is the currently logged-in user's own account
        $isOwnAccount = Auth::guard('health_worker')->id() === (int)$id;

        // Get worker counts per barangay (excluding current account if it's a barangay worker)
        $workerCounts = HealthWorker::whereNotNull('barangay_id')
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->selectRaw('barangay_id, COUNT(*) as count')
            ->groupBy('barangay_id')
            ->pluck('count', 'barangay_id')
            ->toArray();

        return view('health_worker.accounts.edit', compact('account', 'barangays', 'workerCounts', 'isOwnAccount'));
    }

    /**
     * Update an account.
     */
    public function update(Request $request, $id)
    {
        $account = HealthWorker::findOrFail($id);
        $isOwnAccount = Auth::guard('health_worker')->id() == $id;

        $validated = $request->validate([
            'account_type' => 'required|in:rhu,barangay',
            'barangay_id' => 'required_if:account_type,barangay|nullable|exists:barangays,id',
            'email' => ['nullable', 'email', 'max:255', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', Rule::unique('health_workers')->ignore($id)],
            'name' => 'required|string|max:255',
            'contact_number' => ['nullable', 'string', 'regex:/^09\d{9}$/'],
        ], [
            'email.regex' => 'Please enter a valid email address.',
            'contact_number.regex' => 'Contact number must start with 09 and be exactly 11 digits.',
        ]);

        // Prevent users from changing their own account type
        // This prevents session issues where access permissions don't update until re-login
        $newBarangayId = $request->account_type === 'barangay' ? $validated['barangay_id'] : null;
        
        if ($isOwnAccount) {
            // For own account, ignore account type changes - keep original
            $newBarangayId = $account->barangay_id;
        }

        // Check barangay worker limit if changing to a different barangay
        if ($newBarangayId && $newBarangayId != $account->barangay_id) {
            $currentCount = HealthWorker::where('barangay_id', $newBarangayId)
                ->where('is_active', true)
                ->where('id', '!=', $id)
                ->count();

            if ($currentCount >= self::MAX_WORKERS_PER_BARANGAY) {
                $barangay = Barangay::find($newBarangayId);
                return back()->withInput()->withErrors([
                    'barangay_id' => "Maximum limit of " . self::MAX_WORKERS_PER_BARANGAY . " active workers reached for {$barangay->name}.",
                ]);
            }
        }

        // Prevent demoting the last RHU admin (only applies when not editing own account)
        if (!$isOwnAccount && $account->isRHU() && $newBarangayId !== null) {
            $rhuAdminCount = HealthWorker::whereNull('barangay_id')
                ->where('is_active', true)
                ->where('id', '!=', $id)
                ->count();

            if ($rhuAdminCount === 0) {
                return back()->withInput()->withErrors([
                    'account_type' => 'Cannot change the last active RHU Admin to a Barangay Worker.',
                ]);
            }
        }

        // Prepare update data
        $updateData = [
            'barangay_id' => $newBarangayId,
        ];

        // If not editing own account, allow changing name, email, and contact
        if (!$isOwnAccount) {
            $updateData['name'] = $validated['name'];
            $updateData['email'] = $validated['email'] ?? null;
            $updateData['contact_number'] = $validated['contact_number'] ?? null;
        }

        $account->update($updateData);

        return redirect()->route('health_worker.accounts.index')
            ->with('success', "Account '{$account->username}' updated successfully.");
    }

    /**
     * Reset password for an account.
     */
    public function resetPassword($id)
    {
        $account = HealthWorker::findOrFail($id);

        // Generate new password
        $password = $this->generatePassword();
        $account->update(['password' => Hash::make($password)]);

        // Store generated password in session for display
        session()->flash('generated_password', $password);
        session()->flash('reset_username', $account->username);

        return redirect()->route('health_worker.accounts.index')
            ->with('success', "Password reset for '{$account->username}'.");
    }

    /**
     * Toggle account active status.
     */
    public function toggleStatus($id)
    {
        $account = HealthWorker::findOrFail($id);
        $currentUser = Auth::guard('health_worker')->user();

        // Prevent self-deactivation
        if ($account->id === $currentUser->id) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        // Prevent deactivating the last RHU admin
        if ($account->isRHU() && $account->is_active) {
            $rhuAdminCount = HealthWorker::whereNull('barangay_id')
                ->where('is_active', true)
                ->count();

            if ($rhuAdminCount <= 1) {
                return back()->withErrors(['error' => 'Cannot deactivate the last active RHU Admin.']);
            }
        }

        // Check barangay worker limit when reactivating
        if (!$account->is_active && $account->barangay_id) {
            $currentCount = HealthWorker::where('barangay_id', $account->barangay_id)
                ->where('is_active', true)
                ->count();

            if ($currentCount >= self::MAX_WORKERS_PER_BARANGAY) {
                $barangay = $account->barangay;
                return back()->withErrors([
                    'error' => "Cannot activate: Maximum limit of " . self::MAX_WORKERS_PER_BARANGAY . " active workers reached for {$barangay->name}.",
                ]);
            }
        }

        $account->update(['is_active' => !$account->is_active]);
        $status = $account->is_active ? 'activated' : 'deactivated';

        return redirect()->route('health_worker.accounts.index')
            ->with('success', "Account '{$account->username}' has been {$status}.");
    }

    /**
     * Generate a secure random password.
     */
    private function generatePassword(int $length = 12): string
    {
        // Ensure password has uppercase, lowercase, and numbers
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed I, O to avoid confusion
        $lowercase = 'abcdefghjkmnpqrstuvwxyz'; // Removed i, l, o
        $numbers = '23456789'; // Removed 0, 1 to avoid confusion

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];

        // Fill remaining with mixed characters
        $allChars = $uppercase . $lowercase . $numbers;
        for ($i = 3; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle to randomize position
        return str_shuffle($password);
    }

    /**
     * Get worker count for a barangay via AJAX.
     */
    public function getBarangayWorkerCount($barangayId)
    {
        $count = HealthWorker::where('barangay_id', $barangayId)
            ->where('is_active', true)
            ->count();

        return response()->json([
            'count' => $count,
            'limit' => self::MAX_WORKERS_PER_BARANGAY,
            'available' => self::MAX_WORKERS_PER_BARANGAY - $count,
        ]);
    }
}
