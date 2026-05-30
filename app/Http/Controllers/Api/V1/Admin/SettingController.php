<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Http\Traits\ApiResponse;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Setting::with('country');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('search')) {
            $query->where('key', 'like', "%{$request->search}%");
        }

        $settings = $query->get();
        return $this->success(SettingResource::collection($settings));
    }

    public function update(UpdateSettingRequest $request)
    {
        foreach ($request->settings as $item) {
            Setting::where('key', $item['key'])->update(['value' => $item['value']]);
        }

        return $this->success([], 'Settings updated successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key'        => 'required|string|unique:settings,key',
            'value'      => 'nullable|string',
            'type'       => 'required|in:string,text,number,boolean,json,file',
            'country_id' => 'nullable|integer|exists:countries,id',
        ]);

        $setting = Setting::create($request->only('key', 'value', 'type', 'country_id'));
        return $this->success(new SettingResource($setting), 'Setting created successfully.', 201);
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();
        return $this->success([], 'Setting deleted successfully.');
    }
}
