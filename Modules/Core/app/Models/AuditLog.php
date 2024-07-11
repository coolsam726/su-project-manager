<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class AuditLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRecord()
    {
        return DB::table($this->table_name)->find($this->record_id);
    }

    public function getChangedColumns(): array
    {
        return array_keys($this->changed_values);
    }

    public function getOldValues()
    {
        return $this->old_values;
    }

    public function getNewValues()
    {
        return $this->new_values;
    }

    public function getChangedValues(): array
    {
        return collect($this->changed_values)->mapWithKeys(function ($value, $key) {
            return [$key => new HtmlString("<p>Old Value: <strong>{$value['old']}</strong></p><p>New Value: <strong>{$value['new']}</strong></p>")];
        })->toArray();
    }

    public function getIpAddress()
    {
        return $this->ip_address;
    }

    public function getAction()
    {
        return $this->action;
    }
}
