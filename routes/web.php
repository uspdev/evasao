<?php

use App\Models\Reingresso;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\evasaoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::match(['get', 'post'], 'tabelaConsolidada', [evasaoController::class, 'tabelaConsolidada']);
Route::get('/', [evasaoController::class, 'index']);
Route::get('/reingresso', function() {
    $reingresso = Reingresso::listarReingresso();
    return view('reingresso', compact('reingresso'));
});
