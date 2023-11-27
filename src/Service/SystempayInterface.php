<?php

namespace App\Service;

interface SystempayInterface
{
    public function getEndpoint();
    public function getPublicKey();
    public function checkHash();
    public function getParsedFormAnswer();
    public function createPayment(array $store);
}

?>