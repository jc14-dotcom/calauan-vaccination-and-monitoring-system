@if($accounts->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600">
                Showing <span class="font-semibold">{{ $accounts->firstItem() }}</span> 
                to <span class="font-semibold">{{ $accounts->lastItem() }}</span> 
                of <span class="font-semibold">{{ $accounts->total() }}</span> accounts
            </div>
            <div class="flex items-center gap-2">
                @if ($accounts->onFirstPage())
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="#" class="pagination-link px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50" data-page="{{ $accounts->currentPage() - 1 }}">Previous</a>
                @endif

                @if ($accounts->hasMorePages())
                    <a href="#" class="pagination-link px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50" data-page="{{ $accounts->currentPage() + 1 }}">Next</a>
                @else
                    <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    </div>
@endif
