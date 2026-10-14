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
        Schema::create('info_popups', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('video_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('info_popup_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('info_popup_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('body');
            $table->timestamps();

            $table->unique(['info_popup_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_popup_translations');
        Schema::dropIfExists('info_popups');
    }
};
