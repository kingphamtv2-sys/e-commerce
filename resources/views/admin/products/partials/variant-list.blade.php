@forelse ($variants as $variant)
    @include('admin.products.partials.variant-row', ['variant' => $variant, 'activeOptions' => $activeOptions])
@empty
    <div data-empty-state class="rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center text-sm text-slate-500">{{ __('admin.variant_combinations.empty') }}</div>
@endforelse
