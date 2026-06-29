<nav class="mb-4 flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
    <a href="{{ route('admin.dashboard') }}" class="font-medium transition hover:text-indigo-600">{{ __('admin.common.home') }}</a>
    <svg class="h-4 w-4 text-slate-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
    </svg>
    <span class="font-semibold text-slate-700">@yield('title', 'Admin')</span>
</nav>
