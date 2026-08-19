<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    protected $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function getAllBanners(array $filters = [])
    {
        return $this->bannerRepository->all($filters);
    }

    public function getBannerById(int $id)
    {
        return $this->bannerRepository->findById($id);
    }

    public function createBanner(array $data)
    {
        if (isset($data['uploaded_image'])) {
            $data['image'] = $data['uploaded_image']->store('banners', 'public');
        }

        return $this->bannerRepository->create($data);
    }

    public function updateBanner(int $id, array $data)
    {
        $banner = $this->bannerRepository->findById($id);

        if (isset($data['uploaded_image'])) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $data['uploaded_image']->store('banners', 'public');
        }

        return $this->bannerRepository->update($id, $data);
    }

    public function deleteBanner(int $id): bool
    {
        $banner = $this->bannerRepository->findById($id);
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        return $this->bannerRepository->delete($id);
    }
}
