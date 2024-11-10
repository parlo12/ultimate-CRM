@php
    use App\Helpers\Helper;
    use App\Library\Tool;
@endphp
<ul class="chat-users-list chat-list media-list">
    @foreach ($starred_chats as $chat)
        @if ($chat->is_starred)
            <li data-id="{{ $chat->uid }}" data-box-id="{{ $chat->id }}">
                <span class="avatar">
                    <img src="{{ asset('images/profile/profile.jpg') }}" height="36"
                        width="54" alt="Avatar" />
                </span>
                <div class="chat-info flex-grow-1">
                    <h5 class="mb-0">{{ \App\Helpers\Helper::contact_name1($chat->to) }}</h5>
                    <p class="card-text text-truncate">
                        {{ \Illuminate\Support\Str::limit(\App\Helpers\Helper::last_message($chat->id), 15) }}
                    </p>
                </div>
                <div class="chat-meta text-nowrap">
                    <small
                        class="float-end mb-25 chat-time">{{ Tool::customerDateTime($chat->updated_at) }}</small>
                    @if ($chat->notification)
                        <span
                            class="badge bg-primary rounded-pill float-end notification_count">{{ $chat->notification }}</span>
                    @endif
                    <button type="button"
                        class="btn  {{ $chat->is_starred ? 'bg-warning' : '' }} p-0 star-btn float-end"
                        onclick="toggleStar('{{ $chat->uid }}', this)"
                        title="{{ $chat->is_starred ? 'Unmark as Starred' : 'Mark as Starred' }}">
                        <i data-feather="star"
                            class="cursor-pointer font-medium-2  {{ $chat->is_starred ? 'text-white' : 'text-secondary' }}"></i>
                    </button>
                </div>
            </li>
        @endif
    @endforeach
</ul>