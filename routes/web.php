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

//redireciona para o login, caso a pessoa tente acessar o reingresso
Route::middleware(['auth'])->get('/reingresso', function() {
    if (auth()->user()->level !== 'admin') {
        abort(403, 'Acesso negado');
    }
    
    $reingresso = Reingresso::listarReingresso();

    return view('reingresso', compact('reingresso'));
});
