<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
//Master
use App\Http\Controllers\Master\CategoriesController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\EmployeeController;
//Transaction
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\Transaction\TransactionSaleController;
use App\Http\Controllers\Transaction\ReceiptController;


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
	return redirect('/login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Rute yang hanya dapat diakses oleh role Admin
Route::middleware(['auth'])->group(function() {
	Route::resource('posts', PostController::class);
	Route::resource('users', UserController::class);

    //Master
	Route::resource('categories', CategoriesController::class);
	Route::resource('products', ProductController::class);
	Route::resource('suppliers', SupplierController::class);
	Route::resource('employees', EmployeeController::class);

    //Transactions
	Route::resource('transactions', TransactionController::class);
	Route::resource('transaction-sales', TransactionSaleController::class);
	Route::get('/transactions/{id}/details', [TransactionController::class, 'details']);
	Route::resource('receipts', ReceiptController::class);
});