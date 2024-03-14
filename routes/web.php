<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\AupostController;

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

Route::any('/check-domains-status',[AutomationController::class,'chkDomain'])->name('chkDomain');
Route::any('/auto-post-script/{id}',[AupostController::class,'getNewPost'])->name('getNewPost');
Route::any('/generateHTML',[AupostController::class,'generateHTML'])->name('generateHTML');
Route::any('/getContentByCron',[AupostController::class,'getContentByCron']);
Route::any('/test',[AupostController::class,'test']);

Route::any('/{lvl1?}/{lvl2?}/{lvl13?}/{lvl4?}',[DomainController::class,'index'])->name('home');

