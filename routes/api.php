<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EmployeeController;

Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/search', [EmployeeController::class, 'search']);
Route::post('/webhook/employees', [EmployeeController::class, 'handleWebhook']);
