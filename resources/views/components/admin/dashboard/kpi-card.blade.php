@props(['label', 'value', 'hint', 'href', 'tone' => 'indigo', 'icon' => 'chart-bar'])
<a href="{{ $href }}" class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg">
    <div @class([
        'absolute -right-8 -top-8 h-28 w-28 rounded-full opacity-10 transition group-hover:scale-110',
        'bg-indigo-500' => $tone === 'indigo', 'bg-emerald-500' => $tone === 'emerald',
        'bg-amber-500' => $tone === 'amber', 'bg-rose-500' => $tone === 'rose',
        'bg-sky-500' => $tone === 'sky', 'bg-violet-500' => $tone === 'violet',
    ])></div>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-slate-500">{{ $label }}</p>
            <p class="mt-3 truncate text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">{{ $value }}</p>
        </div>
        <span @class([
            'grid h-11 w-11 shrink-0 place-items-center rounded-xl',
            'bg-indigo-50 text-indigo-700' => $tone === 'indigo', 'bg-emerald-50 text-emerald-700' => $tone === 'emerald',
            'bg-amber-50 text-amber-700' => $tone === 'amber', 'bg-rose-50 text-rose-700' => $tone === 'rose',
            'bg-sky-50 text-sky-700' => $tone === 'sky', 'bg-violet-50 text-violet-700' => $tone === 'violet',
        ])><x-admin.icon :name="$icon" /></span>
    </div>
    <p class="mt-3 flex items-center justify-between gap-2 text-xs text-slate-400">
        <span>{{ $hint }}</span><span class="font-bold text-indigo-600 transition group-hover:translate-x-0.5">→</span>
    </p>
</a>
