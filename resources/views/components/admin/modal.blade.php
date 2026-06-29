@props(['id', 'title', 'size' => 'max-w-3xl'])
<div x-data="{ open: false }" class="inline-flex">
    <span @click="open = true" class="inline-flex">{{ $trigger }}</span>
    <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-[70] grid place-items-center p-4" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="open = false"></div>
        <div data-modal-panel x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-3 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100" x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0 scale-100 opacity-100" x-transition:leave-end="translate-y-3 scale-95 opacity-0" class="relative z-10 flex max-h-[90vh] w-full {{ $size }} flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4"><h2 id="{{ $id }}-title" class="text-lg font-extrabold text-slate-950">{{ $title }}</h2><button data-modal-close type="button" @click="open = false" class="grid h-9 w-9 place-items-center rounded-lg text-xl text-slate-500 hover:bg-slate-100" aria-label="{{ __('admin.common.close') }}">×</button></div>
            <div class="overflow-y-auto p-6">{{ $slot }}</div>
        </div>
    </div>
</div>
