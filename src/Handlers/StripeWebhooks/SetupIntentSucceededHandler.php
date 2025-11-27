<?php

namespace AmplifyCode\Transact\Handlers\StripeWebhooks;

use AmplifyCode\Transact\Models\Transaction;
use Exception;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\SetupIntent;
use Stripe\StripeClient;

class SetupIntentSucceededHandler
{

    public function __construct(public Event $event, public string $webhookPayload) {}

    // TODO: this currently only handles subscriptions - update to also handle single transactions
    public function handle(): void
    {
        $setupIntent = $this->event->data->object;

        if (!is_a($setupIntent, SetupIntent::class)) {
            throw new Exception('Invalid event object type: '. get_class($setupIntent), 1);
        }

        if ($setupIntent->metadata !== null && isset($setupIntent->metadata->model) && isset($setupIntent->metadata->model_id)) {
            $model = $setupIntent->metadata->model;
            $modelID = $setupIntent->metadata->model_id;
        }

        if (!isset($model) || !isset($modelID)) {
            throw new Exception('Transactable model not found', 1);
        }

        $transaction = Transaction::query()->create([
            'transactable_type' => $model,
            'transactable_id' => $modelID,
        ]);

        $transaction->markPaid(0, 0, $setupIntent->id, $this->webhookPayload);
    }
}
