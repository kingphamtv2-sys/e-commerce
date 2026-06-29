@php($input = 'mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500')
<form method="POST" action="{{ $action }}" class="grid gap-5 sm:grid-cols-2">
    @csrf
    @if($address->exists) @method('PATCH') @endif
    <div>
        <label class="text-sm font-bold text-slate-700">{{ __('account.address_label') }}</label>
        <input name="label" value="{{ old('label', $address->label) }}" placeholder="{{ __('account.address_label_placeholder') }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('label')" class="mt-2"/>
    </div>
    <div>
        <label class="text-sm font-bold text-slate-700">{{ __('account.recipient_name') }}</label>
        <input name="recipient_name" required value="{{ old('recipient_name', $address->recipient_name ?: auth()->user()->name) }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('recipient_name')" class="mt-2"/>
    </div>
    <div>
        <label class="text-sm font-bold text-slate-700">{{ __('account.phone') }}</label>
        <input name="phone" required value="{{ old('phone', $address->phone ?: auth()->user()->phone) }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
    </div>
    <div>
        <label class="text-sm font-bold text-slate-700">{{ __('account.country') }}</label>
        <input name="country" required maxlength="10" value="{{ old('country', $address->country ?: 'VN') }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('country')" class="mt-2"/>
    </div>
    <div class="sm:col-span-2">
        <label class="text-sm font-bold text-slate-700">{{ __('account.address_line_1') }}</label>
        <input name="address_line_1" required value="{{ old('address_line_1', $address->address_line_1) }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('address_line_1')" class="mt-2"/>
    </div>
    <div class="sm:col-span-2">
        <label class="text-sm font-bold text-slate-700">{{ __('account.address_line_2') }}</label>
        <input name="address_line_2" value="{{ old('address_line_2', $address->address_line_2) }}" class="{{ $input }}">
        <x-input-error :messages="$errors->get('address_line_2')" class="mt-2"/>
    </div>
    @foreach(['city', 'district', 'ward', 'postal_code'] as $field)
        <div>
            <label class="text-sm font-bold text-slate-700">{{ __('account.'.$field) }}</label>
            <input name="{{ $field }}" @required($field === 'city') value="{{ old($field, $address->{$field}) }}" class="{{ $input }}">
            <x-input-error :messages="$errors->get($field)" class="mt-2"/>
        </div>
    @endforeach
    <div class="sm:col-span-2 grid gap-3 sm:grid-cols-2">
        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4">
            <input type="checkbox" name="is_default_shipping" value="1" class="rounded border-slate-300 text-indigo-600" @checked(old('is_default_shipping', $address->is_default_shipping))>
            <span class="text-sm font-bold text-slate-700">{{ __('account.make_default_shipping') }}</span>
        </label>
        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4">
            <input type="checkbox" name="is_default_billing" value="1" class="rounded border-slate-300 text-indigo-600" @checked(old('is_default_billing', $address->is_default_billing))>
            <span class="text-sm font-bold text-slate-700">{{ __('account.make_default_billing') }}</span>
        </label>
    </div>
    <div class="sm:col-span-2 flex flex-wrap justify-end gap-3">
        <a href="{{ route('account.addresses.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600">{{ __('account.cancel') }}</a>
        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-extrabold text-white hover:bg-indigo-700">{{ __('account.save_address') }}</button>
    </div>
</form>
