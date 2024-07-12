<?php
namespace Modules\Projects\Support\Enums;
enum ProjectStatus: string
{
    use \Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;
    case Backlog = 'BACKLOG';
    case TODO = 'TODO';
    case InProgress = 'IN_PROGRESS';

    case Reviewing = 'REVIEW_IN_PROGRESS';

    case Blocked = 'BLOCKED';
    case Done = 'DONE';

    case Closed = 'CLOSED';
}
