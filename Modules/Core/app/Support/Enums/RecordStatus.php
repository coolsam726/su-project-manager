<?php

namespace Modules\Core\Support\Enums;

enum RecordStatus
{
    case draft;

    case processing;
    case submitted;
    case posted;
    case closed;
    case cancelled;
}
