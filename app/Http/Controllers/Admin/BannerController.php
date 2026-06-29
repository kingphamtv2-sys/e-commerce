<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(Request $request, BannerService $service): View
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'in:'.implode(',', BannerService::POSITIONS)],
            'status' => ['nullable', 'in:0,1'],
            'schedule' => ['nullable', 'in:active_now,scheduled,expired'],
        ]);

        return view('admin.banners.index', [
            'banners' => $service->paginate($filters),
            'positions' => BannerService::POSITIONS,
            'service' => $service,
            'filters' => $filters,
        ]);
    }

    public function create(LanguageService $languages): View
    {
        return view('admin.banners.create', $this->formData(
            new Banner(['position' => 'catalog_top', 'link_target' => 'same_tab', 'sort_order' => 0, 'status' => true]),
            $languages,
        ));
    }

    public function store(StoreBannerRequest $request, BannerService $service): RedirectResponse
    {
        $banner = $service->create($request->validated());

        return redirect()->route('admin.banners.edit', $banner)->with('success', __('admin.banners.created'));
    }

    public function edit(Banner $banner, LanguageService $languages): View
    {
        return view('admin.banners.edit', $this->formData($banner, $languages));
    }

    public function update(UpdateBannerRequest $request, Banner $banner, BannerService $service): RedirectResponse
    {
        $service->update($banner, $request->validated());

        return redirect()->route('admin.banners.edit', $banner)->with('success', __('admin.banners.updated_message'));
    }

    public function destroy(Banner $banner, BannerService $service): JsonResponse|RedirectResponse
    {
        $id = $banner->id;
        $service->delete($banner);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => __('admin.banners.deleted'), 'banner_id' => $id]);
        }

        return redirect()->route('admin.banners.index')->with('success', __('admin.banners.deleted'));
    }

    private function formData(Banner $banner, LanguageService $languages): array
    {
        return [
            'banner' => $banner,
            'languages' => collect($languages->active()),
            'defaultLanguage' => $languages->getDefault(),
            'translations' => $banner->exists ? $banner->translations()->get()->keyBy('language_code') : collect(),
            'positions' => BannerService::POSITIONS,
            'bannerService' => app(BannerService::class),
        ];
    }
}
