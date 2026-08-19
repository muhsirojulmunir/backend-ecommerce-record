<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resi Pengiriman Standard – #{{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: 100mm 150mm;
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            background: #eef1f5;
            padding: 20px;
        }

        .no-print-bar {
            max-width: 100mm;
            margin: 0 auto 15px;
            background: #111827;
            color: #fff;
            padding: 10px 14px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Container Presisi 100mm x 150mm Ala Shopee */
        .shopee-label {
            width: 100mm;
            min-height: 150mm;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #000;
            padding: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: Arial, sans-serif;
        }

        /* 1. Top Header */
        .top-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 6px;
            border-bottom: 2px dashed #000;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 16px;
            font-weight: 900;
            color: #EE4D2D; /* Shopee Orange */
        }
        .brand-logo .store-tag {
            background: #EE4D2D;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            padding: 2px 4px;
            border-radius: 2px;
            margin-left: 2px;
        }
        .service-type {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .courier-badge {
            font-size: 13px;
            font-weight: 900;
            color: #D32F2F;
            text-transform: uppercase;
            text-align: right;
        }

        /* 2. Routing Code & Resi Box */
        .resi-box-row {
            display: flex;
            gap: 6px;
            margin: 6px 0;
        }
        .routing-code {
            border: 2px solid #000;
            padding: 4px 8px;
            font-size: 13px;
            font-weight: 900;
            text-align: center;
            min-width: 120px;
            letter-spacing: 1px;
        }
        .resi-code {
            border: 2px solid #000;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 900;
            flex-grow: 1;
            text-align: center;
        }

        /* 3. Barcode */
        .barcode-section {
            padding: 4px 0 8px;
            border-bottom: 2px dashed #000;
            text-align: center;
        }
        .barcode-lines {
            height: 42px;
            background: repeating-linear-gradient(
                90deg,
                #000 0px, #000 2px,
                #fff 2px, #fff 4px,
                #000 4px, #000 7px,
                #fff 7px, #fff 9px,
                #000 9px, #000 12px,
                #fff 12px, #fff 14px
            );
            width: 95%;
            margin: 0 auto;
        }

        /* 4. Pengirim & Penerima */
        .address-box {
            padding: 6px 0;
            border-bottom: 2px solid #000;
        }
        .address-header {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .address-header .penerima { font-weight: 900; font-size: 11px; }
        .address-header .pengirim { font-weight: 900; font-size: 10px; text-align: right; }
        .home-badge {
            display: inline-block;
            border: 1.5px solid #000;
            font-size: 9px;
            font-weight: 900;
            padding: 1px 6px;
            margin-bottom: 4px;
        }
        .full-address {
            font-size: 10px;
            line-height: 1.35;
            font-weight: 700;
            text-transform: uppercase;
        }
        .sender-info {
            font-size: 10px;
            text-align: right;
            line-height: 1.3;
        }

        /* 5. Highlight Kota & Kecamatan */
        .location-highlight {
            display: flex;
            border-bottom: 2px solid #000;
            margin-top: 4px;
        }
        .loc-box {
            flex: 1;
            border: 1.5px solid #000;
            padding: 4px;
            text-align: center;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
        }

        /* 6. Cashless & Status Payment */
        .cashless-row {
            display: flex;
            border: 2px solid #000;
            margin: 6px 0;
            font-size: 11px;
            font-weight: 900;
        }
        .cashless-tag {
            background: #000;
            color: #fff;
            padding: 4px 10px;
            text-align: center;
            text-transform: uppercase;
        }
        .cashless-note {
            padding: 4px 8px;
            font-style: italic;
            font-weight: 700;
            font-size: 10px;
            display: flex;
            align-items: center;
        }

        /* 7. Info Berat & Batas Kirim */
        .ship-meta {
            font-size: 10px;
            font-weight: 700;
            padding-bottom: 6px;
            border-bottom: 2px solid #000;
            line-height: 1.5;
        }
        .ship-meta strong { font-weight: 900; }

        /* 8. Table Packing List */
        .packing-list {
            margin-top: 6px;
            flex-grow: 1;
        }
        .packing-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .packing-table th {
            text-align: left;
            border-bottom: 2px solid #000;
            padding: 3px 2px;
            font-size: 9px;
            font-weight: 900;
        }
        .packing-table td {
            padding: 4px 2px;
            border-bottom: 1px dashed #ccc;
            vertical-align: top;
        }
        .item-sku { font-family: monospace; font-size: 8px; color: #333; }
        .item-note { font-size: 9px; margin-top: 4px; font-weight: 700; }

        @media print {
            body { padding: 0; background: #fff; }
            .no-print-bar { display: none !important; }
            .shopee-label {
                width: 100mm;
                height: 150mm;
                margin: 0;
                border: 2px solid #000;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (tidak ikut diprint) --}}
    <div class="no-print-bar">
        <div>
            <strong style="font-size: 12px;">Label Resi Shopee Style (A6 Thermal)</strong>
        </div>
        <div style="display: flex; gap: 6px;">
            <button onclick="window.print()" style="background:#EE4D2D; color:#fff; border:none; padding:6px 14px; font-weight:bold; border-radius:4px; cursor:pointer; font-size:11px;">
                🖨️ Cetak A6
            </button>
            <button onclick="window.close()" style="background:#374151; color:#fff; border:none; padding:6px 12px; font-weight:bold; border-radius:4px; cursor:pointer; font-size:11px;">
                Tutup
            </button>
        </div>
    </div>

    @php
        $city = strtoupper($order->shipping_address['city'] ?? 'KOTA SURABAYA');
        $district = strtoupper($order->shipping_address['province'] ?? 'JAWA TIMUR');
        $trackingNo = $order->tracking_number ?: ('0046' . str_pad($order->id, 8, '0', STR_PAD_LEFT));
        $courierName = strtoupper($order->courier ?: 'SICEPAT EKSPRES');
    @endphp

    {{-- Layout Resi Presisi Sesuai Gambar Referensi Shopee --}}
    <div class="shopee-label">

        {{-- 1. Top Header --}}
        <div class="top-header">
            <div class="brand-logo">
                <span>S</span> RECORD <span class="store-tag">OFFICIAL</span>
            </div>
            <div class="service-type">
                REG
            </div>
            <div class="courier-badge">
                {{ $courierName }}
            </div>
        </div>

        {{-- 2. Routing Code & No. Resi --}}
        <div class="resi-box-row">
            <div class="routing-code">
                SUB-KMY-A
            </div>
            <div class="resi-code">
                No. Resi: {{ $trackingNo }}
            </div>
        </div>

        {{-- 3. Barcode --}}
        <div class="barcode-section">
            <div class="barcode-lines"></div>
        </div>

        {{-- 4. Pengirim & Penerima --}}
        <div class="address-box">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 58%;">
                    <div class="address-header">
                        <span class="penerima">Penerima: {{ $order->shipping_address['recipient_name'] ?? ($order->user->name ?? 'Customer') }}</span>
                    </div>
                    <div class="home-badge">HOME</div>
                    <div class="full-address">
                        {{ $order->shipping_address['address_line'] ?? '-' }}, {{ $city }}, {{ $district }} {{ $order->shipping_address['postal_code'] ?? '' }}
                    </div>
                </div>
                <div style="width: 40%; text-align: right;" class="sender-info">
                    <div style="font-weight: 900; font-size: 10px;">Pengirim: RECORD Shoes Official...</div>
                    <div style="font-weight: 700; margin-top: 2px;">{{ $order->shipping_address['phone'] ?? ($order->user->phone ?? '081323065554') }}</div>
                    <div style="font-weight: 700; margin-top: 2px;">KOTA SURABAYA</div>
                </div>
            </div>

            {{-- 5. Highlight Kota & Kecamatan --}}
            <div class="location-highlight">
                <div class="loc-box">{{ $city }}</div>
                <div class="loc-box">{{ $district }}</div>
            </div>
        </div>

        {{-- 6. Cashless Box --}}
        <div class="cashless-row">
            <div class="cashless-tag">
                CASHLESS
            </div>
            <div class="cashless-note">
                Penjual tidak perlu bayar ongkir ke Kurir
            </div>
        </div>

        {{-- 7. Detail Berat & No. Pesanan --}}
        <div class="ship-meta">
            <div>Berat: <strong>{{ ($order->items->sum('quantity') * 400) }} gr</strong></div>
            <div>Batas Kirim: <strong>{{ now()->addDays(2)->format('d-m-Y') }}</strong></div>
            <div>No. Pesanan: <strong>{{ $order->order_number }}</strong></div>
        </div>

        {{-- 8. Table Rincian Produk --}}
        <div class="packing-list">
            <table class="packing-table">
                <thead>
                    <tr>
                        <th style="width: 15px;">#</th>
                        <th>Nama Produk</th>
                        <th>SKU</th>
                        <th>Variasi</th>
                        <th style="width: 25px; text-align: right;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $item->product_name }}</strong></td>
                            <td class="item-sku">REC-{{ str_pad($item->product_id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $item->variant_info ?: '-' }}</td>
                            <td style="text-align: right; font-weight: 900;">{{ $item->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($order->notes)
                <div class="item-note">Pesan: {{ $order->notes }}</div>
            @endif
        </div>

    </div>

</body>
</html>
