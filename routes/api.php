<?php

use App\Http\Controllers\Api\ActorsPromptController;
use Illuminate\Support\Facades\Route;

Route::get('/actors/prompt-validation', ActorsPromptController::class);
