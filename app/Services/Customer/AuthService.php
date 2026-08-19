<?php

namespace App\Services\Customer;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'customer';

        $user = $this->userRepository->create($data);

        // Assign Spatie Role
        $role = Role::findOrCreate('customer', 'web');
        $user->assignRole($role);

        $token = $user->createToken('customer_token', ['customer-access'])->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials)
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        if ($user->role !== 'customer') {
            throw ValidationException::withMessages([
                'email' => ['Akses ditolak. Endpoint ini khusus untuk Customer.'],
            ]);
        }

        // Akun yang diblokir admin lewat menu Kelola Customer tidak boleh masuk
        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda sedang diblokir. Silakan hubungi customer service toko.'],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('customer_token', ['customer-access'])->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }
}
