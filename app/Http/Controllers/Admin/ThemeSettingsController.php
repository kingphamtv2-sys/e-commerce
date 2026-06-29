<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateThemeSettingsRequest;
use App\Services\ThemeSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeSettingsController extends Controller
{
    public function edit(ThemeSettingService $theme): View
    {
        return view('admin.theme.edit', [
            'themeValues' => $theme->all(),
            'themeGroups' => $theme->grouped(),
            'themeService' => $theme,
        ]);
    }

    public function update(UpdateThemeSettingsRequest $request, ThemeSettingService $theme): RedirectResponse
    {
        $theme->update($request->validated(), $this->canUpdateCss($request));

        return back()->with('success', __('admin.theme.updated'));
    }

    public function reset(Request $request, ThemeSettingService $theme): RedirectResponse
    {
        abort_unless($request->user() && in_array($request->user()->role, ['super_admin', 'admin'], true), 403);

        $theme->reset();

        return back()->with('success', __('admin.theme.reset_done'));
    }

    private function canUpdateCss(Request $request): bool
    {
        return $request->user() && in_array($request->user()->role, ['super_admin', 'admin'], true);
    }
}
