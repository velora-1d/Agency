@extends('pdf.layout')

@section('title', 'Perjanjian Kerja Sama - ' . $contract->title)

@section('content')
    <h2 class="document-title">PERJANJIAN KERJA SAMA PROYEK</h2>
    <h3 class="document-subtitle">(PROJECT AGREEMENT)</h3>

    <table class="no-border" style="width: 70%; margin: 0 auto 1cm auto;">
        <tr>
            <td class="font-bold" width="30%">No. Kontrak</td>
            <td>: {{ $contract->number ?? 'MF-' . date('Y') . '-' . strtoupper(substr($contract->id, 0, 6)) }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tanggal</td>
            <td>: {{ now()->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="font-bold">Nama Proyek</td>
            <td>: {{ $contract->project?->name ?? $contract->title }}</td>
        </tr>
    </table>

    <p class="text-center" style="font-style: italic; font-size: 9pt; margin-bottom: 1cm;">
        Dokumen ini merupakan perjanjian yang sah dan mengikat secara hukum antara {{ $workspace->name }} dan Klien yang tertera di bawah ini.
    </p>

    <hr style="border: 1px solid #1c1917; margin-bottom: 1cm;">

    <h2>BAB I — DATA PARA PIHAK</h2>
    
    <h3>Pasal 1.1 — Pihak Pertama (Agensi)</h3>
    <table class="bordered">
        <tr>
            <th width="35%">Nama Agensi</th>
            <td>{{ $workspace->name }}</td>
        </tr>
        <tr>
            <th>Jenis Usaha</th>
            <td>Digital Agency & Technology Solutions</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>{{ $workspace->address ?? 'Jakarta, Indonesia' }}</td>
        </tr>
        <tr>
            <th>Nomor Telepon</th>
            <td>{{ $workspace->wa_phone_number ?? '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $workspace->smtp_username ?? 'hello@' . $workspace->slug . '.com' }}</td>
        </tr>
        <tr>
            <th>PIC / Penanggung Jawab</th>
            <td>{{ $contract->creator?->name ?? 'Admin ' . $workspace->name }}</td>
        </tr>
        <tr>
            <th>Jabatan PIC</th>
            <td>Project Manager</td>
        </tr>
    </table>

    <h3>Pasal 1.2 — Pihak Kedua (Klien)</h3>
    <table class="bordered">
        <tr>
            <th width="35%">Nama Klien / PIC</th>
            <td>{{ $contract->client?->pic_name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nama Perusahaan</th>
            <td>{{ $contract->client?->company_name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Alamat</th>
            <td>{{ $contract->client?->address ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nomor Telepon</th>
            <td>{{ $contract->client?->phone ?? '-' }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $contract->client?->email ?? '-' }}</td>
        </tr>
        <tr>
            <th>Jabatan</th>
            <td>Owner / Manager</td>
        </tr>
    </table>

    <p class="prose">
        Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama disebut <strong>PARA PIHAK</strong>, dan masing-masing disebut <strong>PIHAK</strong>, sepakat untuk mengadakan Perjanjian Kerja Sama Proyek dengan syarat dan ketentuan sebagai berikut.
    </p>

    <div style="page-break-after: always;"></div>

    <h2>BAB II — DASAR HUKUM & DEFINISI</h2>
    <h3>Pasal 2.1 — Dasar Hukum</h3>
    <p class="prose">
        Perjanjian ini dibuat berdasarkan dan tunduk pada peraturan perundang-undangan yang berlaku di Republik Indonesia, antara lain:
    </p>
    <ul style="font-size: 10pt; line-height: 1.6;">
        <li>Kitab Undang-Undang Hukum Perdata (KUH Perdata) Pasal 1313 tentang Perjanjian</li>
        <li>Undang-Undang No. 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik (UU ITE)</li>
        <li>Undang-Undang No. 28 Tahun 2014 tentang Hak Cipta</li>
        <li>Undang-Undang No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP)</li>
    </ul>

    <h3>Pasal 2.2 — Definisi</h3>
    <ul style="font-size: 10pt; line-height: 1.6;">
        <li><strong>"Proyek"</strong> berarti pekerjaan yang disepakati sebagaimana tercantum dalam Lampiran A perjanjian ini.</li>
        <li><strong>"Scope of Work"</strong> berarti daftar fitur, menu, dan deliverable yang termasuk dalam Proyek.</li>
        <li><strong>"Change Request"</strong> berarti permintaan perubahan atau penambahan di luar Scope of Work.</li>
        <li><strong>"Deliverable"</strong> berarti hasil pekerjaan yang wajib diserahkan oleh Pihak Pertama kepada Pihak Kedua.</li>
        <li><strong>"Source Code"</strong> berarti kode sumber dari hasil pengerjaan Proyek.</li>
    </ul>

    <h2>BAB III — INFORMASI PROYEK</h2>
    <h3>Pasal 3.1 — Detail Proyek</h3>
    <table class="bordered">
        <tr>
            <th width="35%">Nama Proyek</th>
            <td>{{ $contract->project?->name ?? $contract->title }}</td>
        </tr>
        <tr>
            <th>Jenis Proyek</th>
            <td>{{ $contract->project?->category ?? 'Software Development' }}</td>
        </tr>
        <tr>
            <th>Tanggal Mulai</th>
            <td>{{ $contract->start_date?->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <th>Estimasi Selesai</th>
            <td>{{ $contract->end_date?->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <th>PIC Pihak Pertama</th>
            <td>{{ $contract->creator?->name ?? 'Admin ' . $workspace->name }}</td>
        </tr>
        <tr>
            <th>PIC Pihak Kedua</th>
            <td>{{ $contract->client?->pic_name ?? '-' }}</td>
        </tr>
    </table>

    <h2>BAB IV — BIAYA & PEMBAYARAN</h2>
    <h3>Pasal 4.1 — Rincian Biaya</h3>
    <table class="bordered">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Item Deskripsi</th>
                <th width="25%" class="text-right">Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @if($contract->quotation && $contract->quotation->items->count() > 0)
                @foreach($contract->quotation->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-bold">{{ $item->name }}</div>
                            <div style="font-size: 9pt; color: #57534e;">{{ $item->description }}</div>
                        </td>
                        <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @php $total = $contract->quotation->total; @endphp
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td>{{ $contract->title }}</td>
                    <td class="text-right">{{ number_format($contract->value, 0, ',', '.') }}</td>
                </tr>
                @php $total = $contract->value; @endphp
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">TOTAL KESELURUHAN</th>
                <th class="text-right" style="background-color: #fef3c7;">Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-after: always;"></div>

    <h2>BAB V — PELAKSANAAN & TIMELINE</h2>
    <p class="prose">
        Pihak Pertama berkewajiban mengerjakan Proyek sesuai Scope of Work dan Pihak Kedua berkewajiban memberikan feedback serta melakukan pembayaran sesuai termin yang disepakati.
    </p>

    <h2>BAB VI — REVISI & CHANGE REQUEST</h2>
    <p class="prose">
        Revisi diberikan maksimal 5 kali dalam lingkup Scope of Work. Pekerjaan di luar itu dikategorikan sebagai Change Request.
    </p>

    <h2>BAB VII — HAK KEPEMILIKAN & SERAH TERIMA</h2>
    <p class="prose">
        Hak kepemilikan penuh atas Source Code berpindah ke Pihak Kedua setelah pelunasan 100% diterima oleh Pihak Pertama.
    </p>

    <div class="signature-section">
        <div class="signature-box">
            <p class="font-bold">PIHAK PERTAMA</p>
            <p>{{ $workspace->name }}</p>
            <div class="signature-space"></div>
            <p>( ______________________________ )</p>
            <p>Tgl: __________________</p>
        </div>
        <div class="signature-box right">
            <p class="font-bold">PIHAK KEDUA</p>
            <p>{{ $contract->client?->pic_name ?? 'Klien' }}</p>
            <div class="signature-space"></div>
            <p>( ______________________________ )</p>
            <p>Tgl: __________________</p>
        </div>
        <div style="clear: both;"></div>
        <p class="stamp-duty">* Materai Rp 10.000</p>
    </div>

    <div style="page-break-after: always;"></div>

    <h2>BAB V — PELAKSANAAN &amp; TIMELINE</h2>
    <h3>Pasal 5.1 — Kewajiban Pihak Pertama ({{ $workspace->name }})</h3>
    <ul style="font-size: 10pt; line-height: 1.8;">
        <li>Mengerjakan Proyek sesuai Scope of Work yang tercantum dalam Lampiran A.</li>
        <li>Memberikan laporan progres secara berkala kepada Pihak Kedua.</li>
        <li>Menyelesaikan Proyek sesuai estimasi timeline yang disepakati.</li>
        <li>Memberikan pemberitahuan tertulis apabila terjadi keterlambatan.</li>
        <li>Menjaga kerahasiaan data dan informasi Pihak Kedua.</li>
        <li>Menyerahkan Source Code setelah pelunasan 100% diterima.</li>
    </ul>
    <h3>Pasal 5.2 — Kewajiban Pihak Kedua (Klien)</h3>
    <ul style="font-size: 10pt; line-height: 1.8;">
        <li>Menyediakan konten, aset, dan informasi yang dibutuhkan dalam waktu yang wajar.</li>
        <li>Memberikan feedback atau persetujuan atas hasil pekerjaan maksimal dalam 3 hari kerja.</li>
        <li>Melakukan pembayaran sesuai jadwal yang telah disepakati.</li>
        <li>Tidak mempekerjakan pihak lain untuk mengerjakan proyek yang sama selama perjanjian berlaku.</li>
        <li>Menjaga kerahasiaan proses, metode, dan harga yang telah disepakati dengan Pihak Pertama.</li>
    </ul>
    <h3>Pasal 5.3 — Keterlambatan &amp; Force Majeure</h3>
    <p class="prose">Keterlambatan akibat Force Majeure atau keterlambatan penyediaan aset oleh Pihak Kedua tidak termasuk dalam kewajiban Pihak Pertama. Pihak yang mengalami kondisi Force Majeure wajib memberitahu dalam 3x24 jam.</p>

    <h2>BAB VI — REVISI &amp; CHANGE REQUEST</h2>
    <h3>Pasal 6.1 — Ketentuan Revisi</h3>
    <table class="bordered">
        <tr><th width="40%">Ketentuan Revisi</th><th>Keterangan</th></tr>
        <tr><td>Jumlah Revisi</td><td>Maksimal 5 (lima) kali revisi</td></tr>
        <tr><td>Waktu Pengajuan Revisi</td><td>Maksimal 7 hari setelah Deliverable diterima</td></tr>
        <tr><td>Cakupan Revisi</td><td>Perubahan dalam Scope of Work yang telah disepakati</td></tr>
        <tr><td>Di Luar Cakupan</td><td>Dikategorikan sebagai Change Request — dikenakan biaya tambahan</td></tr>
    </table>
    <h3>Pasal 6.2 — Change Request</h3>
    <p class="prose">Permintaan perubahan di luar Scope of Work dikategorikan sebagai Change Request. Pihak Pertama memberikan estimasi biaya dalam 3 hari kerja. Pengerjaan dimulai setelah pembayaran DP Change Request diterima.</p>

    <h2>BAB VII — HAK KEPEMILIKAN &amp; SERAH TERIMA</h2>
    <h3>Pasal 7.1 — Hak Kepemilikan</h3>
    <ul style="font-size: 10pt; line-height: 1.8;">
        <li>Selama proses pengerjaan, Source Code dan semua Deliverable merupakan aset Pihak Pertama.</li>
        <li>Hak kepemilikan penuh atas Source Code berpindah ke Pihak Kedua setelah pelunasan 100% diterima.</li>
        <li>Pihak Pertama berhak menampilkan Proyek sebagai portofolio kecuali ada perjanjian kerahasiaan khusus.</li>
    </ul>
    <h3>Pasal 7.2 — Serah Terima Proyek</h3>
    <ul style="font-size: 10pt; line-height: 1.8;">
        <li>Serah terima Proyek dilakukan setelah Deliverable selesai dan disetujui Pihak Kedua.</li>
        <li>Source Code diserahkan dalam bentuk repository atau file ZIP setelah pelunasan diterima.</li>
        <li>Pihak Kedua wajib menandatangani Berita Acara Serah Terima (BAST) sebagai tanda penerimaan resmi.</li>
        <li>Setelah BAST ditandatangani, Proyek dinyatakan selesai dan masa garansi bug mulai berjalan.</li>
    </ul>

    <h2>BAB VIII — GARANSI &amp; MAINTENANCE</h2>
    <h3>Pasal 8.1 — Garansi Bug</h3>
    <table class="bordered">
        <tr><th width="40%">Ketentuan</th><th>Detail</th></tr>
        <tr><td>Masa Garansi</td><td>2 (dua) bulan sejak tanggal BAST ditandatangani</td></tr>
        <tr><td>Yang Ditanggung</td><td>Bug / error yang berasal dari hasil pengerjaan Pihak Pertama</td></tr>
        <tr><td>Yang Tidak Ditanggung</td><td>Error akibat modifikasi oleh Pihak Kedua atau pihak lain</td></tr>
        <tr><td>Biaya Garansi</td><td>Gratis untuk bug dalam cakupan garansi</td></tr>
    </table>
    <h3>Pasal 8.2 — Modifikasi oleh Pihak Kedua</h3>
    <p class="prose">Apabila Pihak Kedua atau pihak lain melakukan modifikasi pada Deliverable, garansi bug otomatis gugur untuk bagian yang dimodifikasi. Biaya perbaikan disepakati secara custom sesuai kompleksitas masalah.</p>

    <h2>BAB IX — KERAHASIAAN (NDA)</h2>
    <h3>Pasal 9.1 — Kewajiban Kerahasiaan</h3>
    <ul style="font-size: 10pt; line-height: 1.8;">
        <li><strong>Pihak Pertama:</strong> menjaga kerahasiaan data bisnis, data pelanggan, strategi, dan informasi teknis Pihak Kedua.</li>
        <li><strong>Pihak Kedua:</strong> menjaga kerahasiaan metode kerja, harga, proses internal, dan informasi bisnis Pihak Pertama.</li>
        <li>Para Pihak tidak diperkenankan mengungkapkan informasi rahasia kepada pihak ketiga tanpa persetujuan tertulis.</li>
    </ul>
    <h3>Pasal 9.2 — Perlindungan Data Pribadi</h3>
    <p class="prose">Sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi, data yang diterima hanya digunakan untuk keperluan Proyek. Kewajiban ini tetap berlaku setelah perjanjian berakhir.</p>

    <h2>BAB X — PEMBATALAN KONTRAK</h2>
    <h3>Pasal 10.1 — Pembatalan oleh Pihak Kedua (Klien)</h3>
    <p class="prose">Apabila Pihak Kedua membatalkan kontrak setelah pengerjaan dimulai, Down Payment (DP) dipotong <strong>15% (lima belas persen)</strong> sebagai biaya administrasi dan cancellation fee. Sisa DP dikembalikan dalam 14 hari kerja.</p>
    <h3>Pasal 10.2 — Pembatalan oleh Pihak Pertama</h3>
    <p class="prose">Apabila Pihak Pertama membatalkan kontrak, Down Payment dikembalikan <strong>100% (seratus persen)</strong> dalam 14 hari kerja. Pihak Pertama wajib memberikan pemberitahuan tertulis minimal 7 hari sebelum pembatalan.</p>

    <h2>BAB XI — PENYELESAIAN SENGKETA</h2>
    <h3>Pasal 11.1 — Musyawarah &amp; Mediasi</h3>
    <p class="prose">Para Pihak mengutamakan penyelesaian secara musyawarah mufakat dalam 30 hari kalender. Apabila tidak tercapai kesepakatan, Para Pihak menempuh jalur mediasi dengan biaya ditanggung bersama secara proporsional.</p>
    <h3>Pasal 11.2 — Jalur Hukum</h3>
    <p class="prose">Apabila mediasi tidak berhasil, Para Pihak menyelesaikan perselisihan melalui jalur hukum sesuai ketentuan yang berlaku di Republik Indonesia.</p>

    <div style="page-break-after: always;"></div>

    <h2>LAMPIRAN A — SCOPE OF WORK</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Fitur / Menu</th>
                <th>Deskripsi</th>
                <th width="15%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @if($contract->quotation && $contract->quotation->items->count() > 0)
                @foreach($contract->quotation->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">✅ Include</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold">{{ $contract->title }}</td>
                    <td>Sesuai kesepakatan awal proyek</td>
                    <td class="text-center">✅ Include</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>

    <h2>LAMPIRAN B — RINCIAN BIAYA DETAIL</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Item</th>
                <th>Keterangan</th>
                <th width="25%" class="text-right">Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @if($contract->quotation && $contract->quotation->items->count() > 0)
                @foreach($contract->quotation->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item->name }}</td>
                        <td style="font-size: 9pt; color: #57534e;">{{ $item->description }}</td>
                        <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="3" class="text-right">SUBTOTAL</th>
                    <th class="text-right">Rp {{ number_format($contract->quotation->subtotal ?? $contract->quotation->total, 0, ',', '.') }}</th>
                </tr>
                @if(($contract->quotation->discount ?? 0) > 0)
                    <tr>
                        <td colspan="3" class="text-right">Diskon</td>
                        <td class="text-right">( {{ number_format($contract->quotation->discount, 0, ',', '.') }} )</td>
                    </tr>
                @endif
            @endif
            <tr>
                <th colspan="3" class="text-right">TOTAL KESELURUHAN</th>
                <th class="text-right" style="background-color: #fef3c7;">Rp {{ number_format($contract->value, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

    <h3 style="margin-top: 1cm;">Rekening Pembayaran</h3>
    <table class="bordered" style="width: 60%;">
        <tr><th width="40%">Bank</th><td>{{ data_get($workspace->settings, 'bank_name', '—') }}</td></tr>
        <tr><th>Nama Rekening</th><td>{{ $workspace->name }}</td></tr>
        <tr><th>Nomor Rekening</th><td>{{ data_get($workspace->settings, 'bank_account_number', '—') }}</td></tr>
        <tr><th>Atas Nama</th><td>{{ data_get($workspace->settings, 'bank_account_name', $workspace->name) }}</td></tr>
    </table>
    <p style="font-size: 9pt; font-style: italic; margin-top: 0.5cm;">
        Harap konfirmasi setiap pembayaran kepada PIC Pihak Pertama via WhatsApp atau email dengan menyertakan bukti transfer.
    </p>

    <p style="text-align: center; font-size: 8pt; color: #78716c; margin-top: 1.5cm; border-top: 1px solid #e7e5e4; padding-top: 0.5cm;">
        &copy; {{ now()->year }} {{ $workspace->name }} — Dokumen ini bersifat rahasia dan mengikat secara hukum
    </p>
@endsection

