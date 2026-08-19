<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $userRepository;
    protected $reportService;

    public function __construct(UserRepositoryInterface $userRepository, ReportService $reportService)
    {
        $this->userRepository = $userRepository;
        $this->reportService = $reportService;
    }

    public function index(Request $request): JsonResponse
    {
        // Kelola Customer (financials of customer: total orders, total spend)
        if ($request->get('tab') === 'customers_financials') {
            $financials = $this->reportService->getCustomersFinancials($request->all());
            return response()->json([
                'customers_financials' => $financials,
            ]);
        }

        $users = $this->userRepository->paginate(15, $request->all());

        return response()->json([
            'users' => UserResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Only Super Admin can manage Admins
        $currentUser = $request->user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:customer,admin,super_admin',
        ];

        $request->validate($rules);

        if (in_array($request->role, ['admin', 'super_admin']) && !$currentUser->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya Super Admin yang dapat menambahkan akun Admin.'], 403);
        }

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        $user = $this->userRepository->create($data);
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Pengguna berhasil dibuat.',
            'user' => new UserResource($user),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();
        $user = $this->userRepository->findById($id);

        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:customer,admin,super_admin',
        ];

        if ($request->has('password') && $request->password !== null) {
            $rules['password'] = 'required|string|min:8';
        }

        $request->validate($rules);

        // Security check
        if (($request->role === 'admin' || $request->role === 'super_admin' || $user->role === 'admin' || $user->role === 'super_admin') && !$currentUser->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya Super Admin yang dapat mengubah data akun Admin.'], 403);
        }

        $data = $request->only(['name', 'email', 'phone', 'role']);
        if ($request->has('password') && $request->password !== null) {
            $data['password'] = Hash::make($request->password);
        }

        $updatedUser = $this->userRepository->update($id, $data);

        if ($request->has('role')) {
            $updatedUser->syncRoles([$request->role]);
        }

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'user' => new UserResource($updatedUser),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();
        $user = $this->userRepository->findById($id);

        if ($user->isAdmin() && !$currentUser->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya Super Admin yang dapat menghapus akun Admin.'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 400);
        }

        $this->userRepository->delete($id);

        return response()->json([
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}
