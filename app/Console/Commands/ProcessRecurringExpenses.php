<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Expense\CreateExpense;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseRun;
use App\Models\ResourceType;
use App\Service\Api\ApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProcessRecurringExpenses extends Command
{
    protected $signature = 'expense:process-recurring';

    protected $description = 'Post any active recurring expenses that are due today (or overdue) to the API';

    private string $serviceToken;

    /**
     * One CreateExpense (and its underlying ApiService) per resource type,
     * built the first time a due recurring expense needs it - each resource
     * type needs its own API scoping, so a single instance built once
     * globally (as before multiple resource types existed) no longer works.
     *
     * @var array<int, CreateExpense>
     */
    private array $createExpenseByResourceType = [];

    public function handle(): int
    {
        $serviceToken = config('app.api.service_token');

        if (blank($serviceToken)) {
            $this->error('API_SERVICE_TOKEN is not configured, cannot post recurring expenses.');

            return self::FAILURE;
        }

        $this->serviceToken = $serviceToken;

        $today = Carbon::today();

        $due = RecurringExpense::query()
            ->where('active', true)
            ->where('next_run_date', '<=', $today)
            ->with(['allocations', 'resourceType'])
            ->get();

        foreach ($due as $recurringExpense) {
            if ($recurringExpense->ends_on !== null && $recurringExpense->next_run_date->gt($recurringExpense->ends_on)) {
                $recurringExpense->update(['active' => false]);

                continue;
            }

            $alreadyRun = RecurringExpenseRun::query()
                ->where('recurring_expense_id', $recurringExpense->id)
                ->where('run_date', $recurringExpense->next_run_date)
                ->exists();

            if ($alreadyRun) {
                $recurringExpense->advanceNextRunDate();

                continue;
            }

            $createExpense = $this->createExpenseFor($recurringExpense->resourceType);

            // A resource type with categories turned off posts uncategorised
            // expenses; the template keeps its stored categorisation, so
            // turning categories back on resumes using it.
            $categoriesEnabled = $recurringExpense->resourceType->categoriesEnabled();

            $result = $createExpense(
                [
                    'name' => $recurringExpense->name,
                    'description' => $recurringExpense->description,
                    'effective_date' => $recurringExpense->next_run_date->toDateString(),
                    'currency_id' => $recurringExpense->currency_id,
                    'total' => (string) $recurringExpense->total,
                    'category_id' => $categoriesEnabled ? $recurringExpense->category_id : null,
                    'subcategory_id' => $categoriesEnabled ? $recurringExpense->subcategory_id : null,
                ],
                $recurringExpense->allocations->map(fn ($allocation) => [
                    'resource_id' => $allocation->resource_id,
                    'percentage' => $allocation->percentage,
                ])->all(),
            );

            if (! $result->ok) {
                $reason = $result->message !== '' ? $result->message : json_encode($result->fieldErrors);
                $this->error("Failed to post recurring expense #{$recurringExpense->id} ({$recurringExpense->name}): {$reason}");
                report(new \RuntimeException("Recurring expense #{$recurringExpense->id} failed to post: {$reason}"));

                continue;
            }

            RecurringExpenseRun::create([
                'recurring_expense_id' => $recurringExpense->id,
                'run_date' => $recurringExpense->next_run_date,
                'created_item_ids' => $result->data['items'],
            ]);

            $this->info("Posted recurring expense #{$recurringExpense->id} ({$recurringExpense->name}) for {$recurringExpense->next_run_date->toDateString()}.");

            $recurringExpense->advanceNextRunDate();
        }

        return self::SUCCESS;
    }

    private function createExpenseFor(ResourceType $resourceType): CreateExpense
    {
        return $this->createExpenseByResourceType[$resourceType->id] ??= new CreateExpense(
            new ApiService($this->serviceToken, $resourceType->api_resource_type_id, $resourceType->item_subtype_id)
        );
    }
}
