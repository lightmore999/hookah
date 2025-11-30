<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HookahController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('tables.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('tables.index');
    })->name('dashboard');
    
    
    Route::resource('clients', ClientController::class);
    Route::resource('sales', SaleController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('sales/search/clients', [SaleController::class, 'searchClients'])->name('sales.search.clients');
    
    Route::get('/accounting', [\App\Http\Controllers\AccountingController::class, 'index'])->name('accounting.index');
    
    Route::resource('hookahs', HookahController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('tables', TableController::class);
    Route::get('tables/search/clients', [TableController::class, 'searchClients'])->name('tables.search.clients');
    Route::post('tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.update.status');
    Route::post('tables/add-hookah', [TableController::class, 'addHookah'])->name('tables.add-hookah');
    Route::post('tables/close', [TableController::class, 'close'])->name('tables.close');
    Route::resource('warehouses', WarehouseController::class);
    Route::post('warehouses/{warehouse}/stock-levels/{stockLevel}/move', [WarehouseController::class, 'moveStock'])->name('warehouses.stock-levels.move');
    Route::post('warehouses/{warehouse}/stock-levels/{stockLevel}/writeoff-work', [WarehouseController::class, 'writeoffWork'])->name('warehouses.stock-levels.writeoff-work');
    Route::post('warehouses/{warehouse}/stock-levels/{stockLevel}/writeoff', [WarehouseController::class, 'writeoff'])->name('warehouses.stock-levels.writeoff');
    Route::resource('purchases', PurchaseController::class)->only(['create', 'store', 'destroy']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
