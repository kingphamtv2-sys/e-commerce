<section id="order-management" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <div>
        <h2 class="text-lg font-extrabold text-slate-950">{{ __('admin.orders.management') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('admin.orders.management_help') }}</p>
    </div>

    @if(count($service->allowedTransitions($order)))
        <form data-order-action action="{{ route('admin.orders.status.update', $order) }}" method="POST" class="space-y-3">
            @csrf @method('PATCH')
            <label class="block text-sm font-bold text-slate-700">{{ __('admin.orders.next_status') }}</label>
            <select name="status" class="w-full rounded-xl border-slate-300 text-sm">
                @foreach($service->allowedTransitions($order) as $status)<option value="{{ $status }}">{{ __('admin.orders.status_'.$status) }}</option>@endforeach
            </select>
            <textarea name="note" rows="2" placeholder="{{ __('admin.orders.status_note') }}" class="w-full rounded-xl border-slate-300 text-sm"></textarea>
            <div data-order-errors class="hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"></div>
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">{{ __('admin.orders.update_status') }}</button>
        </form>
    @else
        <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">{{ __('admin.orders.no_transition') }}</p>
    @endif

    @if($order->payment_method === 'cod')
        <div class="border-t border-slate-200 pt-5">
            <h3 class="text-sm font-extrabold text-slate-900">{{ __('admin.orders.cod_payment') }}</h3>
            @if(count($service->allowedPaymentTransitions($order)))
                <form data-order-action action="{{ route('admin.orders.payment.update', $order) }}" method="POST" class="mt-3 space-y-3">
                    @csrf @method('PATCH')
                    <select name="payment_status" class="w-full rounded-xl border-slate-300 text-sm">
                        @foreach($service->allowedPaymentTransitions($order) as $status)<option value="{{ $status }}">{{ __('admin.orders.payment_'.$status) }}</option>@endforeach
                    </select>
                    <textarea name="note" rows="2" placeholder="{{ __('admin.orders.payment_note') }}" class="w-full rounded-xl border-slate-300 text-sm"></textarea>
                    <div data-order-errors class="hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"></div>
                    <div class="flex gap-2">
                        <button class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 disabled:opacity-60">{{ __('admin.orders.update_payment') }}</button>
                        @if(in_array('paid', $service->allowedPaymentTransitions($order), true))
                            <button type="button" data-open-mark-paid class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-60">{{ __('admin.orders.mark_paid') }}</button>
                        @endif
                    </div>
                </form>
            @else
                <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600">{{ __('admin.orders.payment_locked') }}</p>
            @endif
        </div>
    @endif

    @if(count($service->allowedFulfillmentTransitions($order)))
        <div class="border-t border-slate-200 pt-5">
            <h3 class="text-sm font-extrabold text-slate-900">{{ __('admin.orders.fulfillment') }}</h3>
            <form data-order-action action="{{ route('admin.orders.fulfillment.update', $order) }}" method="POST" class="mt-3 space-y-3">
                @csrf @method('PATCH')
                <select name="fulfillment_status" class="w-full rounded-xl border-slate-300 text-sm">
                    @foreach($service->allowedFulfillmentTransitions($order) as $status)<option value="{{ $status }}">{{ __('admin.orders.fulfillment_'.$status) }}</option>@endforeach
                </select>
                <textarea name="note" rows="2" placeholder="{{ __('admin.orders.fulfillment_note') }}" class="w-full rounded-xl border-slate-300 text-sm"></textarea>
                <div data-order-errors class="hidden rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"></div>
                <button class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 disabled:opacity-60">{{ __('admin.orders.update_fulfillment') }}</button>
            </form>
        </div>
    @endif

    @if($service->canCancel($order))
        <div class="mt-6 border-t border-rose-200 pt-6">
            <h3 class="text-sm font-extrabold text-rose-800">{{ __('admin.orders.danger_zone') }}</h3>
            <p class="mt-1 text-xs text-slate-500">{{ __('admin.orders.cancel_help') }}</p>
            <button type="button" data-open-order-cancel class="mt-3 w-full rounded-xl border border-rose-300 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 hover:bg-rose-100">{{ __('admin.orders.cancel_order') }}</button>
        </div>
    @endif
</section>
