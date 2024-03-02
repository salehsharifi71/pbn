<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\AutomationController;

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
Route::any('/auto-post-script/{id}',[AutomationController::class,'autoPost'])->name('autoPost');
Route::any('/generateHTML',[AutomationController::class,'generateHTML'])->name('generateHTML');

Route::any('/{lvl1?}/{lvl2?}/{lvl13?}/{lvl4?}',[DomainController::class,'index'])->name('home');

