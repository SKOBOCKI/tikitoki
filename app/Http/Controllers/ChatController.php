<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request, ?Chat $chat = null): View
    {
        $user = $request->user();

        $chats = $user->chats()
            ->with(['participants', 'lastMessage.user'])
            ->withCount('messages')
            ->orderByDesc('chats.updated_at')
            ->get();

        $selectedChat = $chat ?: $chats->first();

        if ($selectedChat) {
            $this->ensureParticipant($request, $selectedChat);
            $selectedChat->load(['participants', 'messages.user']);
            $selectedChat->participants()->updateExistingPivot($user->id, [
                'last_read_at' => now(),
            ]);
        }

        return view('chats.index', [
            'chats' => $chats,
            'selectedChat' => $selectedChat,
            'users' => User::query()
                ->whereKeyNot($user->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['direct', 'group'])],
            'title' => ['nullable', 'string', 'max:80'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('id', '!=', $request->user()->id)),
            ],
        ]);

        $participantIds = collect($data['user_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($data['type'] === 'direct' && $participantIds->count() !== 1) {
            return back()->withErrors(['user_ids' => 'Choose exactly one person for a direct chat.']);
        }

        if ($data['type'] === 'group' && $participantIds->count() < 2) {
            return back()->withErrors(['user_ids' => 'Choose at least two people for a group chat.']);
        }

        $chat = $data['type'] === 'direct'
            ? $this->findOrCreateDirectChat($request->user(), $participantIds->first())
            : $this->createGroupChat($request->user(), $participantIds, $data['title'] ?? null);

        return redirect()->route('chats.show', $chat);
    }

    public function messages(Request $request, Chat $chat): JsonResponse
    {
        $this->ensureParticipant($request, $chat);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $chat->messages()
            ->with('user')
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->oldest()
            ->get();

        $chat->participants()->updateExistingPivot($request->user()->id, [
            'last_read_at' => now(),
        ]);

        return response()->json([
            'messages' => $messages->map(fn ($message) => $this->formatMessage($message)),
        ]);
    }

    public function send(Request $request, Chat $chat): JsonResponse
    {
        $this->ensureParticipant($request, $chat);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = DB::transaction(function () use ($chat, $request, $data) {
            $message = $chat->messages()->create([
                'user_id' => $request->user()->id,
                'body' => $data['body'],
            ]);

            $chat->touch();

            return $message;
        });

        $message->load('user');

        return response()->json([
            'message' => $this->formatMessage($message),
        ], 201);
    }

    private function findOrCreateDirectChat(User $user, int $otherUserId): Chat
    {
        $targetIds = collect([$user->id, $otherUserId])->sort()->values();

        $existingChat = $user->chats()
            ->where('is_group', false)
            ->with('participants')
            ->get()
            ->first(function (Chat $chat) use ($targetIds) {
                return $chat->participants
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->all() === $targetIds->all();
            });

        if ($existingChat) {
            return $existingChat;
        }

        return DB::transaction(function () use ($user, $otherUserId) {
            $chat = Chat::create([
                'owner_id' => $user->id,
                'is_group' => false,
            ]);

            $chat->participants()->attach([$user->id, $otherUserId]);

            return $chat;
        });
    }

    private function createGroupChat(User $user, $participantIds, ?string $title): Chat
    {
        return DB::transaction(function () use ($user, $participantIds, $title) {
            $chat = Chat::create([
                'owner_id' => $user->id,
                'title' => $title ?: 'New group',
                'is_group' => true,
            ]);

            $chat->participants()->attach(
                $participantIds
                    ->push($user->id)
                    ->unique()
                    ->values()
                    ->all()
            );

            return $chat;
        });
    }

    private function ensureParticipant(Request $request, Chat $chat): void
    {
        abort_unless(
            $chat->participants()->whereKey($request->user()->id)->exists(),
            403
        );
    }

    private function formatMessage($message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'user_id' => $message->user_id,
            'user_name' => $message->user->name,
            'username' => $message->user->username,
            'avatar_url' => $message->user->avatar_url ?: 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($message->user->name),
            'created_at' => $message->created_at->format('H:i'),
        ];
    }
}
