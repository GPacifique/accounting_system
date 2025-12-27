@extends('layouts.app')

@section('title', 'Chat Support')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="flex items-center justify-between border-b border-gray-200 p-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Chat Support</h1>
                    <p class="text-sm text-gray-600 mt-1">Session {{ $chat->id }} • Started {{ $chat->created_at->format('M d, Y H:i') }}</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $chat->status === 'closed' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ ucfirst($chat->status) }}
                </span>
            </div>

            <div class="p-6">
                <div id="messages" class="bg-gray-50 rounded-lg p-4 h-96 overflow-y-auto mb-6 border border-gray-200">
                    @forelse($chat->messages as $message)
                        <div class="mb-4 {{ $message->user_id === auth()->id() ? 'text-right' : 'text-left' }}">
                            <div class="inline-block max-w-xs {{ $message->user_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900' }} rounded-lg p-3">
                                <p class="text-sm">{{ $message->message }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $message->user?->name ?? 'Support' }} • {{ $message->created_at->format('H:i') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-12">No messages yet. Start the conversation!</p>
                    @endforelse
                </div>

                @if($chat->status !== 'closed')
                    <form id="messageForm" class="flex gap-2 mb-4">
                        @csrf
                        <input type="text" id="messageInput" name="message" placeholder="Type your message..." 
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                               required minlength="1" maxlength="1000" autocomplete="off">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Send
                        </button>
                    </form>
                @else
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg mb-4">
                        <p class="text-red-800">This chat has been closed. <a href="{{ route('chat.show') }}" class="underline font-semibold">Start a new chat</a></p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    const chatId = {{ $chat->id }};
    const messageForm = document.getElementById('messageForm');
    const messagesContainer = document.getElementById('messages');
    let lastMessageId = {{ $chat->messages->max('id') ?? 0 }};

    @if($chat->status !== 'closed')
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            if (!message) return;

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
                    messageInput.value = '';
                    loadMessages();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    @endif

    async function loadMessages() {
        try {
            const response = await fetch(`/chat/${chatId}/messages`);
            if (response.ok) {
                const messages = await response.json();
                messagesContainer.innerHTML = '';

                if (messages.length === 0) {
                    messagesContainer.innerHTML = '<p class="text-gray-500 text-center py-12">No messages yet</p>';
                    return;
                }

                messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `mb-4 ${msg.is_own ? 'text-right' : 'text-left'}`;
                    messageDiv.innerHTML = `
                        <div class="inline-block max-w-xs ${msg.is_own ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'} rounded-lg p-3">
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

    // Poll for new messages
    setInterval(loadMessages, 1500);
    loadMessages();
</script>
@endsection
