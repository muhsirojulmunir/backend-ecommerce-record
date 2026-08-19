<?php

namespace App\Services\Admin;

use App\Repositories\Interfaces\DiscountRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class DiscountService
{
    protected $discountRepository;
    protected $productRepository;

    public function __construct(
        DiscountRepositoryInterface $discountRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->discountRepository = $discountRepository;
        $this->productRepository = $productRepository;
    }

    public function getAllDiscounts(array $filters = [])
    {
        return $this->discountRepository->all($filters);
    }

    public function getDiscountById(int $id)
    {
        return $this->discountRepository->findById($id);
    }

    public function setDiscount(array $data)
    {
        // Check if there is already a discount for the product
        $existing = $this->discountRepository->findByProductId($data['product_id']);

        if ($existing) {
            return $this->discountRepository->update($existing->id, [
                'discount_percentage' => $data['discount_percentage'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);
        }

        return $this->discountRepository->create($data);
    }

    public function updateDiscount(int $id, array $data)
    {
        return $this->discountRepository->update($id, $data);
    }

    public function deleteDiscount(int $id): bool
    {
        return $this->discountRepository->delete($id);
    }
}
