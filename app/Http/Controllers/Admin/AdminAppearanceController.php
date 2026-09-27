<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAppearanceController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.appearance', [
            'colors' => config('site_theme.colors'),
            'selectedColor' => Cache::rememberForever('site_theme.primary_color', function () {
                return SiteSetting::query()->where('key', 'primary_color')->value('value')
                    ?? config('site_theme.default');
            }),
            'siteIcon' => Cache::rememberForever('site_theme.icon_path', function () {
                return SiteSetting::query()->where('key', 'site_icon')->value('value');
            }),
            'phoneAuthEnabled' => SiteSetting::phoneAuthEnabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'primary_color' => ['required', 'string', 'in:' . implode(',', array_keys(config('site_theme.colors')))],
            'site_icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp,ico', 'max:2048'],
            'phone_auth_enabled' => ['sometimes', 'boolean'],
        ]);

        SiteSetting::query()->updateOrCreate(
            ['key' => 'primary_color'],
            ['value' => $validated['primary_color']],
        );

        Cache::forever('site_theme.primary_color', $validated['primary_color']);

        $phoneAuthEnabled = $request->boolean('phone_auth_enabled');
        SiteSetting::query()->updateOrCreate(
            ['key' => 'phone_auth_enabled'],
            ['value' => $phoneAuthEnabled ? '1' : '0'],
        );
        Cache::forever('site_setting.phone_auth_enabled', $phoneAuthEnabled);

        if ($request->hasFile('site_icon')) {
            $oldPath = SiteSetting::query()->where('key', 'site_icon')->value('value');
            $path = $request->file('site_icon')->storePublicly('site', 'public');
            SiteSetting::query()->updateOrCreate(['key' => 'site_icon'], ['value' => $path]);
            Cache::forever('site_theme.icon_path', $path);

            if ($oldPath && $oldPath !== $path) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return redirect()->route('admin.appearance.edit')->with('status', 'Site settings updated.');
    }
}
