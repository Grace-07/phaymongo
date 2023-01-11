<?php

namespace Paymongo\Phaymongo;

use GuzzleHttp\Psr7\Response;

class Refund extends PaymongoClient {
    public function __construct()
    {
        $this->base_resource_key = 'refunds';
    }

    public function create($amount, $payment_id, $reason, $notes = null, $metadata = null): Response {
        $attributes = array(
            'amount' => $amount * 100,
            'payment_id' => $payment_id,
            'reason' => $reason,
        );

        if (!empty($notes)) {
            $attributes['notes'] = $notes;
        }

        if (!empty($metadata)) {
            $attributes['metadata'] = $metadata;
        }

        $payload = PaymongoUtils::constructPayload($attributes);
        return $this->createResource($payload);
    }

    /**
     * A function to retrieve a Paymongo refund object by ID
     *
     * @param  string $id
     * @return Response
     */
    public function retrieveById($id): Response {
        return $this->retrieveResourceById($id);
    }
}