<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Traits\ApiResponse;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Role::withCount('rolePermissions');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $this->paginated(RoleResource::class, $query->latest());
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);

        if ($request->filled('permission_ids')) {
            $this->syncPermissions($role, $request->permission_ids);
        }

        $role->load('permissions');
        return $this->success(new RoleResource($role), 'Role created successfully.', 201);
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return $this->success(new RoleResource($role));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        if ($request->filled('name')) {
            $role->update(['name' => $request->name]);
        }
        if ($request->has('permission_ids')) {
            $this->syncPermissions($role, $request->permission_ids ?? []);
        }

        $role->load('permissions');
        return $this->success(new RoleResource($role), 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return $this->success([], 'Role deleted successfully.');
    }

    private function syncPermissions(Role $role, array $permissionIds): void
    {
        RolePermission::where('role_id', $role->id)->delete();
        foreach ($permissionIds as $permId) {
            RolePermission::create(['role_id' => $role->id, 'permission_id' => $permId]);
        }
    }
}
