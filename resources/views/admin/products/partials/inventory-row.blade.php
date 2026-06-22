<tr id="inventory-row-{{ $stock->id }}">
    <td class="px-6 py-4 font-bold text-slate-900">{{ $stock->productVariant?->sku ?? $stock->product->sku }}</td>
    <td class="px-6 py-4">{{ $stock->quantity }}</td><td class="px-6 py-4">{{ $stock->reserved_quantity }}</td><td class="px-6 py-4 font-bold">{{ $stock->availableQuantity() }}</td><td class="px-6 py-4">{{ $stock->low_stock_threshold }}</td><td class="px-6 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-700' => $stock->stockStatus() === 'in_stock', 'bg-amber-100 text-amber-700' => $stock->stockStatus() === 'low_stock', 'bg-rose-100 text-rose-700' => $stock->stockStatus() === 'out_of_stock'])>{{ __('admin.inventory.'.$stock->stockStatus()) }}</span></td>
    <td class="px-6 py-4 text-right">
        <x-admin.modal id="adjust-inventory-{{ $stock->id }}" :title="__('admin.inventory.adjust_title')">
            <x-slot:trigger><button type="button" class="rounded-lg border border-indigo-200 px-3 py-2 text-xs font-bold text-indigo-700">{{ __('admin.inventory.adjust') }}</button></x-slot:trigger>
            <form method="POST" action="{{ route('admin.inventory.update', $stock) }}" data-async-create data-replace-target="#inventory-row-{{ $stock->id }}" data-log-target="#inventory-recent-logs" class="grid gap-4 md:grid-cols-2">@csrf
                <div data-async-errors class="hidden md:col-span-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
                <div><label class="text-sm font-semibold">{{ __('admin.inventory.adjustment_type') }} *</label><select name="adjustment_type" class="mt-2 block w-full rounded-xl border-slate-300 text-sm" required><option value="increase">{{ __('admin.inventory.increase') }}</option><option value="decrease">{{ __('admin.inventory.decrease') }}</option><option value="set">{{ __('admin.inventory.set') }}</option></select></div>
                <div><label class="text-sm font-semibold">{{ __('admin.inventory.adjust_quantity') }} *</label><input name="quantity" type="number" min="0" value="0" class="mt-2 block w-full rounded-xl border-slate-300 text-sm" required></div>
                <div><label class="text-sm font-semibold">{{ __('admin.inventory.threshold') }} *</label><input name="low_stock_threshold" type="number" min="0" value="{{ $stock->low_stock_threshold }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm" required></div>
                <div><label class="text-sm font-semibold">{{ __('admin.inventory.reason') }}</label><input name="reason" maxlength="255" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
                <div class="md:col-span-2"><label class="text-sm font-semibold">{{ __('admin.inventory.note') }}</label><textarea name="note" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></textarea></div>
                <div class="flex justify-end md:col-span-2"><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white">{{ __('admin.inventory.save_adjustment') }}</button></div>
            </form>
        </x-admin.modal>
    </td>
</tr>
