<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Enregistre les dimensions des photos (évite les sauts de mise en page).
        Event::listen(MediaHasBeenAddedEvent::class, function (MediaHasBeenAddedEvent $event) {
            $media = $event->media;

            if ($media->collection_name === 'photo' && str_starts_with((string) $media->mime_type, 'image/')) {
                $size = @getimagesize($media->getPath());
                if ($size) {
                    $media->setCustomProperty('width', $size[0]);
                    $media->setCustomProperty('height', $size[1]);
                    $media->saveQuietly();
                }
            }
        });

        View::composer(['layouts.app', 'home', 'about'], function ($view) {
            $view->with('site', SiteSetting::current());
        });
    }
}