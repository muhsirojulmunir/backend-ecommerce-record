<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteSetting\UpdateWebsiteSettingRequest;
use App\Services\Admin\WebsiteSettingService;
use Illuminate\Http\JsonResponse;

class WebsiteSettingController extends Controller
{
    protected $settingService;

    public function __construct(WebsiteSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index(): JsonResponse
    {
        $settings = $this->settingService->getSettings();

        return response()->json([
            'settings' => $settings,
        ]);
    }

    public function update(UpdateWebsiteSettingRequest $request): JsonResponse
    {
        $settings = $this->settingService->updateSettings($request->validated());

        return response()->json([
            'message' => 'Pengaturan website berhasil disimpan.',
            'settings' => $settings,
        ]);
    }
}
