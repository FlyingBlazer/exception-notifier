<?php

namespace ExceptionNotifier\Logger;

use ExceptionNotifier\Jobs\ExceptionReport;

class JianLiaoLogger
{
    /**
     * @var \ExceptionNotifier\Logger\JianLiaoLogger
     */
    private static $instance;

    /**
     * @var string
     */
    private $url;

    /**
     * @return \ExceptionNotifier\Logger\JianLiaoLogger
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param \Throwable $e
     */
    public function log(\Throwable $e)
    {
        if (is_null($this->url)) return;
        $data = [
            'title' => $e->getMessage(),
            'text' => $e->getTraceAsString()
        ];

        $job = new ExceptionReport('jianliao', $data);
        dispatch($job);
    }

    /**
     * @param $data array
     */
    public function send($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * JianLiaoLogger constructor.
     */
    private function __construct()
    {
        $this->url = env('JIANLIAO_WEBHOOK');
    }
}