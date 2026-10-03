<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
        });

        Schema::create('media_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('type', 10)->default('photo'); // photo | video | embed
            $t->string('title');
            $t->text('caption')->nullable();
            $t->string('alt_text')->nullable();
            $t->string('video_url')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->boolean('is_published')->default(true);
            $t->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $t) {
            $t->id();
            $t->string('site_name');
            $t->string('tagline')->nullable();
            $t->string('hero_title')->nullable();
            $t->text('hero_subtitle')->nullable();
            $t->string('about_title')->nullable();
            $t->text('about_text')->nullable();
            $t->string('contact_email')->nullable();
            $t->string('city')->nullable();
            $t->string('instagram')->nullable();
            $t->string('youtube')->nullable();
            $t->string('vimeo')->nullable();
            $t->string('tiktok')->nullable();
            $t->string('linkedin')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('seo_description', 200)->nullable();
            $t->string('about_seo_title')->nullable();
            $t->string('about_seo_description', 200)->nullable();
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->text('message');
            $t->string('ip', 45)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('categories');
    }
};