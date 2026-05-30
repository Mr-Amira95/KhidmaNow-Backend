<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Http\Resources\SubCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = SubCategory::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $this->paginated(SubCategoryResource::class, $query->latest());
    }

    public function store(StoreSubCategoryRequest $request)
    {
        $subCategory = SubCategory::create($request->validated());
        $subCategory->load('category');
        return $this->success(new SubCategoryResource($subCategory), 'Sub-category created successfully.', 201);
    }

    public function show(SubCategory $subCategory)
    {
        $subCategory->load('category');
        return $this->success(new SubCategoryResource($subCategory));
    }

    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory)
    {
        $subCategory->update($request->validated());
        $subCategory->load('category');
        return $this->success(new SubCategoryResource($subCategory), 'Sub-category updated successfully.');
    }

    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();
        return $this->success([], 'Sub-category deleted successfully.');
    }
}
