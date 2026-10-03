<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected static ?self $cached = null;

    /** Ligne unique de réglages (créée automatiquement). */
    public static function current(): self
    {
        return static::$cached ??= static::query()->first() ?? static::create([
            'site_name' => 'Quentin Vapaille',
            'tagline' => 'Photographe & vidéaste',
            'hero_title' => 'Des images qui racontent des histoires',
            'hero_subtitle' => 'Portraits, moments de vie, voyages et vidéos : de la lumière, de la couleur et beaucoup de bonne humeur.',
            'about_title' => 'Salut, moi c\'est Quentin !',
            'about_text' => "Écris ici ta présentation : qui tu es, ta démarche, ce qui te passionne.\n\nTu peux modifier ce texte dans l'administration.",
            'contact_email' => 'contact@example.com',
            'seo_title' => 'Quentin Vapaille — Photographe & vidéaste',
            'seo_description' => 'Portfolio de Quentin Vapaille : photos et vidéos, portraits, événements et voyages.',
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('og_image')->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('avatar')
            ->width(900)->format('webp')->nonQueued();

        $this->addMediaConversion('og')
            ->performOnCollections('og_image')
            ->fit(Fit::Crop, 1200, 630)->format('jpg')->nonQueued();
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn() => $this->getFirstMediaUrl('avatar', 'thumb') ?: null);
    }

    protected function ogImageUrl(): Attribute
    {
        return Attribute::get(fn() => $this->getFirstMediaUrl('og_image', 'og') ?: null);
    }

    /** @return array<string,string> label => url */
    public function socials(): array
    {
        return array_filter([
            'Instagram' => $this->instagram,
            'YouTube' => $this->youtube,
            'Vimeo' => $this->vimeo,
            'TikTok' => $this->tiktok,
            'LinkedIn' => $this->linkedin,
        ]);
    }
}