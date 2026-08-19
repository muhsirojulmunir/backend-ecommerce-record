<?php

namespace App\Services\Customer;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getActiveProducts(array $filters = [])
    {
        // Public customer can only see active products
        $filters['status'] = 'active';
        return $this->productRepository->paginate(16, $filters);
    }

    public function getProductDetailBySlug(string $slug)
    {
        return $this->productRepository->findBySlug($slug);
    }
}
