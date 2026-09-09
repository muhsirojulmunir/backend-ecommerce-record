<?php

namespace App\Support;

class DeviceDetector
{
    /**
     * Parse User-Agent string to structured device details.
     */
    public static function detect(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'device_type'  => 'Sistem',
                'device_label' => 'Sistem / Server',
                'platform'     => 'Server / CLI',
                'browser'      => 'Proses Otomatis',
                'is_mobile'    => false,
                'is_tablet'    => false,
                'is_desktop'   => false,
                'icon'         => 'fa-server',
                'badge_class'  => 'bg-slate-100 text-slate-700 border-slate-200',
                'formatted'    => 'Sistem / CLI',
            ];
        }

        $ua = $userAgent;

        // 1. Detect Bots / Crawlers
        if (preg_match('/(googlebot|bingbot|slurp|duckduckbot|baiduspider|yandexbot|sogou|exabot|facebot|facebookexternalhit)/i', $ua, $m)) {
            $botName = ucfirst($m[1]);
            return [
                'device_type'  => 'Bot',
                'device_label' => 'Web Crawler / Bot',
                'platform'     => 'Bot',
                'browser'      => $botName,
                'is_mobile'    => false,
                'is_tablet'    => false,
                'is_desktop'   => false,
                'icon'         => 'fa-robot',
                'badge_class'  => 'bg-zinc-100 text-zinc-600 border-zinc-200',
                'formatted'    => "Bot • {$botName}",
            ];
        }

        // 2. Detect Platform / OS
        $platform = 'Unknown OS';
        $isMobile = false;
        $isTablet = false;
        $isDesktop = false;

        if (preg_match('/windows nt 10\.0/i', $ua)) {
            $platform = 'Windows 10/11';
            $isDesktop = true;
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $platform = 'Windows 8.1';
            $isDesktop = true;
        } elseif (preg_match('/windows nt 6\.2/i', $ua)) {
            $platform = 'Windows 8';
            $isDesktop = true;
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $platform = 'Windows 7';
            $isDesktop = true;
        } elseif (preg_match('/windows/i', $ua)) {
            $platform = 'Windows';
            $isDesktop = true;
        } elseif (preg_match('/ipad/i', $ua)) {
            $platform = 'iPadOS';
            if (preg_match('/OS (\d+[_.]\d+)/i', $ua, $m)) {
                $platform .= ' ' . str_replace('_', '.', $m[1]);
            }
            $isTablet = true;
        } elseif (preg_match('/iphone/i', $ua)) {
            $platform = 'iOS (iPhone)';
            if (preg_match('/OS (\d+[_.]\d+)/i', $ua, $m)) {
                $platform .= ' ' . str_replace('_', '.', $m[1]);
            }
            $isMobile = true;
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
            if (preg_match('/Android (\d+(\.\d+)?)/i', $ua, $m)) {
                $platform .= ' ' . $m[1];
            }
            if (preg_match('/mobile/i', $ua)) {
                $isMobile = true;
            } else {
                $isTablet = true;
            }
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
            if (preg_match('/Mac OS X (\d+[_.]\d+)/i', $ua, $m)) {
                $platform .= ' ' . str_replace('_', '.', $m[1]);
            }
            $isDesktop = true;
        } elseif (preg_match('/cros/i', $ua)) {
            $platform = 'Chrome OS';
            $isDesktop = true;
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
            $isDesktop = true;
        }

        // 3. Detect Browser
        $browser = 'Web Browser';
        if (preg_match('/edg\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Edge ' . explode('.', $m[1])[0];
        } elseif (preg_match('/samsungbrowser\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Samsung Internet ' . explode('.', $m[1])[0];
        } elseif (preg_match('/opr\/([\d.]+)|opera\/([\d.]+)/i', $ua, $m)) {
            $ver = !empty($m[1]) ? $m[1] : $m[2];
            $browser = 'Opera ' . explode('.', $ver)[0];
        } elseif (preg_match('/chrome\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Chrome ' . explode('.', $m[1])[0];
        } elseif (preg_match('/firefox\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Firefox ' . explode('.', $m[1])[0];
        } elseif (preg_match('/version\/([\d.]+).*safari/i', $ua, $m)) {
            $browser = 'Safari ' . explode('.', $m[1])[0];
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Safari';
        }

        // 4. Classify device type & badges
        if ($isTablet) {
            $deviceType = 'Tablet';
            $icon = 'fa-tablet-screen-button';
            $badgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
        } elseif ($isMobile) {
            $deviceType = 'Mobile';
            $icon = 'fa-mobile-screen-button';
            $badgeClass = 'bg-purple-50 text-purple-700 border border-purple-200';
        } else {
            $deviceType = 'Desktop';
            $isDesktop = true;
            $icon = 'fa-desktop';
            $badgeClass = 'bg-sky-50 text-sky-700 border border-sky-200';
        }

        return [
            'device_type'  => $deviceType,
            'device_label' => $deviceType,
            'platform'     => $platform,
            'browser'      => $browser,
            'is_mobile'    => $isMobile,
            'is_tablet'    => $isTablet,
            'is_desktop'   => $isDesktop,
            'icon'         => $icon,
            'badge_class'  => $badgeClass,
            'formatted'    => "{$deviceType} • {$platform} • {$browser}",
        ];
    }

    /**
     * Resolve device info from Activity model instance.
     */
    public static function fromActivity(mixed $activity): array
    {
        $props = is_array($activity->properties)
            ? $activity->properties
            : (is_object($activity->properties) ? $activity->properties->toArray() : []);

        // 1. Sudah tersimpan di properties['device']
        if (!empty($props['device']) && is_array($props['device'])) {
            return $props['device'];
        }

        // 2. Ada properties['user_agent']
        if (!empty($props['user_agent'])) {
            return self::detect($props['user_agent']);
        }

        // 3. Log oleh admin di admin panel
        if ($activity->causer && in_array($activity->causer->role, ['admin', 'super_admin'])) {
            return [
                'device_type'  => 'Desktop',
                'device_label' => 'Desktop',
                'platform'     => 'Admin Console',
                'browser'      => 'Web Browser',
                'is_mobile'    => false,
                'is_tablet'    => false,
                'is_desktop'   => true,
                'icon'         => 'fa-desktop',
                'badge_class'  => 'bg-sky-50 text-sky-700 border border-sky-200',
                'formatted'    => 'Desktop • Admin Web Console',
            ];
        }

        // 4. Log oleh customer terdaftar
        if ($activity->causer && $activity->causer->role === 'customer') {
            return [
                'device_type'  => 'Mobile / Web',
                'device_label' => 'Customer App / Web',
                'platform'     => 'Customer Session',
                'browser'      => 'Web Browser',
                'is_mobile'    => true,
                'is_tablet'    => false,
                'is_desktop'   => false,
                'icon'         => 'fa-mobile-screen-button',
                'badge_class'  => 'bg-purple-50 text-purple-700 border border-purple-200',
                'formatted'    => 'Mobile / Web • Customer Session',
            ];
        }

        // 5. Tamu (Guest) checkout / aksi toko tanpa login
        if (empty($activity->causer_id)) {
            $isShopAction = in_array($activity->log_name, ['pesanan', 'ulasan', 'pengembalian', 'checkout', 'keranjang', 'toko', 'rpay']);
            if ($isShopAction) {
                return [
                    'device_type'  => 'Desktop / Mobile',
                    'device_label' => 'Pengunjung Toko',
                    'platform'     => 'Guest Browser',
                    'browser'      => 'Web Store',
                    'is_mobile'    => false,
                    'is_tablet'    => false,
                    'is_desktop'   => true,
                    'icon'         => 'fa-globe',
                    'badge_class'  => 'bg-amber-50 text-amber-700 border border-amber-200',
                    'formatted'    => 'Desktop / Mobile • Pengunjung Toko',
                ];
            }
        }

        // 6. Default Sistem
        return [
            'device_type'  => 'Sistem',
            'device_label' => 'Sistem Otomatis',
            'platform'     => 'Server Task',
            'browser'      => 'Artisan / Cron',
            'is_mobile'    => false,
            'is_tablet'    => false,
            'is_desktop'   => false,
            'icon'         => 'fa-server',
            'badge_class'  => 'bg-slate-100 text-slate-700 border border-slate-200',
            'formatted'    => 'Sistem / CLI',
        ];
    }

    /**
     * Resolve actor/pelaku info from Activity instance.
     */
    public static function actorInfo(mixed $activity): array
    {
        if ($activity->causer) {
            $role = $activity->causer->role ?? 'user';

            if ($role === 'super_admin') {
                return [
                    'type'        => 'super_admin',
                    'role_label'  => 'Super Admin',
                    'badge_class' => 'bg-purple-100 text-purple-800 border border-purple-200',
                    'icon'        => 'fa-shield-halved',
                    'name'        => $activity->causer->name,
                    'email'       => $activity->causer->email,
                    'is_guest'    => false,
                ];
            }

            if ($role === 'admin') {
                return [
                    'type'        => 'admin',
                    'role_label'  => 'Admin',
                    'badge_class' => 'bg-blue-100 text-blue-800 border border-blue-200',
                    'icon'        => 'fa-user-shield',
                    'name'        => $activity->causer->name,
                    'email'       => $activity->causer->email,
                    'is_guest'    => false,
                ];
            }

            return [
                'type'        => 'customer',
                'role_label'  => 'Customer',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                'icon'        => 'fa-user',
                'name'        => $activity->causer->name,
                'email'       => $activity->causer->email,
                'is_guest'    => false,
            ];
        }

        // Causer is null
        $isGuestStoreAction = in_array($activity->log_name, ['pesanan', 'ulasan', 'pengembalian', 'checkout', 'keranjang', 'toko', 'rpay']);

        if ($isGuestStoreAction) {
            return [
                'type'        => 'guest',
                'role_label'  => 'Guest (Tamu)',
                'badge_class' => 'bg-amber-100 text-amber-800 border border-amber-200',
                'icon'        => 'fa-user-secret',
                'name'        => 'Pengunjung Tamu',
                'email'       => 'Belum / Tidak Login',
                'is_guest'    => true,
            ];
        }

        return [
            'type'        => 'system',
            'role_label'  => 'Sistem',
            'badge_class' => 'bg-gray-100 text-gray-700 border border-gray-200',
            'icon'        => 'fa-robot',
            'name'        => 'Sistem Otomatis',
            'email'       => '—',
            'is_guest'    => false,
        ];
    }
}
