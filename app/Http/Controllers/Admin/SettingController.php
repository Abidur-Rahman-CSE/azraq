<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingUpdateRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::query()->where('group', 'storefront')->pluck('value', 'key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(SettingUpdateRequest $request)
    {
        foreach ($request->validated() as $key => $value) {
            Setting::updateOrCreate(
                ['group' => 'storefront', 'key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated.');
    }
}
