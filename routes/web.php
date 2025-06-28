
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicCalculatorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Route for the main calculator page
Route::get('/', [AcademicCalculatorController::class, 'index'])->name('calculator');
