<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaItem extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = ['is_published' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if (!$item->position) {
                $item->position = (static::max('position') ?? 0) + 1;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('video')->singleFile()
            ->acceptsMimeTypes(['video/mp4', 'video/webm']);
        $this->addMediaCollection('poster')->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('photo', 'poster')
            ->width(640)->format('webp')->nonQueued();

        $this->addMediaConversion('large')
            ->performOnCollections('photo', 'poster')
            ->width(1600)->format('webp')->nonQueued();
    }

    /* ---------- Accessors ---------- */

    protected function displayAlt(): Attribute
    {
        return Attribute::get(fn() => $this->alt_text ?: $this->title);
    }

    protected function thumbUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->type === 'photo') {
                return $this->getFirstMediaUrl('photo', 'thumb') ?: null;
            }
            if ($poster = $this->getFirstMediaUrl('poster', 'thumb')) {
                return $poster;
            }
            return $this->youtubeId() ? "https://i.ytimg.com/vi/{$this->youtubeId()}/hqdefault.jpg" : null;
        });
    }

    protected function largeUrl(): Attribute
    {
        return Attribute::get(function () {
            $collection = $this->type === 'photo' ? 'photo' : 'poster';
            return $this->getFirstMediaUrl($collection, 'large') ?: $this->thumbUrl;
        });
    }

    protected function videoFileUrl(): Attribute
    {
        return Attribute::get(fn() => $this->getFirstMediaUrl('video') ?: null);
    }

    protected function embedUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($id = $this->youtubeId()) {
                return "https://www.youtube-nocookie.com/embed/{$id}?autoplay=1&rel=0";
            }
            if ($id = $this->vimeoId()) {
                return "https://player.vimeo.com/video/{$id}?autoplay=1&dnt=1";
            }
            return null;
        });
    }

    private function youtubeId(): ?string
    {
        if (!$this->video_url) {
            return null;
        }
        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $this->video_url, $m);
        return $m[1] ?? null;
    }

    private function vimeoId(): ?string
    {
        if (!$this->video_url) {
            return null;
        }
        preg_match('~vimeo\.com/(?:video/)?(\d+)~', $this->video_url, $m);
        return $m[1] ?? null;
    }

    /** Données structurées schema.org pour le SEO. */
    public function toSchema(): array
    {
        $description = Str::limit(strip_tags((string) ($this->caption ?: $this->title)), 300);
        $date = $this->created_at?->toIso8601String();

        if ($this->type === 'photo') {
            return array_filter([
                '@type' => 'ImageObject',
                'name' => $this->title,
                'caption' => $this->display_alt,
                'description' => $description,
                'contentUrl' => $this->large_url,
                'thumbnailUrl' => $this->thumb_url,
                'uploadDate' => $date,
            ], fn($v) => filled($v));
        }

        return array_filter([
            '@type' => 'VideoObject',
            'name' => $this->title,
            'description' => $description,
            'thumbnailUrl' => $this->thumb_url,
            'uploadDate' => $date,
            'contentUrl' => $this->type === 'video' ? $this->video_file_url : null,
            'embedUrl' => $this->type === 'embed' ? preg_replace('/\?.*/', '', (string) $this->embed_url) : null,
        ], fn($v) => filled($v));
    }
}
