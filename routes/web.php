<?php

use AmplifyCode\Transact\Models\Transaction;

/** outside web middleware to avoid CSRF clashes */

Route::get('/transact/return', function() {
    return view("transact::return");
});

Route::post('/transact/subscribe', function() {
    // dd(request()->all());
    // $t = Transaction::where('uuid', request()->transaction_id)->first();
    return \AmplifyCode\Transact\Transact::subscribe(request()->setupIntent);
});

Route::post('/transact/stripe', [AmplifyCode\Transact\Controllers\WebhookController::class, 'stripe']);


Route::middleware(['web'])->group(function() {
    Route::get('/transact/poll-reference/{transaction:reference}', function(AmplifyCode\Transact\Models\Transaction $transaction) {
        return $transaction->status;
    });

    Route::post('/transact/fail', function() {

        $data = request()->all();

        $trans = \AmplifyCode\Transact\Models\Transaction::where('reference', $data['reference'])->first();
        $trans->failed = 1;
        $trans->failure_reason = $data['message'];
        $trans->save();

        // return $transaction->setFailed();
    });
});




/** legacy route - this is the URL format from the original checkout module */
Route::post('/stripe/webhook', [AmplifyCode\Transact\Controllers\WebhookController::class, 'stripe']);

