<?php

namespace Modules\Projects\Concerns;

use Modules\Projects\Models\Project;
use Modules\Projects\Support\Enums\ProjectStatus;

/**
 * @mixin Project
 */
trait HasProjectWorkflows
{
    public static function bootHasProjectWorkflows(): void
    {
        static::created(function (Project $project) {
//            $project->createWorkflow();
            return $project;
        });

        static::updating(function (Project $project) {
            // Flow 2
            if ($project->isDirty('progress')) {
                $project->progressWorkflow();
            }
            // Flow 1
            if ($project->isDirty('status')) {
                $project->statusWorkflow();
            }
            return $project;
        });
    }

    public function getCalculatedProgressAttribute(): float|int
    {
        return $this->tasks->average('progress') ?? 0;
    }

    public function runWorkflows($commit = true): static
    {
        $model = $this->statusWorkflow()->progressWorkflow();
        if ($commit) {
            $model->save();
        }
        return $model;
    }

    public function progressWorkflow(): static
    {
        if ($this->progress == 100 && $this->status != ProjectStatus::Done->value) {
            $this->status = ProjectStatus::Done->value;
        } elseif ($this->progress == 0 && $this->status != ProjectStatus::TODO->value) {
            $this->status = ProjectStatus::TODO->value;
        } elseif ($this->progress > 0 && $this->progress < 100 && $this->status != ProjectStatus::InProgress->value) {
            $this->status = ProjectStatus::InProgress->value;
        }
        return $this;
    }

    public function statusWorkflow(): static
    {
        /*if ($this->status == ProjectStatus::Done->value && $this->progress != 100) {
            $this->progress = $this->tasks->count() ? $this->calculated_progress : 100;
        } else {

        }*/
        $this->progress = $this->calculated_progress;
        return $this;
    }

}