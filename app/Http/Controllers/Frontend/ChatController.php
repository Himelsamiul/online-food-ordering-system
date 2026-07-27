<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The customer side of live chat.
 *
 * Every route here sits behind auth:frontend — an anonymous visitor sees the
 * widget but it only offers a link to the login page, and there is no endpoint
 * they could call directly to get around that.
 */
class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    /**
     * Everything the widget needs to paint itself.
     *
     * `after` makes this both the initial load (after=0 → the tail of the
     * history) and the poll (after=<last id> → only what is new), so the widget
     * has one code path instead of two that can disagree.
     */
    public function poll(Request $request): JsonResponse
    {
        $customer     = Auth::guard('frontend')->user();
        $conversation = $this->chat->threadFor($customer);

        $after = max(0, (int) $request->query('after', 0));
        $limit = (int) config('security.chat.history_limit', 50);

        $query = $conversation->messages()->orderBy('id');

        if ($after > 0) {
            $messages = $query->where('id', '>', $after)->limit($limit)->get();
        } else {
            // First paint: the newest N, put back in reading order.
            $messages = $query->reorder('id', 'desc')->limit($limit)->get()->reverse()->values();
        }

        // Opening the panel is what marks things read; a background poll with
        // the panel shut must leave the badge alone.
        if ($request->boolean('read')) {
            $this->chat->markReadForCustomer($conversation);
        }

        return response()->json([
            'conversation' => [
                'id'     => $conversation->id,
                'status' => $conversation->status,
                'open'   => $conversation->isOpen(),
            ],
            'messages' => $messages->map(fn (ChatMessage $m) => $m->toPayload())->all(),
            'unread'   => $request->boolean('read') ? 0 : $conversation->customer_unread,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $max = (int) config('security.chat.max_length', 2000);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:' . $max],
        ], [
            'body.required' => 'Type a message first.',
            'body.max'      => "Messages are limited to {$max} characters.",
        ]);

        $body = trim($validated['body']);

        if ($body === '') {
            return response()->json(['message' => 'Type a message first.'], 422);
        }

        $customer     = Auth::guard('frontend')->user();
        $conversation = $this->chat->threadFor($customer);

        $message = $this->chat->postFromCustomer($conversation, $customer, $body, $request->ip());

        return response()->json([
            'message'      => $message->toPayload(),
            'conversation' => [
                'id'     => $conversation->id,
                'status' => 'open',
                'open'   => true,
            ],
        ], 201);
    }
}
