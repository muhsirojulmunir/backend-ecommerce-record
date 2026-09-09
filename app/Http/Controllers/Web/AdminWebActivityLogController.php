<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWebActivityLogController extends Controller
{
    /** Log module yang berasal dari interaksi toko (frontend), bukan admin. */
    private const SHOP_LOG_NAMES = ['pesanan', 'ulasan', 'pengembalian', 'checkout', 'keranjang', 'toko', 'rpay', 'rpaywithdrawal'];

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'admin');
        if (! in_array($tab, ['admin', 'user', 'all'], true)) {
            $tab = 'admin';
        }

        $logs = $this->filtered($request, $tab)
            ->with('causer')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Hitung counter per tab
        $adminCount = Activity::whereHas('causer', fn ($q) => $q->whereIn('role', ['admin', 'super_admin']))->count();
        $userCount  = Activity::where(function ($q) {
            $q->whereHas('causer', fn ($q2) => $q2->where('role', 'customer'))
              ->orWhere(function ($q2) {
                  // Guest: causer_id null, log dari modul toko
                  $q2->whereNull('causer_id')->whereIn('log_name', self::SHOP_LOG_NAMES);
              });
        })->count();
        $allCount   = Activity::count();

        // Causers dropdown: disesuaikan per tab
        $causers = $this->caUsers($tab);

        return view('admin.activity-logs', [
            'tab'        => $tab,
            'logs'       => $logs,
            'filters'    => [
                'log_name' => $request->get('log_name', ''),
                'event'    => $request->get('event', ''),
                'causer'   => $request->get('causer', ''),
                'from'     => $request->get('from', ''),
                'to'       => $request->get('to', ''),
                'search'   => $request->get('search', ''),
            ],
            'logNames'   => Activity::select('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->filter()->values(),
            'causers'    => $causers,
            'stats'      => $this->stats(),
            'tabCounts'  => [
                'admin' => $adminCount,
                'user'  => $userCount,
                'all'   => $allCount,
            ],
        ]);
    }

    /**
     * Unduh log sesuai filter aktif sebagai CSV, termasuk kolom Perangkat.
     */
    public function export(Request $request): StreamedResponse
    {
        $tab  = $request->get('tab', 'admin');
        $logs = $this->filtered($request, $tab)->with('causer')->latest('id')->limit(5000)->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Waktu', 'Tab', 'Modul', 'Aksi', 'Deskripsi', 'Pelaku', 'Peran', 'Perangkat', 'IP', 'Subjek', 'Perubahan'], escape: '');

            foreach ($logs as $log) {
                $device     = DeviceDetector::fromActivity($log);
                $actorInfo  = DeviceDetector::actorInfo($log);
                $props      = is_array($log->properties) ? $log->properties : $log->properties->toArray();

                fputcsv($out, [
                    $log->created_at?->format('d/m/Y H:i:s'),
                    $actorInfo['type'],
                    $log->log_name,
                    $log->event ?? '-',
                    $log->description,
                    $actorInfo['name'],
                    $actorInfo['role_label'],
                    $device['formatted'] ?? '-',
                    $props['ip'] ?? '-',
                    class_basename($log->subject_type ?? '') . ' #' . ($log->subject_id ?? '-'),
                    json_encode($props, JSON_UNESCAPED_UNICODE),
                ], escape: '');
            }

            fclose($out);
        }, 'log-aktivitas-' . now()->format('Y-m-d-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Hapus log yang lebih tua dari sekian hari.
     */
    public function prune(Request $request)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:7', 'max:3650'],
        ], [
            'days.min' => 'Demi keamanan, log yang berumur kurang dari 7 hari tidak bisa dihapus.',
        ]);

        $cutoff  = now()->subDays($data['days']);
        $deleted = Activity::where('created_at', '<', $cutoff)->delete();

        return redirect()->route('admin.activity-logs')->with(
            'success',
            $deleted > 0
                ? "{$deleted} catatan log yang lebih tua dari {$data['days']} hari berhasil dihapus."
                : "Tidak ada log yang lebih tua dari {$data['days']} hari."
        );
    }

    // ─── Helper privat ────────────────────────────────────────────────────────

    /**
     * Membangun query Activity dengan filter tab + filter UI.
     */
    private function filtered(Request $request, string $tab)
    {
        $query = Activity::query();

        // Filter berdasarkan tab
        match ($tab) {
            'admin' => $query->whereHas('causer', fn ($q) => $q->whereIn('role', ['admin', 'super_admin'])),
            'user'  => $query->where(function ($q) {
                $q->whereHas('causer', fn ($q2) => $q2->where('role', 'customer'))
                  ->orWhere(function ($q2) {
                      $q2->whereNull('causer_id')->whereIn('log_name', self::SHOP_LOG_NAMES);
                  });
            }),
            default => null, // 'all' — tidak ada filter tambahan
        };

        // Filter form: modul
        if (filled($request->get('log_name'))) {
            $query->where('log_name', $request->get('log_name'));
        }

        // Filter form: aksi
        if (filled($request->get('event'))) {
            $query->where('event', $request->get('event'));
        }

        // Filter form: pelaku (tidak berlaku untuk tab user agar tidak konflik dengan filter guest)
        if (filled($request->get('causer'))) {
            $query->where('causer_id', $request->get('causer'));
        }

        if ($from = $this->parseDate($request->get('from'))) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to = $this->parseDate($request->get('to'))) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        if (filled($search = $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Daftar user untuk dropdown filter Pelaku, disesuaikan per tab.
     */
    private function caUsers(string $tab)
    {
        $causerIds = Activity::when($tab === 'admin', fn ($q) =>
            $q->whereHas('causer', fn ($q2) => $q2->whereIn('role', ['admin', 'super_admin']))
        )->when($tab === 'user', fn ($q) =>
            $q->whereHas('causer', fn ($q2) => $q2->where('role', 'customer'))
        )->whereNotNull('causer_id')->distinct()->pluck('causer_id');

        return User::whereIn('id', $causerIds)->orderBy('name')->get(['id', 'name', 'email', 'role']);
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function stats(): array
    {
        return [
            'total'   => Activity::count(),
            'today'   => Activity::whereDate('created_at', today())->count(),
            'week'    => Activity::where('created_at', '>=', now()->subDays(7))->count(),
            'created' => Activity::where('event', 'created')->count(),
            'updated' => Activity::where('event', 'updated')->count(),
            'deleted' => Activity::where('event', 'deleted')->count(),
        ];
    }
}
