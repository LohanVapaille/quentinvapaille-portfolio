<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\MediaItem;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = ['is_visible' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $category) {
            $category->position = (static::max('position') ?? 0) + 1;
            $category->slug = $category->slug ?: Str::slug($category->name);
        });
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class);
    }
}