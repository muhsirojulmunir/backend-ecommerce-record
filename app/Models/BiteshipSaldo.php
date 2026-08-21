<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu catatan saldo Biteship, dituliskan admin setelah mengisi ulang.
 */
class BiteshipSaldo extends Model
{
    protected $table = 'biteship_saldo';

    protected $fillable = [
        'saldo_tercatat',
        'dicatat_pada',
        'dicatat_oleh',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'dicatat_pada'   => 'datetime',
            'saldo_tercatat' => 'integer',
        ];
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
