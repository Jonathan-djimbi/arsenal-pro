<?php

namespace App\Service;

use Lyra\Client;
use Illuminate\Http\Response;
use App\Service\SystempayInterface;

class SystempayService implements SystempayInterface {
    /**
     * The Client instance.
     *
     * @var object
     */
    protected $client;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setUsername($_ENV['EPAY_USERNAME']);
        $this->client->setEndpoint($_ENV['EPAY_ENDPOINT']);
        $this->client->setPassword($_ENV['EPAY_PASSWORD']);
        $this->client->setSHA256Key($_ENV['EPAY_SHA256KEY']);
        $this->client->setPublicKey($_ENV['EPAY_PUBLIC_KEY']);
    }

    /**
     * Get Endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->client->getEndpoint();
    }

    /**
     * Get PublicKey.
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->client->getPublicKey();
    }

    /**
     * Check Hash.
     *
     * @return boolean
     */
    public function checkHash()
    {
        return $this->client->checkHash();
    }

    /**
     * Get Parsed Form Answer.
     *
     * @return array
     */
    public function getParsedFormAnswer()
    {
        return $this->client->getParsedFormAnswer();
    }

    /**
     * Create payment.
     *
     * @param array $store
     * @return array
     */
    public function createPayment($store)
    {
        return $this->client->post("V4/Charge/CreatePayment", $store);
    }
}