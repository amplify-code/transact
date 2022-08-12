<?php

namespace AscentCreative\Transact\Contracts;

interface iTransactable {

    public function getTransactionAmount():float;

    public function onTransactionComplete();

}