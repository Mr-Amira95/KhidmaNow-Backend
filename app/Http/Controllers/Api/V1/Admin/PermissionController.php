<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Traits\ApiResponse;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('key', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(PermissionResource::class, $query->orderBy('name'));
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create($request->validated());
        return $this->success(new PermissionResource($permission), 'Permission created successfully.', 201);
    }

    public function show(Permission $permission)
    {
        return $this->success(new PermissionResource($permission));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->validated());
        return $this->success(new PermissionResource($permission), 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return $this->success([], 'Permission deleted successfully.');
    }
}
