<?php

namespace app\Provider;

use app\Core\LogsCore;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SlackProvider
{

    private Client $Guzzle;

    public function __construct()
    {
        $this->Guzzle = $this->Guzzle ?? new Client([ 'timeout' => 5 ]);
    }


    /**
     * Send message
     *
     * @param string $message
     * @param string $endPoint
     *
     * @return array
     */
    public function send(string $message, string $endPoint = ""): array
    {

        $ROOT = empty($endPoint) ? SLACK_CHANNEL : $endPoint;

        $encoded = [
            'channel' => $ROOT,
            'text' => $message
        ];

        $encoded = json_encode($encoded, JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            return ['status' => false];
        }

        try {

            $Request = $this->Guzzle->post(SLACK_ROOT, ['body' => $encoded]);

            if( $Request->getStatusCode() !== 200 ){

                $this->Logs('Error performing request, server did not respond as expected', ['responseCode' => $Request->getStatusCode(), 'body' => $encoded]);

            }

            return ['status' => true];

        } catch (GuzzleException $e) {

            $this->Logs($e->getMessage(), ['body' => $encoded]);

        }

        return ['status' => false];
    }

    /**
     * Set log for erros
     *
     * @param string $message
     * @param array $props
     */
    private function Logs(string $message, array $props = []): void
    {
        $Log = LogsCore::file('SlackProvider')->action(3)->method(5);

        if (!empty($props)) {
            $Log->props($props);
        }

        $Log->message($message)->save();
    }


}
