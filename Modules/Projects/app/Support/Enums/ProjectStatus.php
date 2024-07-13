<?php
namespace Modules\Projects\Support\Enums;
enum ProjectStatus: string
{
    use \Mokhosh\FilamentKanban\Concerns\IsKanbanStatus;
    case Backlog = 'BACKLOG';
    case TODO = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Done = 'DONE';
}
