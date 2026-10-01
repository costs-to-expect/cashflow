<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Now used as the model's route key (URLs show the API's resource type id
     * rather than this app's own auto-increment id), so it needs to actually
     * be unique at the DB level, not just unique by construction.
     */
    public function up(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->unique('api_resource_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('resource_types', function (Blueprint $table) {
            $table->dropUnique(['api_resource_type_id']);
        });
    }
};
