<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Sematkan otomatis data perangkat, IP, dan User-Agent pada setiap aktivitas yang dicatat
        if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
            \Spatie\Activitylog\Models\Activity::creating(function (\Spatie\Activitylog\Models\Activity $activity) {
                if (! app()->runningInConsole() && request()) {
                    $props = is_array($activity->properties)
                        ? $activity->properties
                        : (is_object($activity->properties) ? $activity->properties->toArray() : []);

                    if (! isset($props['ip'])) {
                        $props['ip'] = request()->ip();
                    }
                    if (! isset($props['user_agent'])) {
                        $props['user_agent'] = request()->userAgent();
                    }
                    if (! isset($props['device'])) {
                        $props['device'] = \App\Support\DeviceDetector::detect(request()->userAgent());
                    }
                    $activity->properties = collect($props);
                }
            });
        }
    }
}
