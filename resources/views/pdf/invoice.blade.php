@extends('pdf.layout')

@section('title', 'Invoice - ' . $invoice->number)

@section('content')
    <h2 class="document-title">INVOICE</h2>
    
    <table class="no-border" style="margin-bottom: 1cm;">
        <tr>
            <td width="50%">
                <div class="font-bold">DITERBITKAN UNTUK:</div>
                <div>{{ $invoice->client?->company_name ?? $invoice->client?->pic_name ?? '-' }}</div>
                <div>{{ $invoice->client?->address ?? '-' }}</div>
                <div>Telp: {{ $invoice->client?->phone ?? '-' }}</div>
            </td>
            <td width="50%" class="text-right">
                <table class="no-border">
                    <tr>
                        <td class="font-bold">Nomor Invoice</td>
                        <td>: {{ $invoice->number }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">Tanggal</td>
                        <td>: {{ $invoice->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">Jatuh Tempo</td>
                        <td>: {{ $invoice->due_date?->format('d M Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">Status</td>
                        <td class="font-bold" style="color: {{ $invoice->status === 'paid' ? '#10b981' : ($invoice->status === 'overdue' ? '#ef4444' : '#f59e0b') }}">
                            : {{ strtoupper($invoice->status) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="bordered">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Deskripsi Layanan</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="20%" class="text-right">Harga Satuan</th>
                <th width="20%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $item->name }}</div>
                        @if($item->description)
                            <div style="font-size: 9pt; color: #57534e;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Subtotal</th>
                <td class="text-right">{{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
                <th colspan="4" class="text-right">Diskon</th>
                <td class="text-right">- {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($invoice->tax_amount > 0)
            <tr>
                <th colspan="4" class="text-right">Pajak ({{ $invoice->tax_rate }}%)</th>
                <td class="text-right">{{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <th colspan="4" class="text-right">TOTAL</th>
                <th class="text-right" style="background-color: #fef3c7; font-size: 12pt;">
                    {{ $invoice->currency }} {{ number_format($invoice->total, 0, ',', '.') }}
                </th>
            </tr>
            @if($invoice->paid_amount > 0)
            <tr>
                <th colspan="4" class="text-right">Telah Dibayar</th>
                <td class="text-right" style="color: #10b981;">{{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th colspan="4" class="text-right">Sisa Tagihan</th>
                <th class="text-right">{{ number_format($invoice->total - $invoice->paid_amount, 0, ',', '.') }}</th>
            </tr>
            @endif
        </tfoot>
    </table>

    <div style="margin-top: 1cm;">
        <div class="font-bold">Informasi Pembayaran:</div>
        @if($invoice->pakasir_payment_url && $invoice->status !== 'paid')
            <div style="margin-top: 5px; padding: 10px; border: 1px dashed #d6d3d1; background-color: #fffbeb;">
                Bayar lebih mudah via Online (QRIS, VA, CC):<br>
                <a href="{{ $invoice->pakasir_payment_url }}" style="color: #d97706; font-weight: bold; text-decoration: none;">
                    {{ $invoice->pakasir_payment_url }}
                </a>
            </div>
        @endif
        
        @if($invoice->notes)
            <div style="margin-top: 10px;">
                <div class="font-bold">Catatan:</div>
                <div style="font-size: 9pt; white-space: pre-wrap;">{{ $invoice->notes }}</div>
            </div>
        @endif
    </div>

    <div class="signature-section">
        <div class="signature-box right">
            <p class="text-center">{{ $workspace->name }}</p>
            <div class="signature-space"></div>
            <p class="text-center">______________________________</p>
            <p class="text-center font-bold">Finance Department</p>
        </div>
        <div style="clear: both;"></div>
    </div>
@endsection
