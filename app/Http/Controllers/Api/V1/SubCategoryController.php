<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = SubCategory::where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return $this->success(SubCategoryResource::collection($query->latest()->get()));
    }

    public function byCategory(Category $category)
    {
        $subCategories = SubCategory::where('category_id', $category->id)
            ->where('is_active', true)
            ->with('providers.user', 'providers.city')
            ->latest()
            ->get();

        return $this->success(SubCategoryResource::collection($subCategories));
    }
}
