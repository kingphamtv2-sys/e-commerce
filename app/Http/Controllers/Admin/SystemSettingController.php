<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingRequest;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(SystemSettingService $settingsService): View
    {
        $defaults = collect(SystemSettingService::DEFINITIONS)
            ->mapWithKeys(static fn (array $definition, string $key): array => [$key => $definition['default']])
            ->all();

        return view('admin.settings.edit', [
            'settings' => array_replace($defaults, $settingsService->all()),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request, SystemSettingService $settingsService): RedirectResponse
    {
        $values = Arr::only($request->validated(), array_keys(SystemSettingService::DEFINITIONS));

        DB::transaction(function () use ($values, $settingsService): void {
            foreach ($values as $key => $value) {
                $definition = SystemSettingService::DEFINITIONS[$key];

                $settingsService->set(
                    $key,
                    $value,
                    $definition['type'],
                    $definition['group'],
                    $definition['public'],
                );
            }
        });

        $settingsService->clearCache();

        return back()->with('success', __('admin.messages.settings_updated'));
    }
}
