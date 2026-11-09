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
        Schema::create('product_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->unsignedTinyInteger('min_selections')->default(0);
            $table->unsignedTinyInteger('max_selections')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'code']);
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_option_group_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_option_group_id');
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();

            $table->foreign('product_option_group_id', 'pog_trans_group_fk')
                ->references('id')
                ->on('product_option_groups')
                ->cascadeOnDelete();
            $table->unique(['product_option_group_id', 'locale'], 'option_group_locale_unique');
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_option_group_id');
            $table->string('code');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_option_group_id', 'pov_group_fk')
                ->references('id')
                ->on('product_option_groups')
                ->cascadeOnDelete();
            $table->unique(['product_option_group_id', 'code'], 'option_value_code_unique');
            $table->index(['product_option_group_id', 'sort_order'], 'pov_group_sort_idx');
        });

        Schema::create('product_option_value_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_option_value_id');
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();

            $table->foreign('product_option_value_id', 'pov_trans_value_fk')
                ->references('id')
                ->on('product_option_values')
                ->cascadeOnDelete();
            $table->unique(['product_option_value_id', 'locale'], 'option_value_locale_unique');
        });

        Schema::create('variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_option_value_id');
            $table->unsignedInteger('surcharge')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('product_option_value_id', 'vov_option_value_fk')
                ->references('id')
                ->on('product_option_values')
                ->cascadeOnDelete();
            $table->unique(['product_variant_id', 'product_option_value_id'], 'variant_option_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_option_values');
        Schema::dropIfExists('product_option_value_translations');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_option_group_translations');
        Schema::dropIfExists('product_option_groups');
    }
};
