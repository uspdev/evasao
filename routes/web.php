<?php

use App\Http\Controllers\Auth\loginController;
use App\Http\Controllers\evasaoController;
use Illuminate\Support\Facades\Route;

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

Route::get('login', [LoginController::class, 'redirectToProvider']);
Route::get('callback', [LoginController::class, 'handleProviderCallback']);
Route::post('logout', [LoginController::class, 'logout']);

Route::match(['get', 'post'], 'tabelaConsolidada', [evasaoController::class, 'tabelaConsolidada']);
Route::get('/', [evasaoController::class, 'index']);
