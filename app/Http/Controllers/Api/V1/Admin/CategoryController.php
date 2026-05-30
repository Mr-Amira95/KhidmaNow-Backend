<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Category::withCount('subCategories');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $this->paginated(CategoryResource::class, $query->latest());
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());
        return $this->success(new CategoryResource($category), 'Category created successfully.', 201);
    }

    public function show(Category $category)
    {
        $category->load('subCategories');
        return $this->success(new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());
        return $this->success(new CategoryResource($category), 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return $this->success([], 'Category deleted successfully.');
    }
}
