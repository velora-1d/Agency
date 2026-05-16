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
@endsection

