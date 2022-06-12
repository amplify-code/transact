<?php

namespace AscentCreative\Transact\Contracts;

interface iTransactable {


    public function getAmount():float;


    public function onPaymentConfirmed();

}