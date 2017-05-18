<?php

namespace ExceptionNotifier\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ExceptionNotifier\Logger\JianLiaoLogger;
use ExceptionNotifier\Logger\RollbarLogger;

class ExceptionReport implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    protected $type;
    protected $data;

    public function __construct($type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function handle()
    {
        switch ($this->type) {
            case 'rollbar':
                RollbarLogger::instance()->send($this->data);
                break;
            case 'jianliao':
            default:
                JianLiaoLogger::instance()->send($this->data);
                break;
        }
    }

    public function failed()
    {
        $this->delete();
    }
}
