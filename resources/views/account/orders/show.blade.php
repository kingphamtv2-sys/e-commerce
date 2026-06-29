@extends($guestView ? 'layouts.public' : 'layouts.account')

@section('title', $order->order_code.' - '.$siteName)

@if($guestView)
    @section('content')
        <div class="min-h-screen bg-slate-50">
            <section class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6 lg:px-8">
                @include('account.orders._detail')
            </section>
        </div>
    @endsection
@else
    @section('account-title', __('account.order_detail'))
    @section('account-content')
        @include('account.orders._detail')
    @endsection
@endif
