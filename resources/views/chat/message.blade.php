<div class="mb-3">
    <div class="text-sm {{ $message->sender_type === 'user' ? 'text-right' : 'text-left' }}">
        <div class="inline-block max-w-xs {{ $message->sender_type === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900' }} rounded-lg p-2">
            <p class="text-sm">{{ $message->message }}</p>
        </div>
        <p class="text-xs text-gray-500 mt-1">{{ $message->sender_name }} • {{ $message->created_at->format('H:i') }}</p>
    </div>
</div>
