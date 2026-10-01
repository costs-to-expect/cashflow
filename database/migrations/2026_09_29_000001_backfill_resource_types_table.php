<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Preserves the single resource type every existing install already
     * operates under (config('app.api.resource_type_id') etc, set via
     * .env) now that resource types are stored locally rather than fixed
     * in config - a fresh install with none of these set gets no row and
     * starts empty, picking a resource type via the new create screen.
     */
    public function up(): void
    {
        $resourceTypeId = config('app.api.resource_type_id');

        if (blank($resourceTypeId)) {
            return;
        }

        DB::table('resource_types')->insert([
            'user_id' => null,
            'name' => 'Kids',
            'description' => 'Migrated from the single resource type this app used to be fixed to.',
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => $resourceTypeId,
            'api_item_type_id' => config('app.api.item_type_id'),
            'item_subtype_id' => config('app.api.item_subtype_id'),
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $resourceTypeId = config('app.api.resource_type_id');

        if (blank($resourceTypeId)) {
            return;
        }

        DB::table('resource_types')->where('api_resource_type_id', $resourceTypeId)->delete();
    }
};
