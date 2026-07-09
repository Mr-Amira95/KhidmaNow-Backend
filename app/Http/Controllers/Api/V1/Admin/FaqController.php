<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Http\Traits\ApiResponse;
use App\Models\Faq;

class FaqController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->paginated(FaqResource::class, Faq::orderBy('order'));
    }

    public function store(StoreFaqRequest $request)
    {
        $faq = Faq::create($request->validated());
        return $this->success(new FaqResource($faq), 'FAQ created successfully.', 201);
    }

    public function show(Faq $faq)
    {
        return $this->success(new FaqResource($faq));
    }

    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());
        return $this->success(new FaqResource($faq), 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return $this->success([], 'FAQ deleted successfully.');
    }
}
