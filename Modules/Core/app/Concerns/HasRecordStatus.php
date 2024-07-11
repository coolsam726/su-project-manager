<?php

namespace Modules\Core\Concerns;

use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Core;
use Modules\Core\Support\Enums\RecordStatus;

use function Modules\Core\Support\core;

/**
 * @mixin Model
 *
 * @method whereDraft(bool $condition = true)
 * @method orWhereDraft(bool $condition = true)
 * @method whereProcessing(bool $condition = true)
 * @method orWhereProcessing(bool $condition = true)
 * @method whereSubmitted(bool $condition = true)
 * @method orWhereSubmitted(bool $condition = true)
 * @method wherePosted(bool $condition = true)
 * @method orWherePosted(bool $condition = true)
 * @method whereClosed(bool $condition = true)
 * @method orWhereClosed(bool $condition = true)
 * @method whereCancelled(bool $condition = true)
 * @method orWhereCancelled(bool $condition = true)
 * @method withCancelled()
 * @method onlyCancelled()
 */
trait HasRecordStatus
{
    use EvaluatesClosures;

    public static function bootHasDocStatus(): void
    {
        static::addGlobalScope('record-status', function (Builder|self $builder) {
            if (! core()->modelHasColumn(static::class, Core::RECORD_STATUS)) {
                return;
            }
            $builder->whereCancelled(false);
        });

        static::creating(function (Model|self $model) {
            if (core()->modelHasColumn($model, Core::RECORD_STATUS)) {
                match ($model->getAttribute(Core::RECORD_STATUS)) {
                    RecordStatus::processing->name => $model->beforeStartProcessing(),
                    RecordStatus::submitted->name => $model->beforeSubmitting(),
                    RecordStatus::posted->name => $model->beforePosting(),
                    RecordStatus::closed->name => $model->beforeClosing(),
                    RecordStatus::cancelled->name => $model->beforeCanceling(),
                    default => $model->setAttribute(Core::RECORD_STATUS, RecordStatus::draft->name)
                };
            }
        });
        static::created(function (Model|self $model) {
            if (core()->modelHasColumn($model, Core::RECORD_STATUS)) {
                match ($model->getAttribute(Core::RECORD_STATUS)) {
                    RecordStatus::processing->name => $model->afterStartProcessing(),
                    RecordStatus::submitted->name => $model->afterSubmitting(),
                    RecordStatus::posted->name => $model->afterPosting(),
                    RecordStatus::closed->name => $model->afterClosing(),
                    RecordStatus::cancelled->name => $model->afterCanceling(),
                    default => $model->setAttribute(Core::RECORD_STATUS, RecordStatus::draft->name)
                };
            }
        });

        static::updating(function (Model|self $model) {
            if (! $model->canBeUpdated()) {
                throw new \RuntimeException('The record cannot be modified in the current '.$model->getRecordStatus().' state.');
            }
        });

        static::saving(function (Model|self $model) {
            if (! $model->canBeUpdated() && ! $model->getAttribute('id')) {
                throw new \RuntimeException('The record cannot be modified in the current '.$model->getRecordStatus().' state.');
            }
        });

        static::deleting(function (Model|self $model) {
            if (! $model->canBeUpdated()) {
                throw new \RuntimeException('The record cannot be deleted in the current '.$model->getRecordStatus().' state.');
            }
        });
    }

    protected function initializeHasDocStatus(): void
    {
        if (core()->modelHasColumn($this, 'is_active')) {
            $this->casts['is_active'] = 'bool';
        }

        if (core()->modelHasColumn($this, Core::RECORD_STATUS)) {
            $this->casts[Core::RECORD_STATUS] = 'string';
        }
    }

    public function isDraft(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::draft->name;
    }

    public function isProcessing(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::processing->name;
    }

    public function isSubmitted(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::submitted->name;
    }

    public function isPosted(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::posted->name;
    }

    public function isConfirmed(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::confirmed->name;
    }

    public function isCompleted(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::completed->name;
    }

    public function isClosed(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::closed->name;
    }

    public function isCancelled(): bool
    {
        return $this->getAttribute(Core::RECORD_STATUS) === RecordStatus::cancelled->name;
    }

