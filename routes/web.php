<?php

use AscentCreative\Transact\Models\Transaction;

/** outside web middleware to avoid CSRF clashes */

Route::get('/transact/return', function() {
    return view("transact::return");
});

Route::post('/transact/subscribe', function() {
    // dd(request()->all());
    // $t = Transaction::where('uuid', request()->transaction_id)->first();
    return \AscentCreative\Transact\Transact::subscribe(request()->setupIntent);
});

Route::post('/transact/stripe', [AscentCreative\Transact\Controllers\WebhookController::class, 'stripe']);


Route::middleware(['web'])->group(function() {
    Route::get('/transact/poll-reference/{transaction:reference}', function(AscentCreative\Transact\Models\Transaction $transaction) {
        return $transaction->status;
    });

    Route::post('/transact/fail', function() {

        $data = request()->all();

        $trans = \AscentCreative\Transact\Models\Transaction::where('reference', $data['reference'])->first();
        $trans->failed = 1;
        $trans->failure_reason = $data['message'];
        $trans->save();

        // return $transaction->setFailed();
    });
});




/** legacy route - this is the URL format from the original checkout module */
Route::post('/stripe/webhook', [AscentCreative\Transact\Controllers\WebhookController::class, 'stripe']);

