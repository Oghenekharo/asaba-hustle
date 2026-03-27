<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\ChatMessage;
use App\Models\ServiceJob;
use App\Services\ChatService;
use App\Traits\LogActivity;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    use LogActivity;
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }
    /**
     * List user conversations
     */
    public function conversations(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where('client_id', $user->id)
            ->orWhere('worker_id', $user->id)
            ->with(['job', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->orderByDesc('id')
            ->get();

        return $this->successResponse(
            ConversationResource::collection($conversations),
            'Conversations retrieved successfully.'
        );
    }


    /**
     * Get messages in a conversation
     */
    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->with('sender')
            ->orderBy('id')
            ->cursorPaginate(20);

        return $this->successResponse(
            ChatMessageResource::collection($messages),
            'Messages retrieved successfully.'
        );
    }


    /**
     * Send message
     */
    public function send(SendMessageRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $job = ServiceJob::findOrFail($data['job_id']);
        $this->authorize('message', $job);

        $chat = $this->chatService->sendMessage(
            $user,
            $job,
            $data['message']
        );

        $this->activityLog()->log(
            $user->id,
            'message_sent',
            ['date' => now()],
            $request->ip()
        );

        return $this->successResponse(
            new ChatMessageResource($chat->load('sender')),
            'Message sent successfully.',
            201
        );
    }


    /**
     * Mark messages as read
     */
    public function markRead(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);
        $user = $request->user();

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->update([
                'is_read' => true
            ]);

        return $this->successResponse(
            null,
            'Messages marked as read.'
        );
    }
}
