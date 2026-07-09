<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index(Request $request)
    {
        $query = Category::withCount('subCategories');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(CategoryResource::class, $query->latest());
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storeUpload($request->file('icon'), 'categories');
        }

        $category = Category::create($data)->refresh();
        return $this->success(new CategoryResource($category), 'Category created successfully.', 201);
    }

    public function show(Category $category)
    {
        $category->load('subCategories');
        return $this->success(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->storeUpload($request->file('icon'), 'categories');
        }

        $category->update($data);
        return $this->success(new CategoryResource($category), 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return $this->success([], 'Category deleted successfully.');
    }
}
