<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\Branch;


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
    $branch = Branch::first();
    return view('welcome', compact('branch'));
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::resource('branches', App\Http\Controllers\BranchController::class)->middleware('auth');
Route::resource('specialties', App\Http\Controllers\SpecialtyController::class)->middleware('auth');
Route::resource('users', App\Http\Controllers\UserController::class)->middleware('auth');
Route::resource('patients', App\Http\Controllers\PatientController::class)->middleware('auth');
Route::resource('areas', App\Http\Controllers\AreaController::class)->middleware('auth');
Route::resource('catalogs', App\Http\Controllers\CatalogController::class);
Route::resource('profiles', App\Http\Controllers\ProfileController::class);

Route::get('api/areas/{area}/details', [App\Http\Controllers\AreaController::class, 'getDetails']);
Route::post('profiles/{profile}/sync', [App\Http\Controllers\ProfileController::class, 'toggleExam'])->name('profiles.sync');
Route::resource('orders', App\Http\Controllers\OrderController::class);
Route::get('/check-patient-history/{patient}', [App\Http\Controllers\OrderController::class, 'checkHistory']);
// Si lo pones en web.php
Route::get('/search-patients', [App\Http\Controllers\OrderController::class, 'searchPatients'])->name('patients.search');
Route::get('/search-items', [App\Http\Controllers\OrderController::class, 'searchItems'])->name('items.search');
Route::resource('histories', App\Http\Controllers\HistoryController::class);
// Rutas adicionales para Impresión de Documentos (PDF)
Route::controller(App\Http\Controllers\HistoryController::class)->group(function () {
    Route::get('histories/{history}/print-LAB', 'printLab')->name('histories.print');
    Route::get('histories/{history}/print-prescription', 'printPrescription')->name('histories.print-prescription');
    Route::get('histories/{history}/print-history', 'printHistory')->name('histories.print_history');
});

Route::get('/api/search/cie10', [App\Http\Controllers\SearchController::class, 'cie10']);
Route::get('/api/search/products', [App\Http\Controllers\SearchController::class, 'products']);
Route::get('/api/search/lab', [App\Http\Controllers\SearchController::class, 'lab']);

Route::resource('lab-results', App\Http\Controllers\LabResultController::class)->names('lab-results');

Route::get('/cashbox', [App\Http\Controllers\CashBoxController::class, 'index'])->name('cashbox.index');
Route::post('/cashbox/expense', [App\Http\Controllers\CashBoxController::class, 'storeExpense'])->name('cashbox.expense');
Route::put('/cashbox/expense/{expense}', [App\Http\Controllers\CashBoxController::class, 'updateExpense'])->name('cashbox.expense.update');
Route::get('/cashbox/pdf', [App\Http\Controllers\CashBoxController::class, 'exportPdf'])->name('cashbox.pdf');
