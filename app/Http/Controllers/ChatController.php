<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ChatController extends Controller
{
    /**
     * Show the chat page for initiating a new chat
     */
    public function show(): View
    {
        $chat = null;

        if (auth()->check()) {
            $chat = Chat::where('user_id', auth()->id())
                ->where('status', '!=', 'closed')
                ->first();
        }

        return view('chat.index', compact('chat'));
    }

    /**
     * Start a new chat session
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|min:5|max:1000',
        ]);

        // Create chat
        $chat = Chat::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'] ?? (auth()->user()?->name ?? null),
            'email' => $validated['email'] ?? (auth()->user()?->email ?? null),
            'initial_message' => $validated['message'],
            'status' => 'open',
            'started_at' => now(),
        ]);

        // Add initial message
        ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'sender_type' => 'user',
        ]);

        return redirect()->route('chat.window', $chat->id);
    }

    /**
     * Show active chat window
     */
    public function window(Chat $chat): View
    {
        $this->authorize('view', $chat);
        $chat->markAsRead();

        return view('chat.window', compact('chat'));
    }

    /**
     * Send a message in chat
     */
    public function sendMessage(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $chat);

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'sender_type' => auth()->user()?->is_admin ? 'admin' : 'user',
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'html' => view('chat.message', compact('message'))->render(),
        ]);
    }

    /**
     * Get chat messages (for AJAX)
     */
    public function getMessages(Chat $chat): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $chat);

        $messages = $chat->messages()
            ->with('user')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender_name,
                    'created_at' => $message->created_at->diffForHumans(),
                    'is_own' => $message->user_id === auth()->id(),
                ];
            });

        return response()->json($messages);
    }

    /**
     * Close a chat
     */
    public function close(Chat $chat): RedirectResponse
    {
        $this->authorize('view', $chat);
        $chat->close();

        return redirect()->route('chat.show')->with('success', 'Chat closed successfully.');
    }

    /**
     * Admin: List all chats
     */
    public function adminList(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $chats = Chat::with('user', 'assignedTo')
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('admin.chats.index', compact('chats'));
    }

    /**
     * Admin: View specific chat
     */
    public function adminView(Chat $chat): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }
        $chat->markAsRead();

        return view('admin.chats.show', compact('chat'));
    }

    /**
     * Admin: Reply to chat
     */
    public function adminReply(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'sender_type' => 'admin',
        ]);

        // Update chat status if it was waiting
        if ($chat->status === 'waiting') {
            $chat->update([
                'status' => 'in-progress',
                'assigned_to' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Admin: Update chat status
     */
    public function updateStatus(Request $request, Chat $chat): RedirectResponse
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'status' => 'required|in:open,waiting,in-progress,closed',
            'priority' => 'required|in:low,medium,high',
        ]);

        $chat->update([
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'assigned_to' => $validated['status'] !== 'closed' ? (auth()->id() ?? $chat->assigned_to) : $chat->assigned_to,
        ]);

        if ($validated['status'] === 'closed') {
            $chat->close();
        }

        return back()->with('success', 'Chat status updated successfully.');
    }
}
