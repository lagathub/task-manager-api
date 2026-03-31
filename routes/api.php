<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Task Management API Routes
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
| The report route is defined BEFORE the {task} wildcard routes
| to prevent Laravel from trying to resolve "report" as a task ID.
|
*/

// Daily report (must be above wildcard routes)
Route::get('/tasks/report', [TaskController::class, 'report']);

// Core CRUD
Route::post('/tasks',                  [TaskController::class, 'store']);
Route::get('/tasks',                   [TaskController::class, 'index']);
Route::patch('/tasks/{task}/status',   [TaskController::class, 'updateStatus']);
Route::delete('/tasks/{task}',         [TaskController::class, 'destroy']);
