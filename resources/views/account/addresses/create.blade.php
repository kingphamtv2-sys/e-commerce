@extends('layouts.account')
@section('title', __('account.add_address').' - '.$siteName)
@section('account-title', __('account.add_address'))
@section('account-content')
<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    @include('account.addresses._form', ['action' => route('account.addresses.store')])
</section>
@endsection
