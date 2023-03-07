<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{

    private $accountSid;
    private $authToken;
    private $twilioNumber;
    private $client;

    public function __construct()
    {
        $this->accountSid = "AC0f26a87bc35e437038bda8d0bc069986";
        $this->authToken = "8f42361b064c195101e24714ef5ebdd2";
        $this->twilioNumber = "+15746525693";
        $this->client = new Client($this->accountSid, $this->authToken);
    }

    public function sendSms(string $to, string $body)
    {
        $client =new Client( $this->accountSid,  $this->authToken);
        $message= $client->messages->create(
            $to,
            [
                'from' => $this->twilioNumber,
                'body' => $body,
            ]
        );
    }
}