<?php

namespace ExceptionNotifier\Logger;

use ExceptionNotifier\Jobs\ExceptionReport;
use Rollbar\Config;
use Rollbar\Payload\Level;
use Rollbar\Payload\Payload;

class RollbarLogger
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \ExceptionNotifier\Logger\RollbarLogger
     */
    private static $instance;

    /**
     * @return \ExceptionNotifier\Logger\RollbarLogger
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param \Exception $e
     */
    public function log(\Exception $e)
    {
        $level = Level::error();
        $accessToken = $this->config->getAccessToken();
        $payload = $this->getPayload($accessToken, $level, $e, []);

        if (!$this->config->checkIgnored($payload, $accessToken, $e, false)) {
            $toSend = $this->scrub($payload);
            $toSend = $this->truncate($toSend);
            $job = new ExceptionReport('rollbar', $toSend);
            dispatch($job);
        }
    }

    /**
     * @param $toSend array
     */
    public function send($toSend)
    {
        $accessToken = $this->config->getAccessToken();
        $this->config->send($toSend, $accessToken);
    }

    /**
     * RollbarLogger constructor.
     */
    private function __construct()
    {
        $this->config = new Config([
            'access_token' => env('ROLLBAR_ACCESS_TOKEN'),
            'environment' => env('APP_ENV'),
            'root' => base_path(),
        ]);
    }

    /**
     * @param $accessToken
     * @param $level
     * @param $toLog
     * @param $context
     * @return \Rollbar\Payload\Payload
     */
    private function getPayload($accessToken, $level, $toLog, $context)
    {
        $data = $this->config->getRollbarData($level, $toLog, $context);
        $payload = new Payload($data, $accessToken);
        return $this->config->transform($payload, $level, $toLog, $context);
    }

    /**
     * @param $payload \Rollbar\Payload\Payload
     * @return array
     */
    private function scrub(Payload $payload)
    {
        $serialized = $payload->jsonSerialize();
        $serialized['data'] = $this->config->getDataBuilder()->scrub($serialized['data']);
        return $serialized;
    }

    /**
     * @param $payload array
     * @return array
     */
    private function truncate(array $payload)
    {
        return $this->config->getDataBuilder()->truncate($payload);
    }
}