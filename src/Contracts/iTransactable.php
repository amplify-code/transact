<?php

namespace AmplifyCode\Transact\Contracts;

interface iTransactable {

    public function getTransactionAmount():float;

    public function getTransactionDescription():?string;

    public function onTransactionComplete(): void;

}
