<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWebActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filtered($request)
            ->with('causer')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity-logs', [
            'logs'    => $logs,
            'filters' => [
                'log_name' => $request->get('log_name', ''),
                'event'    => $request->get('event', ''),
                'causer'   => $request->get('causer', ''),
                'from'     => $request->get('from', ''),
                'to'       => $request->get('to', ''),
                'search'   => $request->get('search', ''),
            ],
            'logNames' => Activity::select('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->filter()->values(),
            'causers'  => User::whereIn('id', Activity::whereNotNull('causer_id')->distinct()->pluck('causer_id'))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
            'stats'    => $this->stats(),
        ]);
    }

    /**
     * Unduh log sesuai filter aktif sebagai CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $logs = $this->filtered($request)->with('causer')->latest('id')->limit(5000)->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Waktu', 'Modul', 'Aksi', 'Deskripsi', 'Pelaku', 'Subjek', 'Perubahan'], escape: '');

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at?->format('d/m/Y H:i:s'),
                    $log->log_name,
                    $log->event ?? '-',
                    $log->description,
                    $log->causer?->name ?? 'Sistem',
                    class_basename($log->subject_type ?? '') . ' #' . ($log->subject_id ?? '-'),
                    json_encode($log->properties, JSON_UNESCAPED_UNICODE),
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

    private function filtered(Request $request)
    {
        $query = Activity::query();

        if (filled($request->get('log_name'))) {
            $query->where('log_name', $request->get('log_name'));
        }

        if (filled($request->get('event'))) {
            $query->where('event', $request->get('event'));
        }

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
