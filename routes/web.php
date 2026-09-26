<?php

use App\Http\Controllers\Action\AuthenticationController as AuthenticationAction;
use App\Http\Controllers\Action\ExpenseController as ExpenseAction;
use App\Http\Controllers\Action\RecurringController as RecurringAction;
use App\Http\Controllers\Action\ResourceController as ResourceAction;
use App\Http\Controllers\Action\SettingsController as SettingsAction;
use App\Http\Controllers\View\AuthenticationController as AuthenticationView;
use App\Http\Controllers\View\DashboardController;
use App\Http\Controllers\View\ExpenseController as ExpenseView;
use App\Http\Controllers\View\RecurringController as RecurringView;
use App\Http\Controllers\View\ResourceController as ResourceView;
use App\Http\Controllers\View\SettingsController as SettingsView;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthenticationView::class, 'signIn'])->name('auth.sign-in');
    Route::post('/sign-in', [AuthenticationAction::class, 'signIn'])->name('auth.sign-in.action');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sign-out', [AuthenticationAction::class, 'signOut'])->name('auth.sign-out.action');

    Route::get('/children/create', [ResourceView::class, 'create'])->name('children.create');
    Route::post('/children', [ResourceAction::class, 'store'])->name('children.create.action');
    Route::get('/children/{resource_id}', [ResourceView::class, 'show'])->name('children.show');

    Route::get('/expenses/create', [ExpenseView::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseAction::class, 'store'])->name('expenses.store');
    Route::get('/children/{resource_id}/expenses/{item_id}/edit', [ExpenseView::class, 'edit'])->name('expenses.edit');
    Route::post('/children/{resource_id}/expenses/{item_id}/update', [ExpenseAction::class, 'update'])->name('expenses.update');
    Route::post('/children/{resource_id}/expenses/{item_id}/delete', [ExpenseAction::class, 'destroy'])->name('expenses.delete');

    Route::get('/recurring', [RecurringView::class, 'index'])->name('recurring.index');
    Route::get('/recurring/create', [RecurringView::class, 'create'])->name('recurring.create');
    Route::post('/recurring', [RecurringAction::class, 'store'])->name('recurring.store');
    Route::get('/recurring/{recurringExpense}/edit', [RecurringView::class, 'edit'])->name('recurring.edit');
    Route::post('/recurring/{recurringExpense}/update', [RecurringAction::class, 'update'])->name('recurring.update');
    Route::post('/recurring/{recurringExpense}/toggle', [RecurringAction::class, 'toggle'])->name('recurring.toggle');
    Route::post('/recurring/{recurringExpense}/delete', [RecurringAction::class, 'destroy'])->name('recurring.delete');

    Route::get('/settings', [SettingsView::class, 'index'])->name('settings.index');

    Route::get('/settings/default-split', [SettingsView::class, 'defaultSplit'])->name('settings.default-split');
    Route::post('/settings/default-split', [SettingsAction::class, 'saveDefaultSplit'])->name('settings.default-split.action');

    Route::get('/settings/resource-naming', [SettingsView::class, 'resourceNaming'])->name('settings.resource-naming');
    Route::post('/settings/resource-naming', [SettingsAction::class, 'saveResourceNaming'])->name('settings.resource-naming.action');

    Route::get('/settings/categories', [SettingsView::class, 'categories'])->name('settings.categories');
    Route::post('/settings/categories', [SettingsAction::class, 'storeCategory'])->name('settings.categories.store');
    Route::post('/settings/categories/{category_id}/update', [SettingsAction::class, 'updateCategory'])->name('settings.categories.update');
    Route::post('/settings/categories/{category_id}/subcategories', [SettingsAction::class, 'storeSubcategory'])->name('settings.categories.subcategories.store');
    Route::post('/settings/categories/{category_id}/subcategories/{subcategory_id}/update', [SettingsAction::class, 'updateSubcategory'])->name('settings.categories.subcategories.update');

    Route::get('/settings/periods', [SettingsView::class, 'periods'])->name('settings.periods');
    Route::post('/settings/periods', [SettingsAction::class, 'storePeriod'])->name('settings.periods.store');
    Route::post('/settings/periods/{reportingPeriod}/update', [SettingsAction::class, 'updatePeriod'])->name('settings.periods.update');
    Route::post('/settings/periods/{reportingPeriod}/delete', [SettingsAction::class, 'destroyPeriod'])->name('settings.periods.delete');
});
