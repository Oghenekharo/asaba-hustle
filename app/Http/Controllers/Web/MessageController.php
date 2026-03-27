<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\ServiceJob;
use App\Services\ChatService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function conversations(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where('client_id', $user->id)
            ->orWhere('worker_id', $user->id)
            ->with(['client', 'worker', 'job.client', 'job.worker', 'job.skill', 'messages' => function ($query) {
                $query->latest()->limit(1)->with('sender');
            }])
            ->withCount(['messages as unread_messages_count' => function ($query) use ($user) {
                $query
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false);
            }])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->orderByDesc('id')
            ->get();

        $conversations->each(function (Conversation $conversation) {
            if (blank($conversation->uuid)) {
                $conversation->save();
            }
        });

        return view('web.messages', compact('conversations'));
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender', 'conversation'])
            ->orderBy('id')
            ->cursorPaginate(20);

        return $this->successResponse(
            ChatMessageResource::collection($messages),
            'Messages retrieved successfully.'
        );
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $job = ServiceJob::findOrFail($request->validated('job_id'));
        $conversation = null;

        if ($request->filled('conversation_uuid')) {
            $conversation = Conversation::query()
                ->where('uuid', $request->validated('conversation_uuid'))
                ->where('job_id', $job->id)
                ->firstOrFail();

            $this->authorize('view', $conversation);
        } else {
            $this->authorize('message', $job);
        }

        $chat = $this->chatService->sendMessage(
            $request->user(),
            $job,
            $request->validated('message'),
            $conversation
        );

        return $this->successResponse(
            new ChatMessageResource($chat->load(['sender', 'conversation'])),
            'Message sent successfully.',
            201
        );
    }

    public function markConversationRead(Request $request, Conversation $conversation)
    {
        $this->authorize('update', $conversation);

        ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $request->user()->id)
            ->update(['is_read' => true]);

        return $this->successResponse(null, 'Messages marked as read.');
    }
}
