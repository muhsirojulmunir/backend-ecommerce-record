<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface
{
    public function all(array $filters = []);
    public function findById(int $id);
    public function findByOrderNumber(string $orderNumber);
    public function create(array $data): mixed;
    public function updateStatus(int $id, string $status): bool;
    public function paginate(int $perPage = 15, array $filters = []);
    public function getByUserId(int $userId);
    public function countByStatus(): array;
}
