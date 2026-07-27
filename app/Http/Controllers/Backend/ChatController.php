<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The support inbox.
 *
 * Route middleware already gates each verb on chat.view / chat.create /
 * chat.edit / chat.delete, so nothing here re-checks permissions — the routes
 * are the single place that decision lives.
 */
class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): View
    {
        $conversations = $this->listQuery($request)->paginate($this->perPage($request, 20))->withQueryString();

        // ?conversation= wins; otherwise land on the first thread so the pane
        // is never an empty box on first visit.
        $active = null;

        if ($request->filled('conversation')) {
            $active = ChatConversation::with('customer')->find($request->query('conversation'));
        }

        $active ??= $conversations->first();

        $messages = collect();

        if ($active) {
            $messages = $active->messages()
                ->orderBy('id', 'desc')
                ->limit((int) config('security.chat.history_limit', 50))
                ->get()
                ->reverse()
                ->values();

            $this->chat->markReadForAdmin($active);
            $active->refresh();
        }

        return view('backend.pages.chat.index', [
            'conversations' => $conversations,
            'active'        => $active,
            'messages'      => $messages,
            'filters'       => $request->only(['q', 'status', 'unread']),
            'canReply'      => Auth::user()->hasPermission('chat.create'),
            'canManage'     => Auth::user()->hasPermission('chat.edit'),
            'canDelete'     => Auth::user()->hasPermission('chat.delete'),
        ]);
    }

    /**
     * One request serves both halves of the screen: new lines in the thread
     * the admin is reading, plus the sidebar's unread counts. Splitting it
     * would double the polling load for no benefit.
     */
    public function poll(Request $request): JsonResponse
    {
        $conversationId = (int) $request->query('conversation', 0);
        $after          = max(0, (int) $request->query('after', 0));

        $payload = [
            'messages'      => [],
            'conversation'  => null,
            'threads'       => [],
            'total_unread'  => ChatConversation::sum('admin_unread'),
        ];

        if ($conversationId > 0) {
            $conversation = ChatConversation::find($conversationId);

            if ($conversation) {
                $messages = $conversation->messages()
                    ->where('id', '>', $after)
                    ->orderBy('id')
                    ->limit((int) config('security.chat.history_limit', 50))
                    ->get();

                // The admin has this thread on screen, so anything that just
                // arrived is read by definition.
                if ($messages->isNotEmpty()) {
                    $this->chat->markReadForAdmin($conversation);
                    $conversation->refresh();
                }

                $payload['messages']     = $messages->map(fn (ChatMessage $m) => $m->toPayload())->all();
                $payload['conversation'] = [
                    'id'     => $conversation->id,
                    'status' => $conversation->status,
                    'open'   => $conversation->isOpen(),
                ];
                $payload['total_unread'] = ChatConversation::sum('admin_unread');
            }
        }

        $payload['threads'] = $this->listQuery($request)
            ->limit(50)
            ->get()
            ->map(fn (ChatConversation $c) => [
                'id'      => $c->id,
                'name'    => $c->customer?->full_name ?? 'Deleted customer',
                'preview' => $c->last_message_preview,
                'unread'  => $c->admin_unread,
                'status'  => $c->status,
                'time'    => optional($c->last_message_at ?? $c->created_at)->diffForHumans(null, true),
            ])->all();

        return response()->json($payload);
    }

    public function send(Request $request, ChatConversation $conversation): JsonResponse
    {
        $max = (int) config('security.chat.max_length', 2000);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:' . $max],
        ]);

        $body = trim($validated['body']);

        if ($body === '') {
            return response()->json(['message' => 'Type a message first.'], 422);
        }

        $message = $this->chat->postFromAdmin($conversation, Auth::user(), $body, $request->ip());

        return response()->json(['message' => $message->toPayload()], 201);
    }

    public function updateStatus(Request $request, ChatConversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        if ($validated['status'] === ChatConversation::STATUS_CLOSED) {
            $this->chat->close($conversation, Auth::user());
            $flash = 'Conversation marked resolved. The customer can reopen it by replying.';
        } else {
            $this->chat->reopen($conversation, Auth::user());
            $flash = 'Conversation reopened.';
        }

        return redirect()
            ->route('admin.chat.index', ['conversation' => $conversation->id])
            ->with('success', $flash);
    }

    public function destroy(ChatConversation $conversation): RedirectResponse
    {
        $this->chat->delete($conversation, Auth::user());

        return redirect()
            ->route('admin.chat.index')
            ->with('success', 'Conversation deleted along with its transcript.');
    }

    /* ---------------------------------------------------------------- private */

    /**
     * The thread list, filtered the same way for the page render and the poll
     * so a refresh can never reorder or drop rows the poll would have kept.
     */
    private function listQuery(Request $request)
    {
        return ChatConversation::query()
            ->with('customer:id,full_name,email,image')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->query('q') . '%';

                $query->whereHas('customer', fn ($c) => $c
                    ->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->when($request->query('status') === 'open', fn ($q) => $q->open())
            ->when($request->query('status') === 'closed', fn ($q) => $q->closed())
            ->when($request->boolean('unread'), fn ($q) => $q->awaitingReply())
            ->recentFirst();
    }
}
