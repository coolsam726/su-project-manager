<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Core;

use function Modules\Core\Support\core;

/**
 * @mixin Model
 */
trait HasCode
{
    public function initializeHasCode(): void
    {
        $this->mergeCasts([
            //
        ]);
    }

    public static function bootHasCode(): void
    {
        static::creating(function (Model $model) {
            if (core()->modelHasColumn($model, 'code') && ! $model->getAttribute('code')) {
                $model->generateCode();
            }
        });
        static::updating(function (Model $model) {
            if (core()->modelHasColumn($model, 'code') && ! $model->getAttribute('code')) {
                $model->generateCode(useId: true);
            }
        });
    }

    public function getPrefix(): string
    {
        return core()->generatePrefix($this->getTable());
    }

    public function generateCode(int $increment = 1, bool $useId = false): string
    {
        // use current count but ensure a unique code
        $prefix = $this->getPrefix();
        if ($useId && $id = $this->getAttribute('id')) {
            return str($id)->padLeft(4, '0')->prepend($prefix)->toString();
        }
        $team = $this->getAttribute(Core::TEAM_COLUMN);
        if ($prefix) {
            $query = $this->newQuery()->where('code', 'like', $prefix.'%');
            if (core()->modelHasColumn($this, Core::TEAM_COLUMN) && $team) {
                $query->where(Core::TEAM_COLUMN, '=', $team);
            }
            $count = $query->count();
        } else {
            $query = $this->newQuery();
            if (core()->modelHasColumn($this, Core::TEAM_COLUMN) && $team) {
                $query->where(Core::TEAM_COLUMN, '=', $team);
            }
            $count = $query->count();
        }
        if ($increment > 10) {
            // to avoid infinite loop generate a unique code
            $code = str(uniqid())->upper()->toString();
            $this->setAttribute('code', $code);

            return $code;
        }
        $code = str($count + $increment)->padLeft(4, '0')->prepend($prefix)->toString();
        $q = $this->newQuery()->where('code', $code);
        if (core()->modelHasColumn($this, Core::TEAM_COLUMN) && $team) {
            $q->where(Core::TEAM_COLUMN, '=', $team);
        }
        if ($q->exists()) {
            return $this->generateCode(increment: $increment + 1);
        }
        $this->setAttribute('code', $code);

        return $code;
    }
}
