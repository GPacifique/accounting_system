<!-- Search Box Component -->
<div class="flex-1 max-w-xl mx-4" x-data="{ query: '' }">
    <form method="GET" action="#" class="w-full">
        <div class="relative">
            <input
                type="text"
                name="q"
                x-model="query"
                placeholder="Search..."
                class="w-full px-4 py-2 text-sm text-gray-900 placeholder-gray-500 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none"
            />
            <button
                type="submit"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>
