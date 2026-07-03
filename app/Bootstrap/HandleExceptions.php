<?php

namespace App\Bootstrap;

use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;
use ErrorException;

class HandleExceptions extends BaseHandleExceptions
{
    /**
     * Convert PHP errors to ErrorException instances, ignoring deprecations.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if ($level & (E_DEPRECATED | E_USER_DEPRECATED)) {
            return;
        }

        parent::handleError($level, $message, $file, $line, $context);
    }
}
