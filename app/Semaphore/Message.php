<?php

namespace App\Semaphore;

class Message
{
    private Client $client;

    /**
     * Create a new message client instance.
     *
     * @param  Client  $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send a message to the number.
     *
     * @param  string  $number
     * @param  string  $message
     * @return array
     */
    public function send($number, $message)
    {
        $response = $this->client->send('POST', '/api/v4/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'number'     => $number,
                'message'    => $message,
                'sendername' => $sender = $this->client->getSender(),
                'apikey'     => $apiKey = $this->client->getApiKey(),
            ],
        ]);

        if (array_key_exists('apikey', $response)) {
            throw new Exceptions\InvalidApiKey($apiKey);
        }

        if (array_key_exists('number', $response)) {
            throw new Exceptions\InvalidNumber($number);
        }

        if (array_key_exists(0, $response) && array_key_exists('senderName', $response[0])) {
            throw new Exceptions\InvalidSenderName($sender);
        }

        return $response;
    }

    /**
     * Send a message to the number.
     *
     * @param  string  $number
     * @param  string  $message
     * @return array
     */
    public function sendOtp($number, $message)
    {
        $response = $this->client->send('POST', '/api/v4/otp', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'number'     => $number,
                'message'    => $message,
                'sendername' => $sender = $this->client->getSender(),
                'apikey'     => $apiKey = $this->client->getApiKey(),
            ],
        ]);

        if (array_key_exists('apikey', $response)) {
            throw new Exceptions\InvalidApiKey($apiKey);
        }

        if (array_key_exists('number', $response)) {
            throw new Exceptions\InvalidNumber($number);
        }

        if (array_key_exists(0, $response) && array_key_exists('senderName', $response[0])) {
            throw new Exceptions\InvalidSenderName($sender);
        }

        return $response;
    }
}