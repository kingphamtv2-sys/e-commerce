<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('My Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="space-y-2 p-6 text-gray-900">
                    <p><strong>{{ __('Name') }}:</strong> {{ auth()->user()->name }}</p>
                    <p><strong>{{ __('Email') }}:</strong> {{ auth()->user()->email }}</p>
                    <p><strong>{{ __('Role') }}:</strong> {{ auth()->user()->role }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
