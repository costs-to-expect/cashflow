<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_types', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('name');
            $table->string('description');
            $table->string('item_type');
            $table->string('api_resource_type_id');
            $table->string('api_item_type_id');
            $table->string('item_subtype_id');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_types');
    }
};
