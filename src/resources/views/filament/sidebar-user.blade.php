@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
@endphp

@if ($user)
    <div class="fi-sidebar-user sticky bottom-0 left-0 right-0">
        <div class="m-2 rounded-lg px-3 py-2 hover:bg-gray-950\/5 dark:hover:bg-white\/5">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-600\/10 text-sm font-semibold text-primary-700 dark:text-primary-300">
                    {{ strtoupper(mb_substr($user->name ?: ($user->email ?: 'U'), 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-medium">{{ $user->name ?: 'Account' }}</div>
                    <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                </div>
            </div>
        </div>
    </div>
@endif
