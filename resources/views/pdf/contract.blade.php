@extends('pdf.layout')

@section('title', 'Perjanjian Kerja Sama - ' . $contract->title)

@section('content')
    <h2 class="document-title">PERJANJIAN KERJA SAMA PROYEK</h2>
    <h3 class="document-subtitle">(PROJECT AGREEMENT)</h3>

    <table class="no-border" style="width: 50%; margin: 0 auto 1cm auto;">
        <tr>
            <td class="font-bold">No. Kontrak</td>
            <td>: {{ $contract->number ?? 'MF-' . date('Ymd') . '-' . substr($contract->id, 0, 4) }}</td>
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

    <h2>BAB I — DATA PARA PIHAK</h2>
    
    <h3>Pasal 1.1 — Pihak Pertama (Agensi)</h3>
    <table class="bordered">
        <tr>
            <th width="30%">Nama Agensi</th>
            <td>{{ $workspace->name }}</td>
        </tr>
        <tr>
            <th>Jenis Usaha</th>
            <td>Digital Agency & Technology Solutions</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $workspace->smtp_username ?? 'hello@' . $workspace->slug . '.com' }}</td>
        </tr>
    </table>

    <h3>Pasal 1.2 — Pihak Kedua (Klien)</h3>
    <table class="bordered">
        <tr>
            <th width="30%">Nama Klien / PIC</th>
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
    </table>

    <p class="prose">
        Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama disebut <strong>PARA PIHAK</strong>, dan masing-masing disebut <strong>PIHAK</strong>, sepakat untuk mengadakan Perjanjian Kerja Sama Proyek dengan syarat dan ketentuan sebagai berikut.
    </p>

    <div style="page-break-after: always;"></div>

    <h2>BAB II — DASAR HUKUM & DEFINISI</h2>
    <p class="prose">
        Perjanjian ini dibuat berdasarkan dan tunduk pada peraturan perundang-undangan yang berlaku di Republik Indonesia, antara lain Kitab Undang-Undang Hukum Perdata, UU ITE, dan UU Perlindungan Data Pribadi.
    </p>

    <h2>BAB III — INFORMASI PROYEK</h2>
    <table class="bordered">
        <tr>
            <th width="30%">Nama Proyek</th>
            <td>{{ $contract->project?->name ?? $contract->title }}</td>
        </tr>
        <tr>
            <th>Tanggal Mulai</th>
            <td>{{ $contract->start_date?->format('d M Y') ?? '-' }}</td>
        </tr>
        <tr>
            <th>Estimasi Selesai</th>
            <td>{{ $contract->end_date?->format('d M Y') ?? '-' }}</td>
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
                    <td>{{ $contract->title }} (Sesuai kesepakatan)</td>
                    <td class="text-right">{{ number_format($contract->value, 0, ',', '.') }}</td>
                </tr>
                @php $total = $contract->value; @endphp
            @endif
        </tbody>
        <tfoot>
            @if($contract->quotation)
                <tr>
                    <th colspan="2" class="text-right">Subtotal</th>
                    <td class="text-right">{{ number_format($contract->quotation->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($contract->quotation->discount_amount > 0)
                <tr>
                    <th colspan="2" class="text-right">Diskon</th>
                    <td class="text-right">- {{ number_format($contract->quotation->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($contract->quotation->tax_amount > 0)
                <tr>
                    <th colspan="2" class="text-right">Pajak ({{ $contract->quotation->tax_rate }}%)</th>
                    <td class="text-right">{{ number_format($contract->quotation->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
            @endif
            <tr>
                <th colspan="2" class="text-right">TOTAL KESELURUHAN</th>
                <th class="text-right" style="background-color: #fef3c7;">Rp {{ number_format($total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

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
            <p>Klien</p>
            <div class="signature-space"></div>
            <p>( ______________________________ )</p>
            <p>Tgl: __________________</p>
        </div>
        <div style="clear: both;"></div>
        <p class="stamp-duty">* Materai Rp 10.000</p>
    </div>
@endsection
