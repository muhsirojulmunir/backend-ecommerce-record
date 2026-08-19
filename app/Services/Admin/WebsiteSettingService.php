<?php

namespace App\Services\Admin;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;

class WebsiteSettingService
{
    public function getSettings()
    {
        return WebsiteSetting::all()->groupBy('group');
    }

    public function updateSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            $dbSetting = WebsiteSetting::where('key', $key)->first();

            if ($dbSetting) {
                // If it is an image and uploaded file is passed
                if ($dbSetting->type === 'image' && is_object($value)) {
                    if ($dbSetting->value) {
                        Storage::disk('public')->delete($dbSetting->value);
                    }
                    $value = $value->store('settings', 'public');
                }

                $dbSetting->value = is_array($value) ? json_encode($value) : $value;
                $dbSetting->save();
            }
        }

        return WebsiteSetting::all()->groupBy('group');
    }
}