    public function canBeUpdated(): bool
    {
        if (! core()->modelHasColumn($this, Core::RECORD_STATUS)) {
            return true;
        }

        return $this->isDraft() || $this->isProcessing();
    }

    public function getRecordStatus(): ?RecordStatus
    {
        if (! core()->modelHasColumn($this, Core::RECORD_STATUS)) {
            return null;
        }

        return static::getAttribute(Core::RECORD_STATUS);
    }

    public function startProcessing(): static
    {
        $record = $this->beforeStartProcessing();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::processing->name);
        $record->save();

        return $record->afterStartProcessing();
    }

    public function submit(): static
    {
        $record = $this->beforeSubmitting();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::submitted->name);
        $record->save();

        return $record->afterSubmitting();
    }

    public function post(): static
    {
        $record = $this->beforePosting();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::posted->name);
        $record->save();

        return $record->afterPosting();
    }

    public function confirm(): static
    {
        $record = $this->beforeConfirming();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::confirmed->name);
        $record->save();

        return $record->afterConfirming();
    }

    public function close(): static
    {
        $record = $this->beforeClosing();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::closed->name);
        $record->save();

        return $record->afterClosing();
    }

    public function complete(): static
    {
        $record = $this->beforeCompleting();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::completed->name);
        $record->save();

        return $record->afterCompleting();
    }

    public function cancel(): static
    {
        // canceling
        $record = $this->beforeCanceling();
        $record->setAttribute(Core::RECORD_STATUS, RecordStatus::cancelled->name);
        $record->save();

        return $record->afterCanceling();
    }

    public function beforeStartProcessing(): static
    {
        return $this;
    }

    public function afterStartProcessing(): static
    {
        return $this;
    }

    public function beforeSubmitting(): static
    {
        // Override this method to implement your own logic
        return $this;
    }

    public function afterSubmitting(): static
    {
        // after submit
        return $this;
    }

    public function beforeConfirming(): static
    {
        // Override this method to implement your own logic
        return $this;
    }

    public function afterConfirming(): static
    {
        return $this;
    }

    public function beforePosting(): static
    {
        return $this;
    }

    public function afterPosting(): static
    {
        return $this;
    }

    public function beforeClosing(): static
    {
        return $this;
    }

    public function afterClosing(): static
    {
        return $this;
    }

    public function beforeCompleting(): static
    {
        return $this;
    }

    public function afterCompleting(): static
    {
        return $this;
    }

    public function beforeCanceling(): static
    {
        return $this;
    }

    public function afterCanceling(): static
    {
        return $this;
    }

    public function scopeWhereDraft(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::draft->name);
    }

    public function scopeOrWhereDraft(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::draft->name);
    }

    public function scopeWhereProcessing(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::processing->name);
    }

    public function scopeOrWhereProcessing(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::processing->name);
    }

    public function scopeWhereSubmitted(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::submitted->name);
    }

    public function scopeOrWhereSubmitted(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::submitted->name);
    }

    public function scopeWherePosted(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::posted->name);
    }

    public function scopeOrWherePosted(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::posted->name);
    }

    public function scopeWhereConfirmed(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::confirmed->name);
    }

    public function scopeOrWhereConfirmed(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::confirmed->name);
    }

    public function scopeWhereClosed(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::closed->name);
    }

    public function scopeOrWhereClosed(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::posted->name);
    }

    public function scopeWhereCompleted(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::completed->name);
    }

    public function scopeOrWhereCompleted(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::completed->name);
    }

    public function scopeWhereCancelled(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->where(Core::RECORD_STATUS, $operator, RecordStatus::cancelled->name);
    }

    public function scopeOrWhereCancelled(Builder $query, bool $condition = true): Builder
    {
        $operator = $condition ? '=' : '!=';

        return $query->orWhere(Core::RECORD_STATUS, $operator, RecordStatus::cancelled->name);
    }

    public function scopeWithCancelled(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('record-status');
    }

    public function scopeOnlyCancelled(Builder|self $builder): Builder
    {
        return $builder->withCancelled()->whereCancelled(true);
    }
}
