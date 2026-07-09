<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreIntroScreenRequest;
use App\Http\Requests\Admin\UpdateIntroScreenRequest;
use App\Http\Resources\IntroScreenResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\IntroScreen;

class IntroScreenController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index()
    {
        return $this->paginated(IntroScreenResource::class, IntroScreen::orderBy('order'));
    }

    public function store(StoreIntroScreenRequest $request)
    {
        $data = $request->validated();
        $data['image'] = $this->storeUpload($request->file('image'), 'intro-screens');

        $introScreen = IntroScreen::create($data)->refresh();
        return $this->success(new IntroScreenResource($introScreen), 'Intro screen created successfully.', 201);
    }

    public function show(IntroScreen $introScreen)
    {
        return $this->success(new IntroScreenResource($introScreen));
    }

    public function update(UpdateIntroScreenRequest $request, IntroScreen $introScreen)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), 'intro-screens');
        }

        $introScreen->update($data);
        return $this->success(new IntroScreenResource($introScreen), 'Intro screen updated successfully.');
    }

    public function destroy(IntroScreen $introScreen)
    {
        $introScreen->delete();
        return $this->success([], 'Intro screen deleted successfully.');
    }
}
