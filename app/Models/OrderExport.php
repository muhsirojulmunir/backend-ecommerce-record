<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Satu berkas hasil ekspor pesanan.
 */
class OrderExport extends Model
{
    /** Banyaknya berkas yang disimpan; selebihnya dibuang otomatis. */
    public const BATAS_RIWAYAT = 10;

    protected $fillable = [
        'user_id', 'file_name', 'path',
        'dari', 'sampai',
        'jumlah_pesanan', 'jumlah_baris', 'ukuran',
    ];

    protected function casts(): array
    {
        return [
            'dari'   => 'date',
            'sampai' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Ukuran berkas dalam satuan yang enak dibaca. */
    public function getUkuranRapiAttribute(): string
    {
        $b = (int) $this->ukuran;

        if ($b < 1024) {
            return $b . ' B';
        }

        return $b < 1048576
            ? round($b / 1024) . ' KB'
            : round($b / 1048576, 1) . ' MB';
    }

    /** Rentang tanggal yang diekspor, sebagai satu kalimat. */
    public function getRentangAttribute(): string
    {
        if (! $this->dari && ! $this->sampai) {
            return 'Semua tanggal';
        }

        return ($this->dari?->format('d/m/Y') ?? '…')
            . ' – ' . ($this->sampai?->format('d/m/Y') ?? '…');
    }

    /** Berkasnya masih ada di cakram? */
    public function getBerkasAdaAttribute(): bool
    {
        return filled($this->path) && file_exists(storage_path('app/' . $this->path));
    }

    /**
 * Membuang riwayat yang melewati batas, berikut berkasnya.
 */
    public static function pangkasRiwayat(): int
    {
        $lebih = static::orderByDesc('created_at')
            ->orderByDesc('id')
            ->skip(self::BATAS_RIWAYAT)
            ->take(100)
            ->get();

        foreach ($lebih as $lama) {
            $lama->delete();

            // Berkasnya baru dihapus setelah dipastikan tidak ada baris lain yang masih menunjuk jalur yang sama.
            if (blank($lama->path)) {
                continue;
            }

            if (! static::where('path', $lama->path)->exists()) {
                Storage::disk('local')->delete($lama->path);
            }
        }

        return $lebih->count();
    }
}
