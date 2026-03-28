<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AdminAppSettingsController extends Controller
{
    public function edit()
    {
        $settings = AppSetting::query()->orderBy('id')->first();

        return view('admin.app-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $existing = AppSetting::query()->orderBy('id')->first();
        if ($existing) {
            $existing->update($data);
        } else {
            AppSetting::query()->create($data);
        }

        return redirect()->route('admin.app-settings.edit')->with('status', 'Pengaturan aplikasi disimpan.');
    }
}
