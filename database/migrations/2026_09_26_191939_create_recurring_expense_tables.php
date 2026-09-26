<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency_id');
            $table->decimal('total', 10, 2);
            $table->string('frequency')->default('monthly');
            $table->unsignedTinyInteger('day_of_month');
            $table->date('next_run_date');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('recurring_expense_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_expense_id')->constrained()->cascadeOnDelete();
            $table->string('resource_id');
            $table->unsignedTinyInteger('percentage');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('recurring_expense_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_expense_id')->constrained()->cascadeOnDelete();
            $table->date('run_date');
            $table->json('created_item_ids');
            $table->timestamps();

            $table->unique(['recurring_expense_id', 'run_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expense_runs');
        Schema::dropIfExists('recurring_expense_allocations');
        Schema::dropIfExists('recurring_expenses');
    }
};
