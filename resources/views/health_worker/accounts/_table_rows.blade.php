@forelse($accounts as $account)
    <tr class="hover:bg-gray-50 transition-colors {{ !$account->is_active ? 'bg-gray-100' : '' }}">
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="font-medium text-gray-900">{{ $account->username }}</span>
            @if($account->id === Auth::guard('health_worker')->id())
                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">You</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
            {{ $account->name ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            @if($account->isRHU())
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    RHU Admin
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $account->barangay->name ?? 'Unknown' }}
                </span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $account->email ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            @if($account->is_active)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Active
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Inactive
                </span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $account->last_login_at ? $account->last_login_at->timezone('Asia/Manila')->diffForHumans() : 'Never' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            <div class="flex items-center gap-2">
                <a href="{{ route('activity-log.index', ['causer_id' => $account->id]) }}" 
                    class="text-purple-600 hover:text-purple-900" title="View Activity Log">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </a>
                
                <a href="{{ route('health_worker.accounts.edit', $account->id) }}" 
                    class="text-blue-600 hover:text-blue-900" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                
                <button onclick="confirmResetPassword({{ $account->id }}, '{{ $account->username }}')" 
                    class="text-yellow-600 hover:text-yellow-900" title="Reset Password">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </button>
                
                @if($account->id !== Auth::guard('health_worker')->id())
                    <button onclick="confirmToggleStatus({{ $account->id }}, '{{ $account->username }}', {{ $account->is_active ? 'true' : 'false' }})" 
                        class="{{ $account->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}" 
                        title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                        @if($account->is_active)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-lg font-medium">No accounts found</p>
            <p class="text-sm">Try adjusting your filters or create a new account</p>
        </td>
    </tr>
@endforelse
