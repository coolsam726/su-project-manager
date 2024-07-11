<?php

namespace Modules\Core\Models;

use Modules\Core\Concerns\HasTeam;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use HasTeam;
}
