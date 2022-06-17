<?php



/** outside web middleware to avoid CSRF clashes */


Route::post('/transact/stripe', [AscentCreative\Transact\Controllers\WebhookController::class, 'stripe']);


Route::middleware(['web'])->group(function() {
    Route::get('/transact/poll-reference/{transaction:reference}', function(AscentCreative\Transact\Models\Transaction $transaction) {
        return $transaction->status;
    });
});





/** legacy route - this is the URL format from the original checkout module */
Route::post('/stripe/webhook', [AscentCreative\Transact\Controllers\WebhookController::class, 'stripe']);

