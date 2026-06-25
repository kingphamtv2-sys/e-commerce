@props(['label', 'value', 'hint' => null, 'tone' => 'indigo'])

@php
    $tones = [
        'indigo' => 'bg-indigo-50 text-indigo-700',
        'emerald' => 'bg-emerald-50 text-emerald-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'rose' => 'bg-rose-50 text-rose-700',
        'sky' => 'bg-sky-50 text-sky-700',
        'violet' => 'bg-violet-50 text-violet-700',
    ];
@endphp

<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-extrabold {{ $tones[$tone] ?? $tones['indigo'] }}">{{ $label }}</span>
    <p class="mt-4 break-words text-2xl font-black tracking-tight text-slate-950">{{ $value }}</p>
    @if($hint)<p class="mt-1 text-xs leading-5 text-slate-500">{{ $hint }}</p>@endif
</article>
