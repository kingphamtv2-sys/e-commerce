@foreach ($activeOptions as $option)
    @include('admin.products.partials.variant-selector', ['option' => $option])
@endforeach
