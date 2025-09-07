<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;


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


Route::get('/', [QuizController::class, 'showStart']);       
Route::post('/start', [QuizController::class, 'storeUser']); 

Route::get('/quiz', [QuizController::class, 'showQuiz']);    
Route::get('/api/question/{order}', [QuizController::class, 'getQuestion']); 
Route::post('/api/answer', [QuizController::class, 'submitAnswer']); 
Route::get('/api/summary', [QuizController::class, 'summary']);


