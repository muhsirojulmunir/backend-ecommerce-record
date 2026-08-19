<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Pembungkus trait LogsActivity milik Spatie dengan konfigurasi seragam untuk seluruh model, supaya...
 */
trait RecordsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->activityLogName ?? strtolower(class_basename($this)))
            ->logOnly($this->activityLogAttributes ?? ['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at', 'remember_token', 'password']);
    }

    /**
     * Kalimat deskripsi yang disimpan bersama log.
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $label = $this->activityModelLabel ?? class_basename($this);

        $action = match ($eventName) {
            'created' => 'menambahkan',
            'updated' => 'memperbarui',
            'deleted' => 'menghapus',
            default   => $eventName,
        };

        return "{$action} {$label}: " . $this->activityTitle();
    }

    /**
     * Judul subjek yang ditampilkan di tabel log.
     * Model boleh menimpa method ini kalau punya kolom nama sendiri.
     */
    public function activityTitle(): string
    {
        foreach (['name', 'title', 'order_number', 'label', 'email', 'key'] as $column) {
            if (! empty($this->{$column})) {
                return (string) $this->{$column};
            }
        }

        return '#' . $this->getKey();
    }
}
