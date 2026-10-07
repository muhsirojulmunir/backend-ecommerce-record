<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher - {{ $vouchers->first()?->batch_label ?? 'Voucher' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap"
        rel="stylesheet">
    <style>
        /* ─── Reset ─── */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        /* ─── Print Controls (hidden when printing) ─── */
        .print-controls {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
        }

        .print-controls h2 {
            font-size: 14px;
            font-weight: 800;
        }

        .print-controls p {
            font-size: 11px;
            opacity: 0.8;
        }

        .print-controls button {
            background: white;
            color: #4f46e5;
            border: none;
            padding: 10px 28px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .print-controls button:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .page-wrapper {
            padding-top: 80px;
        }

        /* ─── F4 Paper Layout ───
         * Ukuran F4: 215mm × 330mm
         * Voucher grid: 4 kolom × 8 baris = 32 voucher per halaman
         * Ukuran setiap voucher: ~50mm × ~38mm
         */
        .voucher-page {
            width: 215mm;
            min-height: 330mm;
            margin: 20px auto;
            background: white;
            padding: 8mm;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(8, 1fr);
            gap: 2mm;
            height: calc(330mm - 16mm);
        }

        /* ─── Voucher Card ─── */
        .voucher-card {
            border: 1.5px dashed #cbd5e1;
            border-radius: 6px;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #fefefe 0%, #f8fafc 100%);
        }

        /* Dekoratif: garis gradient atas */
        .voucher-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
        }

        /* Gunting icon di pojok */
        .voucher-card::after {
            content: '✂';
            position: absolute;
            top: -1px;
            right: 3px;
            font-size: 8px;
            color: #94a3b8;
            transform: rotate(-45deg);
        }

        .voucher-brand {
            font-size: 6px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #6366f1;
            margin-bottom: 1.5mm;
        }

        .voucher-label {
            font-size: 7px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }

        .voucher-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #1e293b;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            padding: 2mm 4mm;
            border-radius: 4px;
            margin: 1.5mm 0;
            border: 1px solid #e2e8f0;
        }

        .voucher-amount {
            font-size: 11px;
            font-weight: 800;
            color: #059669;
            margin-top: 1mm;
        }

        .voucher-expires {
            font-size: 5.5px;
            color: #94a3b8;
            margin-top: 1mm;
            font-weight: 600;
        }

        /* ─── Print Styles ─── */
        @media print {
            .print-controls {
                display: none !important;
            }

            .page-wrapper {
                padding-top: 0;
            }

            body {
                background: white;
            }

            @page {
                size: 215mm 330mm;
                /* F4 */
                margin: 0;
            }

            .voucher-page {
                width: 215mm;
                height: 330mm;
                margin: 0;
                padding: 8mm;
                box-shadow: none;
                border-radius: 0;
                page-break-after: always;
            }

            .voucher-page:last-child {
                page-break-after: auto;
            }

            .voucher-card {
                border-color: #94a3b8;
            }

            .voucher-card::before {
                /* Print-safe: gunakan warna solid */
                background: #6366f1;
            }
        }

        /* ─── Preview spacing ─── */
        @media screen {
            .voucher-page+.voucher-page {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>
    {{-- Print Controls --}}
    <div class="print-controls">
        <div>
            <h2>🎫 Preview Cetak Voucher</h2>
            <p>{{ $vouchers->count() }} voucher • {{ $vouchers->first()?->batch_label ?? '-' }} • Kertas F4 (215 × 330
                mm)</p>
        </div>
        <button onclick="window.print()">🖨️ Cetak Sekarang</button>
    </div>

    <div class="page-wrapper">
        @php
            $perPage = 32; // 4 kolom × 8 baris
            $pages = $vouchers->chunk($perPage);
        @endphp

        @foreach($pages as $pageVouchers)
            <div class="voucher-page">
                <div class="voucher-grid">
                    @foreach($pageVouchers as $v)
                        <div class="voucher-card">
                            <div class="voucher-brand">RECORD</div>
                            <div class="voucher-label">Kode Voucher</div>
                            <div class="voucher-code">{{ $v->code }}</div>
                            <div class="voucher-amount">{{ $v->formatted_amount }}</div>
                            @if($v->expires_at)
                                <div class="voucher-expires">Berlaku s/d {{ $v->expires_at->format('d/m/Y') }}</div>
                            @else
                                <div class="voucher-expires">Tanpa Batas Waktu</div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Isi slot kosong agar grid tetap rapi --}}
                    @for($i = $pageVouchers->count(); $i < $perPage; $i++)
                        <div class="voucher-card" style="border-color: transparent; background: transparent;">
                            <div style="visibility: hidden;">—</div>
                        </div>
                    @endfor
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>