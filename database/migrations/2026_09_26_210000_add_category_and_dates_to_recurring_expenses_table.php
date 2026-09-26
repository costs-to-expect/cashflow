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
            $table->string('category_id')->nullable()->after('total');
            $table->string('subcategory_id')->nullable()->after('category_id');
            $table->date('starts_on')->nullable()->after('day_of_month');
            $table->date('ends_on')->nullable()->after('starts_on');
        });

        // Existing rows have no starts_on - backfill from their creation date
        // so the "first occurrence" math has a sensible reference point. The
        // column stays nullable at the DB level (changing it needs
        // doctrine/dbal, which this app doesn't depend on); every write path
        // always supplies a value, so it's effectively required.
        DB::table('recurring_expenses')->whereNull('starts_on')->update([
            'starts_on' => DB::raw('DATE(created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('recurring_expenses', function (Blueprint $table) {
            $table->dropColumn(['category_id', 'subcategory_id', 'starts_on', 'ends_on']);
        });
    }
};
