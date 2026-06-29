@props(['name'])

<svg
    {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    data-admin-icon="{{ $name }}"
    aria-hidden="true"
>
    @switch($name)
        @case('home')
            <path d="m3 11 9-8 9 8" />
            <path d="M5 10v10h14V10M9 20v-6h6v6" />
            @break

        @case('cog')
            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 8.94 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.57 15 1.7 1.7 0 0 0 3 14H3v-4h.08A1.7 1.7 0 0 0 4.6 8.94a1.7 1.7 0 0 0-.34-1.88L4.2 7l2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.57 1.7 1.7 0 0 0 10 3V3h4v.08A1.7 1.7 0 0 0 15.06 4.6a1.7 1.7 0 0 0 1.88-.34L17 4.2 19.83 7l-.06.06A1.7 1.7 0 0 0 19.43 9 1.7 1.7 0 0 0 21 10h.08v4H21a1.7 1.7 0 0 0-1.6 1Z" />
            @break

        @case('globe')
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
            @break

        @case('banknote')
            <rect x="3" y="6" width="18" height="12" rx="2" />
            <circle cx="12" cy="12" r="2.5" />
            <path d="M7 9H6v1M17 15h1v-1" />
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m4 7 8 6 8-6" />
            @break

        @case('receipt')
            <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z" />
            <path d="M9 8h6M9 12h6M9 16h3" />
            @break

        @case('percent')
            <path d="m19 5-14 14" />
            <circle cx="7" cy="7" r="2" />
            <circle cx="17" cy="17" r="2" />
            @break

        @case('folder')
            <path d="M3 6.5A1.5 1.5 0 0 1 4.5 5H9l2 2h8.5A1.5 1.5 0 0 1 21 8.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-11Z" />
            @break

        @case('cube')
            <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" />
            <path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12.1V21" />
            @break

        @case('archive')
            <path d="M4 8h16v12H4V8ZM3 4h18v4H3V4Z" />
            <path d="M9 12h6" />
            @break

        @case('shopping-bag')
            <path d="M5 8h14l-1 12H6L5 8Z" />
            <path d="M9 10V7a3 3 0 0 1 6 0v3" />
            @break

        @case('users')
            <circle cx="9" cy="8" r="3" />
            <path d="M3.5 20v-2a5.5 5.5 0 0 1 11 0v2M16 5.2a3 3 0 0 1 0 5.6M17 14a5 5 0 0 1 3.5 4.8V20" />
            @break

        @case('ticket')
            <path d="M4 6h16v4a2 2 0 0 0 0 4v4H4v-4a2 2 0 0 0 0-4V6Z" />
            <path d="M13 8v2M13 14v2" />
            @break

        @case('image')
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <circle cx="8.5" cy="9" r="1.5" />
            <path d="m5 17 4.5-4.5 3 3 2-2L19 18" />
            @break

        @case('chart-bar')
            <path d="M4 20V10h4v10M10 20V4h4v16M16 20v-7h4v7M3 20h18" />
            @break

        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
