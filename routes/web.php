<?php



/** outside web middleware to avoid CSRF clashes */


Route::post('/transact/stripe', [AscentCreative\Transact\Controllers\WebhookController::class, 'stripe']);




/** legacy route - this is the URL format from the original checkout module */
Route::post('/stripe/webhook', [AscentCreative\Transact\Controllers\WebhookController::class, 'stripe']);

