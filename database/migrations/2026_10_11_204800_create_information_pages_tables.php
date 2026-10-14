<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('information_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('information_page_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('information_page_id');
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->longText('body')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamps();

            $table->foreign('information_page_id', 'info_page_trans_page_fk')
                ->references('id')
                ->on('information_pages')
                ->cascadeOnDelete();
            $table->unique(['information_page_id', 'locale'], 'info_page_locale_unique');
            $table->unique(['locale', 'slug'], 'info_page_locale_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('information_page_translations');
        Schema::dropIfExists('information_pages');
    }
};
