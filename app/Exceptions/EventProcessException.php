<?php

namespace App\Exceptions;

use App\Enums\EventProcessCode;
use Throwable;

class EventProcessException extends \Exception
{
    public function __construct(string $message, EventProcessCode $code, ?Throwable $previous = null)
    {
        parent::__construct($message, $code->value, $previous);
    }
}
