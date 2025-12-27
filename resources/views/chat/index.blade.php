@extends('layouts.app')

@section('title', 'Live Chat Support')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Support Chat</h1>
            <p class="text-gray-600">Get help from our support team</p>
        </div>

        @if($chat && $chat->status !== 'closed')
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Chat Session</h2>
                        <p class="text-sm text-gray-600">Started {{ $chat->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        {{ ucfirst($chat->status) }}
                    </span>
                </div>

                <div id="messages" class="bg-gray-50 rounded-lg p-4 h-96 overflow-y-auto mb-4 border border-gray-200">
                    @forelse($chat->messages as $message)
                        <div class="mb-4 {{ $message->sender_type === 'user' ? 'text-right' : 'text-left' }}">
                            <div class="inline-block max-w-xs {{ $message->sender_type === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900' }} rounded-lg p-3">
                                <p class="text-sm">{{ $message->message }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $message->sender_name }} - {{ $message->created_at->format('H:i') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-8">No messages yet</p>
                    @endforelse
                </div>

                <form id="messageForm" class="flex gap-2">
                    @csrf
                    <input type="text" id="messageInput" name="message" placeholder="Type your message..." 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                           required minlength="1" maxlength="1000">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                        Send
                    </button>
                </form>

                <div class="mt-4">
                    <form action="{{ route('chat.close', $chat->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium" onclick="return confirm('Are you sure you want to close this chat?')">
                            Close Chat
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Start a New Chat</h2>

                <form action="{{ route('chat.start') }}" method="POST" class="space-y-4">
                    @csrf

                    @unless(auth()->check())
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" id="name" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('email')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    @endunless

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea id="message" name="message" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required minlength="5" maxlength="1000" placeholder="Please describe your issue..."></textarea>
                        @error('message')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                        Start Chat
                    </button>
                </form>

                <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">💡 Support Information</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✓ Average response time: 2-5 minutes</li>
                        <li>✓ Support hours: Mon - Fri, 9 AM - 6 PM</li>
                        <li>✓ All chats are recorded for quality assurance</li>
                    </ul>
                </div>
            </div>

            @if($chat && $chat->status === 'closed')
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-800">
                        <strong>Chat Closed:</strong> Your previous chat was closed on {{ $chat->closed_at?->format('M d, Y H:i') }}.
                        Feel free to start a new chat if you need further assistance.
                    </p>
                </div>
            @endif
        @endif
    </div>
</div>

@if($chat && $chat->status !== 'closed')
<script>
    const chatId = {{ $chat->id }};
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const messagesContainer = document.getElementById('messages');

    // Send message
    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = messageInput.value;
        if (!message.trim()) return;

        try {
            const response = await fetch(`/chat/${chatId}/message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ message }),
            });

            if (response.ok) {
                const data = await response.json();
                messageInput.value = '';
                loadMessages();
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        }
    });

    // Load messages
    async function loadMessages() {
        try {
            const response = await fetch(`/chat/${chatId}/messages`);
            if (response.ok) {
                const messages = await response.json();
                messagesContainer.innerHTML = '';

                if (messages.length === 0) {
                    messagesContainer.innerHTML = '<p class="text-gray-500 text-center py-8">No messages yet</p>';
                    return;
                }

                messages.forEach(msg => {
                    const isOwn = msg.is_own;
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `mb-4 ${isOwn ? 'text-right' : 'text-left'}`;
                    messageDiv.innerHTML = `
                        <div class="inline-block max-w-xs ${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'} rounded-lg p-3">
                            <p class="text-sm">${escapeHtml(msg.message)}</p>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">${msg.sender_name} - ${msg.created_at}</p>
                    `;
                    messagesContainer.appendChild(messageDiv);
                });

                // Scroll to bottom
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

    // Poll for new messages every 2 seconds
    setInterval(loadMessages, 2000);
    loadMessages();
</script>
@endif

@endsection
