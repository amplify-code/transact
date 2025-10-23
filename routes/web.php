<?php

use AmplifyCode\Transact\Controllers\WebhookController;
use AmplifyCode\Transact\Models\Transaction;
use AmplifyCode\Transact\Transact;
use Illuminate\Support\Facades\Route;

/** outside web middleware to avoid CSRF clashes */

Route::get('/transact/return', function() {
    return view("transact::return");
});

Route::post('/transact/subscribe', function() {
    // dd(request()->all());
    // $t = Transaction::where('uuid', request()->transaction_id)->first();
    return Transact::subscribe(request()->setupIntent);
});

Route::post('/transact/stripe', [WebhookController::class, 'stripe']);


Route::middleware(['web'])->group(function() {
    Route::get('/transact/poll-reference/{transaction:reference}', function(Transaction $transaction) {
        return $transaction->status;
    });

    Route::post('/transact/fail', function() {

        $data = request()->all();

        $trans = Transaction::where('reference', $data['reference'])->first();
        $trans->failed = 1;
        $trans->failure_reason = $data['message'];
        $trans->save();

        // return $transaction->setFailed();
    });
});




/** legacy route - this is the URL format from the original checkout module */
Route::post('/stripe/webhook', [WebhookController::class, 'stripe']);

