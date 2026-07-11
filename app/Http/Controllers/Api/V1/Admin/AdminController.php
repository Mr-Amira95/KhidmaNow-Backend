<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::where('user_type', 'admin')->with('roles');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(AdminResource::class, $query->latest());
    }

    public function store(StoreAdminRequest $request)
    {
        $admin = User::create([
            'name'           => $request->name,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'password'       => $request->password,
            'user_type'      => 'admin',
            'is_super_admin' => false,
            'status'         => $request->status ?? 'active',
        ]);

        UserRole::create(['user_id' => $admin->id, 'role_id' => $request->role_id]);

        $admin->load('roles');
        return $this->success(new AdminResource($admin), 'Admin created successfully.', 201);
    }

    public function show(User $admin)
    {
        $this->ensureAdmin($admin);

        $admin->load('roles.permissions');
        return $this->success(new AdminResource($admin));
    }

    public function update(UpdateAdminRequest $request, User $admin)
    {
        $this->ensureAdmin($admin);

        $data = $request->safe()->except(['password', 'role_id']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $admin->update($data);

        if ($request->filled('role_id') && !$admin->is_super_admin) {
            UserRole::where('user_id', $admin->id)->delete();
            UserRole::create(['user_id' => $admin->id, 'role_id' => $request->role_id]);
        }

        $admin->load('roles');
        return $this->success(new AdminResource($admin), 'Admin updated successfully.');
    }

    public function destroy(Request $request, User $admin)
    {
        $this->ensureAdmin($admin);

        abort_if($admin->is_super_admin, 403, 'Super admins cannot be deleted.');
        abort_if($admin->id === $request->user()->id, 403, 'You cannot delete your own account.');

        $admin->delete();
        return $this->success([], 'Admin deleted successfully.');
    }

    private function ensureAdmin(User $admin): void
    {
        abort_if($admin->user_type !== 'admin', 404);
    }
}
