<?php

namespace App\Repositories;

use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;

class BannerRepository implements BannerRepositoryInterface
{
    public function all(array $filters = [])
    {
        $query = Banner::query();

        if (isset($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->orderBy('id', 'desc')->get();
    }

    public function findById(int $id)
    {
        return Banner::findOrFail($id);
    }

    public function create(array $data)
    {
        return Banner::create($data);
    }

    public function update(int $id, array $data)
    {
        $banner = Banner::findOrFail($id);
        $banner->update($data);
        return $banner;
    }

    public function delete(int $id): bool
    {
        $banner = Banner::findOrFail($id);
        return $banner->delete();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = Banner::query();

        if (isset($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function getActive()
    {
        return Banner::active()->orderBy('sort_order')->get();
    }
}
