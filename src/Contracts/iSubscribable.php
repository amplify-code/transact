<?php

namespace AscentCreative\Transact\Contracts;


/**
 * Methods for recurring subscriptions
 */
interface iSubscribable {

    public function getCustomerName():string;

    public function getCustomerEmail():string;

    public function getSubscriptionAmount():float;

    public function getInterval():string;
    public function getIntervalCount():int;

    public function onSubscriptionComplete();

    public function onRecurringPayment();

}