<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_expenses', function (Blueprint $table) {
            $table->foreignId('resource_type_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::table('recurring_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resource_type_id');
        });
    }

    /**
     * Every existing row belonged to the single resource type this app used
     * to be fixed to - point them at whatever the earlier backfill migration
     * created for it (none on a fresh install with no pre-existing config).
     */
    private function backfill(): void
    {
        $apiResourceTypeId = config('app.api.resource_type_id');

        if (blank($apiResourceTypeId)) {
            return;
        }

        $resourceTypeId = DB::table('resource_types')->where('api_resource_type_id', $apiResourceTypeId)->value('id');

        if ($resourceTypeId === null) {
            return;
        }

        DB::table('recurring_expenses')->whereNull('resource_type_id')->update([
            'resource_type_id' => $resourceTypeId,
        ]);
    }
};
