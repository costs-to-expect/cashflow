<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('resource_type_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        $this->backfill();

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->unique(['resource_type_id', 'key']);
        });
    }

    public function down(): void
    {
        // The composite unique index is what backs the foreign key, so MySQL
        // won't let it be dropped until the constraint itself is gone first.
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['resource_type_id']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['resource_type_id', 'key']);
            $table->unique('key');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('resource_type_id');
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

        DB::table('settings')->whereNull('resource_type_id')->update([
            'resource_type_id' => $resourceTypeId,
        ]);
    }
};
