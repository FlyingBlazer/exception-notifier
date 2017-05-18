<?php

namespace ExceptionNotifier;

use ExceptionNotifier\Logger\JianLiaoLogger;
use ExceptionNotifier\Logger\RollbarLogger;

class Notifier
{
    public static function notify($e)
    {
        JianLiaoLogger::instance()->log($e);
        RollbarLogger::instance()->log($e);
    }
}
