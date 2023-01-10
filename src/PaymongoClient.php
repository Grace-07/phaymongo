<?php

namespace Paymongo\Phaymongo;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

define('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1');

class PaymongoClient {
    protected $public_key;
    protected $secret_key;
    protected $client;

    public function __construct(string $public_key, string $secret_key, array $client_ops = array())
    {
        $this->public_key = $public_key;
        $this->secret_key = $secret_key;

        $default_client_ops = array(
            'base_uri' => PAYMONGO_BASE_URL,
        );
        
        $client = new Client(array_merge($default_client_ops, $client_ops));

        $this->client = $client;
    }

    public function getAuthorizationHeader(bool $use_public_key = false) {
        $key = $use_public_key ? $this->public_key : $this->secret_key;

        return 'Basic ' . base64_encode($key);
    }

    public function createRequest(string $method, string $url, array $payload = null, bool $use_public_key = false) {
        $request = new Request($method, $url, array(
            'Authorization' => $this->getAuthorizationHeader($use_public_key),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ));

        if (!empty($payload)) {
            $request = $request->withBody(\GuzzleHttp\Psr7\Utils::streamFor(json_encode($payload)));
        }

        return $request;
    }
    
    /**
     * A function to create a Paymongo payment intent object to use for transactions
     *
     * @param  int $amount The transaction amount
     * @param  string[] $payment_methods
     * @param  string $description
     * @param  mixed $metadata
     * @return Response
     */
    public function createPaymentIntent($amount, $payment_methods, $description, $metadata = null): Response {
        $attributes = array(
            'amount' => $amount * 100,
            'payment_method_allowed' => $payment_methods,
            'currency' => 'PHP', // hard-coded for now
            'description' => $description,    
        );

        if (!empty($metadata)) {
            $attributes['metadata'] = $metadata;
        }

        $payload = array(
            'data' => array(
                'attributes' => $attributes,
            ),
        );

        $request = $this->createRequest('POST', '/payment_intents', $payload);
        return $this->client->send($request);
    }
    
    /**
     * A function to create a Paymongo source object to use for transactions
     *
     * @param  int $amount The transaction amount
     * @param  string $type
     * @param  string $success_url
     * @param  string $failed_url
     * @param  object $billing
     * @param  object $metadata
     * @return void
     */
    public function createSource($amount, $type, $success_url, $failed_url, $billing = null, $metadata = null) {
        $attributes = array(
            'type' => $type,
            'amount' => $amount * 100,
            'currency' => 'PHP', // hard-coded for now
            'redirect' => array(
                'success' => $success_url,
                'failed' => $failed_url,
            ),
        );

        if (!empty($billing)) {
            $attributes['billing'] = $billing;
        }

        if (!empty($metadata)) {
            $attributes['metadata'] = $metadata;
        }

        $payload = array(
            'data' => array(
                'attributes' => $attributes,
            ),
        );

        $request = $this->createRequest('POST', '/sources', $payload);
        return $this->client->send($request);
    }
}