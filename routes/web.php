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
Route::resource('products', App\Http\Controllers\ProductController::class);
Route::get('products-kardex', [App\Http\Controllers\ProductController::class, 'kardex'])->name('products.kardex');
Route::post('products-kardex/movement', [App\Http\Controllers\ProductController::class, 'storeMovement'])->name('products.kardex.movement');
Route::get('products-kardex/pdf', [App\Http\Controllers\ProductController::class, 'exportKardexPdf'])->name('products.kardex.pdf');
Route::get('products-kardex/excel', [App\Http\Controllers\ProductController::class, 'exportKardexExcel'])->name('products.kardex.excel');
Route::resource('services', App\Http\Controllers\ServiceController::class);
Route::resource('templates', App\Http\Controllers\TemplateController::class);
Route::resource('packages', App\Http\Controllers\PackageController::class)->middleware('auth');
Route::get('/templates/{template}/preview', [App\Http\Controllers\TemplateController::class, 'preview'])->name('templates.preview');
Route::get('/templates/{template}/render', [App\Http\Controllers\TemplateController::class, 'render'])->name('templates.render');
Route::get('/api/products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');

Route::get('api/areas/{area}/details', [App\Http\Controllers\AreaController::class, 'getDetails']);
Route::post('profiles/{profile}/sync', [App\Http\Controllers\ProfileController::class, 'toggleExam'])->name('profiles.sync');
Route::resource('orders', App\Http\Controllers\OrderController::class);
Route::get('/check-patient-history/{patient}', [App\Http\Controllers\OrderController::class, 'checkHistory']);
// Si lo pones en web.php
Route::get('/search-patients', [App\Http\Controllers\OrderController::class, 'searchPatients'])->name('patients.search');
Route::get('/search-items', [App\Http\Controllers\OrderController::class, 'searchItems'])->name('items.search');
Route::get('/orders/patients/{patient}', [App\Http\Controllers\OrderController::class, 'getPatient'])->name('orders.patients.show');
Route::post('/orders/patients', [App\Http\Controllers\OrderController::class, 'quickStorePatient'])->name('orders.patients.store');
Route::put('/orders/patients/{patient}', [App\Http\Controllers\OrderController::class, 'quickUpdatePatient'])->name('orders.patients.update');
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
Route::post('/api/search/products/quick-store', [App\Http\Controllers\SearchController::class, 'quickStoreProduct']);

Route::resource('lab-results', App\Http\Controllers\LabResultController::class)->names('lab-results');

Route::get('/cashbox', [App\Http\Controllers\CashBoxController::class, 'index'])->name('cashbox.index');
Route::post('/cashbox/expense', [App\Http\Controllers\CashBoxController::class, 'storeExpense'])->name('cashbox.expense');
Route::put('/cashbox/expense/{expense}', [App\Http\Controllers\CashBoxController::class, 'updateExpense'])->name('cashbox.expense.update');
Route::get('/cashbox/pdf', [App\Http\Controllers\CashBoxController::class, 'exportPdf'])->name('cashbox.pdf');
Route::get('/cashbox/excel', [App\Http\Controllers\CashBoxController::class, 'exportExcel'])->name('cashbox.excel');

// 1. PRIMERO tus rutas de atención (Específicas)
Route::get('/atencion/servicio/{detail}', [App\Http\Controllers\ServiceResultController::class, 'atenderServicio'])
    ->name('services.atender');

Route::post('/atencion/servicio/{detail}/guardar', [App\Http\Controllers\ServiceResultController::class, 'guardarInforme'])
    ->name('services.guardar');

Route::get('/atencion/servicio/imprimir/{report}', [App\Http\Controllers\ServiceResultController::class, 'imprimirReporte'])
    ->name('services.imprimir');

    // Ejemplo de cómo debe estar definida tu ruta
Route::post('/services/guardar/{detail}', [App\Http\Controllers\ServiceResultController::class, 'guardarInforme'])->name('services.guardar');

// 2. DESPUÉS el recurso (Genérico)
Route::resource('serviceresults', App\Http\Controllers\ServiceResultController::class);

Route::get('/referrals/create/sis', [App\Http\Controllers\ReferralController::class, 'create'])
    ->defaults('type', 'SIS')
    ->name('referrals.create.sis');
Route::get('/referrals/create/essalud', [App\Http\Controllers\ReferralController::class, 'create'])
    ->defaults('type', 'ESSALUD')
    ->name('referrals.create.essalud');
Route::get('/referrals/cie10/search', [App\Http\Controllers\ReferralController::class, 'searchCie10'])
    ->name('referrals.cie10.search');
Route::get('/referrals/patients/search', [App\Http\Controllers\ReferralController::class, 'searchPatients'])
    ->name('referrals.patients.search');
Route::get('/referrals/{id}/pdf', [App\Http\Controllers\ReferralController::class, 'downloadPdf'])
    ->name('referrals.pdf');
Route::get('/referrals/{id}/pdf-essalud', [App\Http\Controllers\ReferralController::class, 'downloadPdfEssalud'])
    ->name('referrals.pdf.essalud');
Route::resource('referrals', App\Http\Controllers\ReferralController::class)->middleware('auth');
