<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table): void {
            $table->id();
            $table->string('position', 100)->index();
            $table->string('image_path', 500)->nullable();
            $table->string('mobile_image_path', 500)->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('link_target', 20)->default('same_tab');
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('status')->default(true)->index();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('banner_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('banner_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 10)->index();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('button_text', 100)->nullable();
            $table->string('image_alt')->nullable();
            $table->timestamps();
            $table->unique(['banner_id', 'language_code']);
            $table->foreign('language_code')->references('code')->on('languages')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_translations');
        Schema::dropIfExists('banners');
    }
};
