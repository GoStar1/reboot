<?php

namespace App\Enums;

enum EventProcessCode: int
{
    case WRONG = 1000;
    case RETRY = 1001;
    case WARNING = 1002;
}
