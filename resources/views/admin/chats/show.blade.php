@extends('layouts.admin')

@section('title', 'Chat #' . $chat->id)

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 p-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Chat #{{ $chat->id }}</h1>
                    <div class="mt-2 text-sm text-gray-600">
                        <p><strong>Visitor:</strong> {{ $chat->display_name }}</p>
                        <p><strong>Email:</strong> {{ $chat->display_email }}</p>
                        <p><strong>Started:</strong> {{ $chat->started_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        {{ $chat->status === 'open' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $chat->status === 'in-progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $chat->status === 'waiting' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $chat->status === 'closed' ? 'bg-gray-100 text-gray-800' : '' }}
                    ">
                        {{ ucfirst($chat->status) }}
                    </span>
                </div>
            </div>

            <!-- Messages -->
            <div class="p-6">
                <div id="messages" class="bg-gray-50 rounded-lg p-4 h-96 overflow-y-auto mb-6 border border-gray-200">
                    @forelse($chat->messages as $message)
                        <div class="mb-4 {{ $message->sender_type === 'admin' ? 'text-right' : 'text-left' }}">
                            <div class="inline-block max-w-xs {{ $message->sender_type === 'admin' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-900' }} rounded-lg p-3">
                                <p class="text-sm">{{ $message->message }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $message->sender_name }} • {{ $message->created_at->format('H:i') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-12">No messages</p>
                    @endforelse
                </div>

                <!-- Message Input -->
                @if($chat->status !== 'closed')
                    <form id="replyForm" class="flex gap-2 mb-6">
                        @csrf
                        <input type="text" id="replyInput" name="message" placeholder="Type your reply..."
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               required minlength="1" maxlength="1000" autocomplete="off">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Reply
                        </button>
                    </form>
                @else
                    <div class="p-4 bg-gray-100 border border-gray-300 rounded-lg mb-6">
                        <p class="text-gray-700 font-semibold">This chat is closed</p>
                    </div>
                @endif

                <!-- Chat Settings -->
                <div class="border-t border-gray-200 pt-6">
                    <form id="statusForm" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="open" {{ $chat->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="waiting" {{ $chat->status === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                    <option value="in-progress" {{ $chat->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="closed" {{ $chat->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select id="priority" name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="low" {{ $chat->priority === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ $chat->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ $chat->priority === 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                Update Chat Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('admin.chats.index') }}" class="text-green-600 hover:text-green-900 font-medium">
                ← Back to Chats
            </a>
        </div>
    </div>
</div>

<script>
    const chatId = {{ $chat->id }};
    const messagesContainer = document.getElementById('messages');
    const replyForm = document.getElementById('replyForm');
    const statusForm = document.getElementById('statusForm');

    // Handle status form submission
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            handleStatusUpdate();
        }, true); // Use capture phase
    }

    async function handleStatusUpdate() {
        const status = document.getElementById('status').value;
        const priority = document.getElementById('priority').value;
        const token = document.querySelector('input[name="_token"]').value;

        if (!status || !priority || !token) {
            showNotification('Missing required fields', 'error');
            return;
        }

        try {
            const body = new URLSearchParams();
            body.append('_method', 'PUT');
            body.append('status', status);
            body.append('priority', priority);
            body.append('_token', token);

            const response = await fetch(`/admin/chats/${chatId}/status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body.toString()
            });

            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            console.log('Response url:', response.url);

            if (response.ok) {
                showNotification('Chat status updated successfully!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Error: ' + response.status + ' ' + response.statusText, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred: ' + error.message, 'error');
        }
    }

    @if($chat->status !== 'closed')
        if (replyForm) {
            replyForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const replyInput = document.getElementById('replyInput');
                const message = replyInput.value.trim();
                if (!message) return;

                const token = document.querySelector('input[name="_token"]').value;

                try {
                    const response = await fetch(`/admin/chats/${chatId}/reply`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({ message }),
                    });

                    if (response.ok) {
                        replyInput.value = '';
                        loadMessages();
                        showNotification('Message sent!', 'success');
                    } else {
                        showNotification('Error sending message', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('An error occurred while sending the message', 'error');
                }
            });
        }
    @endif

    async function loadMessages() {
        try {
            const response = await fetch(`/admin/chats/${chatId}/messages`);
            if (response.ok) {
                const messages = await response.json();
                messagesContainer.innerHTML = '';

                if (messages.length === 0) {
                    messagesContainer.innerHTML = '<p class="text-gray-500 text-center py-12">No messages</p>';
                    return;
                }

                messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `mb-4 ${msg.sender_type === 'admin' ? 'text-right' : 'text-left'}`;
                    messageDiv.innerHTML = `
                        <div class="inline-block max-w-xs ${msg.sender_type === 'admin' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-900'} rounded-lg p-3">
                            <p class="text-sm">${escapeHtml(msg.message)}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">${msg.sender_name} • ${msg.created_at}</p>
                    `;
                    messagesContainer.appendChild(messageDiv);
                });

                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-medium ${
            type === 'success' ? 'bg-green-600' :
            type === 'error' ? 'bg-red-600' :
            'bg-blue-600'
        } z-50`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Load messages on page load and then periodically
    loadMessages();
    setInterval(loadMessages, 3000);
</script>
@endsection
