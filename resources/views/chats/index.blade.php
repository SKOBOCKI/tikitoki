<x-layouts.app title="Chats | TikiToki">
    <section class="chat-page" data-chat-app data-current-user="{{ auth()->id() }}">
        <nav class="profile-nav" aria-label="Chat navigation">
            <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>
            <div>
                <a href="{{ route('feed.fyp') }}">For You</a>
                <a href="{{ route('profile.show') }}">Profile</a>
            </div>
        </nav>

        @if (session('status'))
            <div class="status-toast">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="status-toast error-toast">{{ $errors->first() }}</div>
        @endif

        <div class="chat-layout">
            <aside class="chat-sidebar" aria-label="Conversations">
                <div class="chat-panel-heading">
                    <div>
                        <p>Inbox</p>
                        <h1>Chats</h1>
                    </div>
                    <a href="#new-chat">New</a>
                </div>

                <div class="chat-list">
                    @forelse ($chats as $chat)
                        <a @class(['chat-list-item', 'active' => $selectedChat?->is($chat)]) href="{{ route('chats.show', $chat) }}">
                            <span class="chat-avatar">{{ $chat->is_group ? 'GC' : Str::substr($chat->displayNameFor(auth()->user()), 0, 2) }}</span>
                            <div>
                                <strong>{{ $chat->displayNameFor(auth()->user()) }}</strong>
                                <p>
                                    @if ($chat->lastMessage)
                                        {{ '@'.$chat->lastMessage->user->username }}: {{ Str::limit($chat->lastMessage->body, 42) }}
                                    @else
                                        No messages yet.
                                    @endif
                                </p>
                            </div>
                        </a>
                    @empty
                        <p class="muted">No chats yet. Start one below.</p>
                    @endforelse
                </div>

                <div class="chat-create" id="new-chat">
                    <details open>
                        <summary>Direct chat</summary>
                        <form method="POST" action="{{ route('chats.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="direct">
                            <select name="user_ids[]" required>
                                <option value="">Choose a person</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ '@'.$user->username }}</option>
                                @endforeach
                            </select>
                            <button type="submit">Open chat</button>
                        </form>
                    </details>

                    <details>
                        <summary>Group chat</summary>
                        <form method="POST" action="{{ route('chats.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="group">
                            <input type="text" name="title" placeholder="Group name" maxlength="80">
                            <div class="chat-user-picker">
                                @foreach ($users as $user)
                                    <label>
                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}">
                                        <span>{{ $user->name }}</span>
                                        <small>{{ '@'.$user->username }}</small>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit">Create group</button>
                        </form>
                    </details>
                </div>
            </aside>

            <main class="chat-window">
                @if ($selectedChat)
                    <header class="chat-window-header">
                        <div>
                            <p>{{ $selectedChat->is_group ? 'Group chat' : 'Direct chat' }}</p>
                            <h2>{{ $selectedChat->displayNameFor(auth()->user()) }}</h2>
                        </div>
                        <span>{{ $selectedChat->participants->count() }} members</span>
                    </header>

                    <div
                        class="chat-messages"
                        data-chat-messages
                        data-messages-url="{{ route('chats.messages', $selectedChat) }}"
                    >
                        @forelse ($selectedChat->messages as $message)
                            <div @class(['chat-message', 'own' => $message->user_id === auth()->id()]) data-message-id="{{ $message->id }}">
                                <img src="{{ $message->user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($message->user->name) }}" alt="{{ $message->user->name }}">
                                <div>
                                    <span>{{ '@'.$message->user->username }} · {{ $message->created_at->format('H:i') }}</span>
                                    <p>{{ $message->body }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="muted" data-empty-chat>No messages yet. Say hello.</p>
                        @endforelse
                    </div>

                    <form class="chat-send" data-chat-send data-send-url="{{ route('chats.messages.store', $selectedChat) }}">
                        <input type="text" name="body" placeholder="Message {{ $selectedChat->displayNameFor(auth()->user()) }}" maxlength="1000" autocomplete="off" required>
                        <button type="submit">Send</button>
                    </form>
                @else
                    <div class="empty-chat-state">
                        <h1>Choose someone to chat with</h1>
                        <p>Create a direct chat or group chat from the left panel.</p>
                    </div>
                @endif
            </main>

            <aside class="chat-members" aria-label="Chat members">
                @if ($selectedChat)
                    <div class="chat-panel-heading">
                        <div>
                            <p>People</p>
                            <h2>Members</h2>
                        </div>
                    </div>

                    <div class="member-list">
                        @foreach ($selectedChat->participants as $participant)
                            <div class="member-row">
                                <img src="{{ $participant->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($participant->name) }}" alt="{{ $participant->name }}">
                                <div>
                                    <strong>{{ $participant->name }}</strong>
                                    <span>{{ '@'.$participant->username }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="muted">Members will appear here.</p>
                @endif
            </aside>
        </div>

        <x-bottom-nav active="inbox" />
    </section>
</x-layouts.app>
