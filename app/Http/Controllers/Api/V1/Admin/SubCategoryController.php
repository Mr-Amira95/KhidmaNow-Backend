<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Http\Resources\SubCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index(Request $request)
    {
        $query = SubCategory::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(SubCategoryResource::class, $query->latest());
    }

    public function store(StoreSubCategoryRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storeUpload($request->file('icon'), 'sub-categories');
        }

        $subCategory = SubCategory::create($data)->refresh();
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
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storeUpload($request->file('icon'), 'sub-categories');
        }

        $subCategory->update($data);
        $subCategory->load('category');
        return $this->success(new SubCategoryResource($subCategory), 'Sub-category updated successfully.');
    }

    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();
        return $this->success([], 'Sub-category deleted successfully.');
    }
}
