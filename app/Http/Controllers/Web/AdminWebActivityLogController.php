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
    private const SHOP_LOG_NAMES = ['pesanan', 'ulasan', 'pengembalian', 'checkout', 'keranjang', 'toko', 'rpay', 'rpaywithdrawal', 'produk', 'pencarian', 'evaluasi_web'];

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

        // Filter periode untuk evaluasi seksi website (default: 30d)
        $dwellPeriod = $request->get('dwell_period', '30d');
        $dwellFrom   = $request->get('dwell_from');
        $dwellTo     = $request->get('dwell_to');

        if (filled($dwellFrom) || filled($dwellTo)) {
            $dwellPeriod = 'custom';
        } elseif (! in_array($dwellPeriod, ['today', '7d', '30d', 'all', 'custom'], true)) {
            $dwellPeriod = '30d';
        }

        return view('admin.activity-logs', [
            'tab'          => $tab,
            'logs'         => $logs,
            'filters'      => [
                'log_name'     => $request->get('log_name', ''),
                'event'        => $request->get('event', ''),
                'causer'       => $request->get('causer', ''),
                'from'         => $request->get('from', ''),
                'to'           => $request->get('to', ''),
                'search'       => $request->get('search', ''),
                'dwell_period' => $dwellPeriod,
                'dwell_from'   => $dwellFrom,
                'dwell_to'     => $dwellTo,
            ],
            'dwellPeriod'  => $dwellPeriod,
            'dwellFrom'    => $dwellFrom,
            'dwellTo'      => $dwellTo,
            'logNames'     => Activity::select('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->filter()->values(),
            'causers'      => $causers,
            'stats'        => $this->stats(),
            'analytics'    => $this->analytics($dwellPeriod, $request, $dwellFrom, $dwellTo),
            'tabCounts'    => [
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

    /**
     * Hitung analitik login, rasio perangkat, produk teratas, dan pencarian terpopuler.
     */
    private function analytics(?string $dwellPeriod = '30d', ?Request $request = null, ?string $dwellFrom = null, ?string $dwellTo = null): array
    {
        // 1. Statistik Login
        $loginQuery = Activity::where('log_name', 'auth')->where('event', 'login');

        $totalLogin    = (clone $loginQuery)->count();
        $todayLogin    = (clone $loginQuery)->whereDate('created_at', today())->count();
        $weekLogin     = (clone $loginQuery)->where('created_at', '>=', now()->subDays(7))->count();
        $adminLogin    = (clone $loginQuery)->whereHas('causer', fn ($q) => $q->whereIn('role', ['admin', 'super_admin']))->count();
        $customerLogin = (clone $loginQuery)->whereHas('causer', fn ($q) => $q->where('role', 'customer'))->count();

        // 2. Evaluasi Proporsi Perangkat (Sample 500 log terbaru)
        $recentLogs = Activity::latest('id')->limit(500)->get(['properties']);
        $mobile = 0;
        $desktop = 0;
        $tablet = 0;

        foreach ($recentLogs as $log) {
            $props   = is_array($log->properties) ? $log->properties : ($log->properties?->toArray() ?? []);
            $devType = $props['device']['device_type'] ?? $props['device_type'] ?? null;

            if ($devType === 'Mobile') {
                $mobile++;
            } elseif ($devType === 'Desktop') {
                $desktop++;
            } elseif ($devType === 'Tablet') {
                $tablet++;
            }
        }

        $totalDevices = $mobile + $desktop + $tablet;
        $mobilePct    = $totalDevices > 0 ? (int) round(($mobile / $totalDevices) * 100) : 0;
        $desktopPct   = $totalDevices > 0 ? (int) round(($desktop / $totalDevices) * 100) : 0;
        $tabletPct    = $totalDevices > 0 ? (int) round(($tablet / $totalDevices) * 100) : 0;

        // 3. Top 3 Produk Paling Banyak Dilihat (7 Hari Terakhir)
        $topViewedRaw = Activity::where('log_name', 'produk')
            ->where('event', 'view')
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('subject_id')
            ->selectRaw('subject_id, COUNT(*) as views')
            ->groupBy('subject_id')
            ->orderByDesc('views')
            ->limit(3)
            ->get();

        $topProducts = [];
        if ($topViewedRaw->isNotEmpty()) {
            $productModels = \App\Models\Product::whereIn('id', $topViewedRaw->pluck('subject_id'))->get()->keyBy('id');
            foreach ($topViewedRaw as $row) {
                $prod = $productModels->get($row->subject_id);
                if ($prod) {
                    $topProducts[] = [
                        'id'    => $prod->id,
                        'name'  => $prod->name,
                        'views' => (int) $row->views,
                    ];
                }
            }
        }

        // 4. Top 3 Kata Kunci Pencarian (7 Hari Terakhir)
        $searchLogs = Activity::where('log_name', 'pencarian')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest('id')
            ->limit(150)
            ->get(['properties']);

        $searchFreq = [];
        foreach ($searchLogs as $sLog) {
            $props = is_array($sLog->properties) ? $sLog->properties : ($sLog->properties?->toArray() ?? []);
            $kw    = strtolower(trim((string) ($props['keyword'] ?? '')));
            if ($kw !== '') {
                $searchFreq[$kw] = ($searchFreq[$kw] ?? 0) + 1;
            }
        }
        arsort($searchFreq);
        $topSearches = array_slice($searchFreq, 0, 3, true);

        // 5. Dwell Time Analytics — Evaluasi Seksi Website berdasarkan filter periode
        $dwellQuery = Activity::where('log_name', 'evaluasi_web')
            ->where('event', 'dwell')
            ->with('causer');

        // Filter rentang tanggal
        if (filled($dwellFrom) || filled($dwellTo)) {
            if (filled($dwellFrom)) {
                $dwellQuery->whereDate('created_at', '>=', $dwellFrom);
            }
            if (filled($dwellTo)) {
                $dwellQuery->whereDate('created_at', '<=', $dwellTo);
            }
        } elseif ($dwellPeriod === 'today') {
            $dwellQuery->where('created_at', '>=', now()->startOfDay());
        } elseif ($dwellPeriod === '7d') {
            $dwellQuery->where('created_at', '>=', now()->subDays(7));
        } elseif ($dwellPeriod === 'all') {
            // semua waktu tanpa batasan awal
        } elseif ($request && (filled($request->get('from')) || filled($request->get('to')))) {
            if (filled($request->get('from'))) {
                $dwellQuery->whereDate('created_at', '>=', $request->get('from'));
            }
            if (filled($request->get('to'))) {
                $dwellQuery->whereDate('created_at', '<=', $request->get('to'));
            }
        } else { // default '30d'
            $dwellQuery->where('created_at', '>=', now()->subDays(30));
        }

        $dwellLogs = $dwellQuery->latest('id')->get();

        $dwellAgg = []; // section_key => [label, total_seconds, views, pages => [...], viewers => [...]]

        foreach ($dwellLogs as $dl) {
            $props = is_array($dl->properties) ? $dl->properties : ($dl->properties?->toArray() ?? []);
            $key   = (string) ($props['section'] ?? $props['section_id'] ?? '');
            $lbl   = (string) ($props['label']   ?? $props['section_label'] ?? $key);
            $secs  = (int)    ($props['seconds'] ?? $props['duration_seconds'] ?? 0);
            $page  = (string) ($props['page']    ?? $props['page_url'] ?? $props['page_name'] ?? '');

            if ($key === '' || $secs <= 0) continue;

            if (!isset($dwellAgg[$key])) {
                $dwellAgg[$key] = [
                    'label'         => $lbl,
                    'total_seconds' => 0,
                    'views'         => 0,
                    'pages'         => [],
                    'viewers'       => [],
                ];
            }

            $dwellAgg[$key]['total_seconds'] += $secs;
            $dwellAgg[$key]['views']         += 1;

            if ($page !== '') {
                $dwellAgg[$key]['pages'][$page] = ($dwellAgg[$key]['pages'][$page] ?? 0) + 1;
            }

            // Identifikasi siapa orangnya (Pelaku)
            $actor  = DeviceDetector::actorInfo($dl);
            $device = DeviceDetector::fromActivity($dl);
            $ip     = $props['ip'] ?? '';

            if (!$actor['is_guest'] && $dl->causer) {
                $viewerKey   = 'u_' . $dl->causer_id;
                $viewerName  = $actor['name'];
                $viewerSub   = 'Customer' . ($actor['email'] ? ' · ' . $actor['email'] : '');
                $viewerType  = 'customer';
                $badgeClass  = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                $icon        = 'fa-user-check';
            } else {
                $viewerKey   = 'g_' . ($ip ?: 'guest_' . $dl->id);
                $deviceShort = ($device['platform'] ?? 'Tamu') . ($device['browser'] ? ' · ' . $device['browser'] : '');
                $viewerName  = 'Pengunjung Tamu';
                $viewerSub   = $deviceShort . ($ip ? " [{$ip}]" : '');
                $viewerType  = 'guest';
                $badgeClass  = 'bg-amber-100 text-amber-800 border-amber-200';
                $icon        = 'fa-user-secret';
            }

            if (!isset($dwellAgg[$key]['viewers'][$viewerKey])) {
                $dwellAgg[$key]['viewers'][$viewerKey] = [
                    'name'        => $viewerName,
                    'sub'         => $viewerSub,
                    'type'        => $viewerType,
                    'badge_class' => $badgeClass,
                    'icon'        => $icon,
                    'count'       => 0,
                    'seconds'     => 0,
                ];
            }
            $dwellAgg[$key]['viewers'][$viewerKey]['count']   += 1;
            $dwellAgg[$key]['viewers'][$viewerKey]['seconds'] += $secs;
        }

        // Urutkan seksi berdasarkan total detik tertinggi (tanpa dibatasi 10 saja)
        uasort($dwellAgg, fn ($a, $b) => $b['total_seconds'] <=> $a['total_seconds']);

        $totalDwellSecs = array_sum(array_column($dwellAgg, 'total_seconds'));

        $dwellSections = [];
        foreach ($dwellAgg as $sectionKey => $d) {
            $avgSecs = $d['views'] > 0 ? (int) round($d['total_seconds'] / $d['views']) : 0;
            $pct     = $totalDwellSecs > 0 ? round(($d['total_seconds'] / $totalDwellSecs) * 100, 1) : 0;

            // Urutkan rincian viewers dari detik terbanyak
            uasort($d['viewers'], fn ($a, $b) => $b['seconds'] <=> $a['seconds']);

            $dwellSections[] = [
                'section'              => $sectionKey,
                'label'                => $d['label'],
                'total_seconds'        => $d['total_seconds'],
                'avg_seconds'          => $avgSecs,
                'views'                => $d['views'],
                'pct'                  => $pct,
                'pages'                => array_keys($d['pages']),
                'unique_viewers_count' => count($d['viewers']),
                'viewers'              => array_values(array_slice($d['viewers'], 0, 8)), // daftar pengunjung teratas
            ];
        }

        return [
            'login' => [
                'total'    => $totalLogin,
                'today'    => $todayLogin,
                'week'     => $weekLogin,
                'admin'    => $adminLogin,
                'customer' => $customerLogin,
            ],
            'devices' => [
                'total'       => $totalDevices,
                'mobile'      => $mobile,
                'desktop'     => $desktop,
                'tablet'      => $tablet,
                'mobile_pct'  => $mobilePct,
                'desktop_pct' => $desktopPct,
                'tablet_pct'  => $tabletPct,
            ],
            'top_products'      => $topProducts,
            'top_searches'      => $topSearches,
            'dwell_sections'    => $dwellSections,
            'dwell_total_secs'  => $totalDwellSecs,
        ];
    }
}
