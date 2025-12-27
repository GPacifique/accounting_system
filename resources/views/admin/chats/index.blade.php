@extends('layouts.admin')

@section('title', 'Chat Support Management')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Support Chat Management</h1>
        <p class="text-gray-600 mt-2">Manage all customer support chats</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid gap-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="text-sm text-blue-600 font-medium">Open Chats</div>
                <div class="text-2xl font-bold text-blue-900 mt-2">{{ $chats->where('status', 'open')->count() }}</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <div class="text-sm text-yellow-600 font-medium">In Progress</div>
                <div class="text-2xl font-bold text-yellow-900 mt-2">{{ $chats->where('status', 'in-progress')->count() }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="text-sm text-purple-600 font-medium">Waiting</div>
                <div class="text-2xl font-bold text-purple-900 mt-2">{{ $chats->where('status', 'waiting')->count() }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="text-sm text-gray-600 font-medium">Closed</div>
                <div class="text-2xl font-bold text-gray-900 mt-2">{{ $chats->where('status', 'closed')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Messages</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($chats as $chat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $chat->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $chat->display_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $chat->display_email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $chat->status === 'open' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $chat->status === 'in-progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $chat->status === 'waiting' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $chat->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst($chat->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded 
                                    {{ $chat->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $chat->priority === 'medium' ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $chat->priority === 'high' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($chat->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $chat->started_at?->format('M d, H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $chat->messages_count ?? $chat->messages->count() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <a href="{{ route('admin.chats.show', $chat->id) }}" class="text-green-600 hover:text-green-900 font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                No chats found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($chats instanceof \Illuminate\Pagination\Paginator)
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $chats->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
