<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\StoreBannerRequest;
use App\Http\Requests\Admin\Banner\UpdateBannerRequest;
use App\Services\Admin\BannerService;
use App\Http\Resources\Admin\BannerResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(Request $request): JsonResponse
    {
        $banners = $this->bannerService->getAllBanners($request->all());

        return response()->json([
            'banners' => BannerResource::collection($banners),
        ]);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $banner = $this->bannerService->createBanner($request->validated());

        return response()->json([
            'message' => 'Banner berhasil ditambahkan.',
            'banner' => new BannerResource($banner),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $banner = $this->bannerService->getBannerById($id);

        return response()->json([
            'banner' => new BannerResource($banner),
        ]);
    }

    public function update(UpdateBannerRequest $request, int $id): JsonResponse
    {
        $banner = $this->bannerService->updateBanner($id, $request->validated());

        return response()->json([
            'message' => 'Banner berhasil diperbarui.',
            'banner' => new BannerResource($banner),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bannerService->deleteBanner($id);

        return response()->json([
            'message' => 'Banner berhasil dihapus.',
        ]);
    }
}
