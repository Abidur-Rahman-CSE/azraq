<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::share('brandConfig', config('brand'));
        View::share('commerceConfig', config('commerce'));

        $settings = [];

        if (Schema::hasTable('settings')) {
            $settings = $this->cachedStorefrontSettings();
        }

        View::share('siteSettings', $settings);
    }

    private function cachedStorefrontSettings(): array
    {
        $cacheKey = 'storefront.settings';
        $cached = Cache::get($cacheKey);
        $normalized = $this->normalizeSettingsPayload($cached);

        if ($normalized !== null) {
            return $normalized;
        }

        $fresh = Setting::query()
            ->where('group', 'storefront')
            ->pluck('value', 'key')
            ->all();

        Cache::put($cacheKey, $fresh, now()->addMinutes(10));

        return $fresh;
    }

    private function normalizeSettingsPayload(mixed $payload): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_object($payload)) {
            return null;
        }

        $properties = get_object_vars($payload);

        if (isset($properties['items']) && is_array($properties['items'])) {
            return $properties['items'];
        }

        return null;
    }
}
