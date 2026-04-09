<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagementController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('users.view');

        $users = User::with(['employee', 'roles'])
            ->when($request->search, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%")->orWhere('email', 'ilike', "%{$v}%"))
            ->when($request->role, fn ($q, $v) => $q->role($v))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        $users->getCollection()->transform(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'full_name' => $user->employee->full_name,
            ] : null,
            'last_login_at' => $user->last_login_at?->toISOString(),
            'created_at' => $user->created_at,
        ]);

        return $this->paginated($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('users.create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'employee_id' => $data['employee_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($data['role']);

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ], 'User berhasil dibuat', 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('users.update');

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'exists:roles,name'],
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $newRole = $data['role'] ?? null;
        unset($data['role']);

        $user->update($data);

        if ($newRole) {
            $user->syncRoles([$newRole]);
        }

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ], 'User diperbarui');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('users.delete');

        if ($user->id === auth()->id()) {
            return $this->error('Tidak bisa menghapus akun sendiri', 422);
        }

        $user->delete();
        return $this->success(null, 'User dihapus');
    }

    public function roles(): JsonResponse
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return $this->success($roles);
    }
}
