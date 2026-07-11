<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeUserPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\WishlistResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\ResolvesWishlistItems;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse, ResolvesWishlistItems;

    public function index(Request $request)
    {
        $query = User::where('user_type', '!=', 'admin');

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
        $this->rejectAdmin($user);

        $user->load('provider');
        return $this->success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->rejectAdmin($user);

        $user->update($request->validated());
        return $this->success(new UserResource($user), 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->rejectAdmin($user);

        $user->delete();
        return $this->success([], 'User deleted successfully.');
    }

    public function block(User $user)
    {
        $this->rejectAdmin($user);

        $user->update(['status' => 'blocked']);
        return $this->success(new UserResource($user), 'User blocked successfully.');
    }

    public function unblock(User $user)
    {
        $this->rejectAdmin($user);

        $user->update(['status' => 'active']);
        return $this->success(new UserResource($user), 'User unblocked successfully.');
    }

    public function changePassword(ChangeUserPasswordRequest $request, User $user)
    {
        $this->rejectAdmin($user);

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        return $this->success(new UserResource($user), 'Password changed successfully.');
    }

    public function wishlist(Request $request, User $user)
    {
        $this->rejectAdmin($user);

        $query = Wishlist::where('user_id', $user->id);

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        return $this->paginated(
            WishlistResource::class,
            $query->latest(),
            15,
            fn ($items) => $this->hydrateWishlistItems($items)
        );
    }

    private function rejectAdmin(User $user): void
    {
        abort_if($user->user_type === 'admin', 403, 'Forbidden.');
    }
}
