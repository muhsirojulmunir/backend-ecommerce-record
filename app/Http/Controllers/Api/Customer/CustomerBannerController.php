<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Http\Resources\Customer\BannerResource;
use Illuminate\Http\JsonResponse;

class CustomerBannerController extends Controller
{
    protected $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function index(): JsonResponse
    {
        $banners = $this->bannerRepository->getActive();

        return response()->json([
            'banners' => BannerResource::collection($banners),
        ]);
    }
}
