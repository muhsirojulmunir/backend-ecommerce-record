<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resi Massal Shopee Style ({{ count($orders) }} Pesanan)</title>
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
            height: 150mm;
            margin: 0 auto 20px;
            background: #fff;
            border: 2px solid #000;
            padding: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: Arial, sans-serif;
            page-break-after: always;
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
            color: #EE4D2D;
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
        /* Barcode sungguhan digambar sebagai SVG oleh App\Support\Barcode\Code128.
           Garis hias yang dulu ada di sini tidak menyimpan informasi apa pun,
           jadi pemindai kurir tidak pernah bisa membacanya. */
        .barcode-section svg {
            width: 95%;
            height: 56px;
            display: block;
            margin: 0 auto;
        }
        .barcode-teks {
            font-family: monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }
        .barcode-kosong {
            border: 2px dashed #b91c1c;
            color: #b91c1c;
            padding: 8px 6px;
            font-size: 9px;
            line-height: 1.35;
        }
        .barcode-kosong strong { display: block; font-size: 11px; margin-bottom: 2px; }

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

    {{-- Top Action Bar --}}
    <div class="no-print-bar">
        <div>
            <strong style="font-size: 12px;">Cetak Massal Resi Shopee Style ({{ count($orders) }} Resi)</strong>
        </div>
        <div style="display: flex; gap: 6px;">
            <button onclick="window.print()" style="background:#EE4D2D; color:#fff; border:none; padding:6px 14px; font-weight:bold; border-radius:4px; cursor:pointer; font-size:11px;">
                🖨️ Cetak Semua
            </button>
            <button onclick="window.close()" style="background:#374151; color:#fff; border:none; padding:6px 12px; font-weight:bold; border-radius:4px; cursor:pointer; font-size:11px;">
                Tutup
            </button>
        </div>
    </div>

    @foreach($orders as $order)
        @php
            /*
             * Semua nilai di label ini berasal dari data pesanan yang
             * sesungguhnya. Versi sebelumnya mengarang beberapa di antaranya —
             * nomor resi cadangan "0046...", kode sortir "SUB-KMY-A" yang
             * dipatok mati, kurir bawaan "SICEPAT EKSPRES", dan tanggal batas
             * kirim yang dihitung dari hari ini. Label yang isinya karangan
             * membuat paket salah sortir atau ditolak petugas.
             */
            $alamat   = (array) $order->shipping_address;
            $city     = strtoupper($alamat['city'] ?? '-');
            $district = strtoupper($alamat['province'] ?? '-');

            // Kosong berarti pengirimannya BELUM dipesan ke kurir. Label tidak
            // boleh menutupi keadaan itu dengan nomor buatan sendiri.
            $trackingNo  = trim((string) $order->tracking_number);
            $adaResi     = $trackingNo !== '' && ! preg_match('/^(REC-|REC0|RTR-)/i', $trackingNo);

            $courierName = strtoupper($order->courier ?: '-');

            // Jenis layanan dari kode resmi Biteship, misalnya "jne:reg".
            [$kodeKurir, $jenisLayanan] = array_pad(explode(':', (string) $order->courier_code, 2), 2, null);
            $layanan = strtoupper($jenisLayanan ?: 'REG');

            // Kode sortir dari Biteship. Tidak semua kurir memakainya.
            $kodeSortir = trim((string) $order->shipping_routing_code);

            // Berat dibaca dari sumber yang sama dengan yang dilaporkan ke
            // Biteship, sehingga label dan tagihan kurir tidak pernah berbeda.
            $beratSatuan = (int) config('pengiriman.berat_kirim_gram', 500);
            $beratTotal  = $order->items->sum('quantity') * $beratSatuan;

            $barcode = app(\App\Support\Barcode\Code128::class);
            $bisaBarcode = $adaResi && $barcode->bisa($trackingNo);

            $diasuransikan = (float) ($order->shipping_insurance_fee ?? 0) > 0;
        @endphp

        {{-- Layout Resi Presisi Sesuai Gambar Referensi Shopee Per Pesanan --}}
        <div class="shopee-label">

            {{-- 1. Top Header --}}
            <div class="top-header">
                <div class="brand-logo">
                    RECORD <span class="store-tag">OFFICIAL</span>
                </div>
                <div class="service-type">
                    {{ $layanan }}
                </div>
                <div class="courier-badge">
                    {{ $courierName }}
                </div>
            </div>

            {{-- 2. Routing Code & No. Resi --}}
            <div class="resi-box-row">
                <div class="routing-code">
                    {{-- Kode sortir dari Biteship. Tidak semua kurir memakainya;
                         kalau kosong, ditulis apa adanya, bukan dikarang. --}}
                    {{ $kodeSortir !== '' ? $kodeSortir : '—' }}
                </div>
                <div class="resi-code">
                    No. Resi: {{ $adaResi ? $trackingNo : 'BELUM ADA' }}
                </div>
            </div>

            {{-- 3. Barcode — Code 128 sungguhan, memuat nomor resi asli --}}
            <div class="barcode-section">
                @if($bisaBarcode)
                    {!! $barcode->svg($trackingNo, 56, 2.0) !!}
                    <div class="barcode-teks">{{ $trackingNo }}</div>
                @else
                    <div class="barcode-kosong">
                        <strong>PENGIRIMAN BELUM DIPESAN</strong>
                        <span>Tekan "Atur Pengiriman" dulu agar nomor resi resmi terbit.
                              Jangan tempelkan label ini ke paket.</span>
                    </div>
                @endif
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
                            {{ $alamat['address_line'] ?? '-' }}, {{ $city }}, {{ $district }} {{ $alamat['postal_code'] ?? '' }}
                        </div>
                        <div style="font-weight: 700; margin-top: 2px; font-size: 10px;">
                            {{ $alamat['phone'] ?? ($order->user->phone ?? '-') }}
                        </div>
                    </div>
                    {{-- Blok pengirim memakai identitas TOKO.
                         Sebelumnya nomor telepon penerima yang ditampilkan di
                         sini — kurir yang perlu menghubungi pengirim justru
                         menelepon pembeli. --}}
                    <div style="width: 40%; text-align: right;" class="sender-info">
                        <div style="font-weight: 900; font-size: 10px;">
                            Pengirim: {{ env('STORE_LABEL', 'RECORD Official Store') }}
                        </div>
                        <div style="font-weight: 700; margin-top: 2px;">
                            {{ env('STORE_PHONE', '081323065554') }}
                        </div>
                        <div style="font-weight: 700; margin-top: 2px;">
                            {{ strtoupper(config('pengiriman.toko.kota', 'Surabaya')) }}
                        </div>
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
                {{-- Keadaan pembayaran yang sebenarnya. "CASHLESS" sebelumnya
                     dicetak untuk semua pesanan tanpa memeriksa apa pun. --}}
                <div class="cashless-tag">
                    {{ $order->payment_status === 'paid' ? 'NON-COD / LUNAS' : 'BELUM LUNAS' }}
                </div>
                <div class="cashless-note">
                    @if($diasuransikan)
                        Paket diasuransikan &middot; jangan diserahkan tanpa tanda terima
                    @else
                        Ongkir sudah dibayar di muka, kurir tidak menagih ke penerima
                    @endif
                </div>
            </div>

            {{-- 7. Detail Berat & No. Pesanan --}}
            <div class="ship-meta">
                <div>Berat: <strong>{{ number_format($beratTotal, 0, ',', '.') }} gr</strong></div>
                <div>Kurir: <strong>{{ $courierName }}</strong></div>
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
                                {{-- SKU varian yang sesungguhnya, bukan nomor
                                     karangan dari id produk. Inilah yang dipakai
                                     mencari barang di rak. --}}
                                <td class="item-sku">{{ $item->productVariant?->sku ?: '—' }}</td>
                                <td>{{ $item->variant_info ?: '-' }}</td>
                                <td style="text-align: right; font-weight: 900;">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endforeach

</body>
</html>
