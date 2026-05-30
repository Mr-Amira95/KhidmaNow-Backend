<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(UserResource::class, $query->latest());
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        return $this->success(new UserResource($user), 'User created successfully.', 201);
    }

    public function show(User $user)
    {
        $user->load('provider');
        return $this->success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return $this->success(new UserResource($user), 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->success([], 'User deleted successfully.');
    }

    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);
        return $this->success(new UserResource($user), 'User blocked successfully.');
    }

    public function unblock(User $user)
    {
        $user->update(['status' => 'active']);
        return $this->success(new UserResource($user), 'User unblocked successfully.');
    }
}
