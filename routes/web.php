<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AdminController;

Route::get('/', [ClienteController::class, 'welcome'])
    ->middleware('auth')
    ->name('cliente.welcome');
Route::get('/favoritos', [ClienteController::class, 'favoritos'])->middleware('auth')->name('cliente.favoritos');
Route::get('/categoria/{categ}', [ClienteController::class, 'categoria'])->middleware('auth')->name('cliente.categoria');
Route::get('/buscar/{busca}', [ClienteController::class, 'buscar'])->middleware('auth');
Route::get('/carrinho', [ClienteController::class, 'carrinho'])->middleware('auth')->name('cliente.carrinho');;
Route::get('produto/{id}', [ClienteController::class, 'produto'])->middleware('auth')->name('cliente.produto');
Route::post('/toggle-favorito', [ClienteController::class, 'toggle'])->middleware('auth');
Route::post('/toggle-carrinho', [ClienteController::class, 'toggleCarrinho'])->middleware('auth');
Route::post('/finalizar-compra', [ClienteController::class, 'finalizarCompra'])->middleware('auth')->name('cliente.finalizarCompra');
Route::post('/avaliar/{id}', [ClienteController::class, 'avaliar'])->name('cliente.avaliar');
Route::post('/registrar-visualizacao', [ClienteController::class, 'registrarVisualizacao'])->middleware('auth')->name('cliente.registrarVisualizacao');

// Rota protegida
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'adminIndex'])->name('admin');

    Route::get('/admin/produtos/create', [AdminController::class, 'create'])->name('produtos.create');
    Route::post('/admin/produtos', [AdminController::class, 'store'])->name('produtos.store');

    Route::get('/admin/produtos/{id}/edit', [AdminController::class, 'edit'])->name('produtos.edit');
    Route::put('/admin/produtos/{id}', [AdminController::class, 'update'])->name('produtos.update');

    Route::delete('/admin/produtos/{id}', [AdminController::class, 'destroy'])->name('produtos.destroy');
});