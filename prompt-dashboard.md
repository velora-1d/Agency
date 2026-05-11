# PROMPT DASHBOARD — VELORA X MAVEN
> Copy paste ke Codex/Gemini untuk build dashboard sesuai PRD

---

## PROMPT UTAMA

```
Buat Dashboard Filament v5 untuk aplikasi "Velora x Maven" 
Agency Management System.

File: app/Filament/Pages/Dashboard.php
Dan semua widget di: app/Filament/Widgets/

---

## LAYOUT DASHBOARD

Layout: Grid 12 kolom, responsive
Padding: konsisten kiri-kanan, tidak ada double scroll
Semua widget harus tampil tanpa perlu scroll horizontal

---

## SECTION 1 — KPI CARDS (Top Row)
Layout: 4 kolom per baris, 2 baris = 8 cards total
Widget: StatsOverviewWidget

8 KPI Cards dengan nama yang pas (tidak terlalu pendek/panjang):

| Nama Card | Data | Icon |
|---|---|---|
| Revenue Bulan Ini | Sum invoice paid bulan ini | heroicon-o-banknotes |
| Klien Aktif | Count clients status=active | heroicon-o-building-office-2 |
| Proyek Berjalan | Count projects status=active | heroicon-o-briefcase |
| Tugas Tertunda | Count tasks status=pending/in_progress | heroicon-o-clock |
| Invoice Belum Bayar | Count invoices status=unpaid | heroicon-o-document-text |
| Automation Berjalan | Count automation runs bulan ini | heroicon-o-bolt |
| Produktivitas Tim | % tasks done / total tasks bulan ini | heroicon-o-chart-bar |
| Leads Masuk | Count leads bulan ini | heroicon-o-funnel |

Setiap card wajib ada:
- Nilai utama (angka/nominal)
- Trend indicator: % naik/turun vs bulan lalu
- Warna trend: hijau=naik, merah=turun
- Icon yang sesuai
- Description singkat di bawah angka

---

## SECTION 2 — CHARTS (Row 2)
Layout: 2 kolom (masing-masing 6 kolom grid)

### Chart 1: Revenue (Kiri)
- Nama widget: RevenueChartWidget
- Tipe: Line chart
- Data: revenue per hari/minggu/bulan
- Toggle filter: 7 Hari / 30 Hari / 3 Bulan / 1 Tahun
- Warna: primary brand color
- Label Y: format Rupiah (Rp)

### Chart 2: Konversi Leads (Kanan)
- Nama widget: LeadsConversionWidget  
- Tipe: Bar chart
- Data: count leads per stage (New, Contacted, Proposal, Negotiation, Won, Lost)
- Toggle filter: 7 Hari / 30 Hari / 3 Bulan
- Warna: gradient per stage

---

## SECTION 3 — CHARTS ROW 2
Layout: 2 kolom

### Chart 3: Progress Proyek (Kiri)
- Nama widget: ProjectProgressWidget
- Tipe: Bar chart horizontal
- Data: top 5 project aktif + progress %
- Warna: berdasarkan % (merah<30%, kuning<70%, hijau>=70%)

### Chart 4: Pertumbuhan Bulanan (Kanan)
- Nama widget: MonthlyGrowthWidget
- Tipe: Area chart
- Data: revenue + leads + projects per bulan (12 bulan terakhir)
- Multi-line: 3 garis berbeda warna
- Toggle: Revenue / Leads / Proyek

---

## SECTION 4 — QUICK ACTIONS + CATEGORY SUMMARY
Layout: 2 kolom

### Quick Actions (Kiri) — 5 tombol
- Nama widget: QuickActionsWidget
- Tombol aksi (nama pas, tidak terlalu panjang):
  1. **Tambah Klien** → redirect ke /clients/create
  2. **Buat Proyek** → redirect ke /projects/create  
  3. **Buat Invoice** → redirect ke /invoices/create
  4. **Jalankan Otomasi** → open modal pilih automation
  5. **Broadcast WA** → open modal kirim WA broadcast
- Style: grid 2x3, tombol dengan icon + label
- Warna: primary untuk utama, secondary untuk lainnya

### Category Summary (Kanan) — 4 kategori
- Nama widget: CategorySummaryWidget
- Layout: 4 mini card dalam 1 widget

| Kategori | Data yang Ditampilkan |
|---|---|
| CRM | Total Leads • Hot Leads |
| Proyek | Sedang Berjalan • Tugas Terlambat |
| Keuangan | Pemasukan Bulan Ini • Belum Dibayar |
| Marketing | Campaign Aktif • Performa Terbaik |

---

## SECTION 5 — RECENT ACTIVITY
Layout: Full width (12 kolom)
- Nama widget: RecentActivityWidget
- Tampilkan 10 aktivitas terbaru
- Per item: icon modul, deskripsi aksi, nama user, waktu (relative: "2 jam lalu")
- Tipe aktivitas yang ditampilkan:
  - 📁 Proyek diperbarui
  - 💰 Pembayaran masuk
  - ✅ Tugas selesai
  - 👤 Klien baru
  - ⚡ Otomasi berjalan
- Link "Lihat Semua" → ke halaman Activity Feed
- Realtime update via polling 30 detik

---

## SECTION 6 — BOTTOM ROW
Layout: 3 kolom

### Jadwal Mendatang (Kiri)
- Nama widget: UpcomingMeetingsWidget
- Tampilkan 5 meeting terdekat
- Per item: nama meeting, tanggal, jam, peserta
- Badge warna: hari ini = hijau, besok = kuning, lainnya = abu
- Link ke Calendar

### Kalender Mini (Tengah)
- Nama widget: MiniCalendarWidget
- Tampilkan bulan berjalan
- Dot indicator pada tanggal yang ada event
- Klik tanggal → popup list event di tanggal itu
- Highlight: hari ini

### File Terbaru (Kanan)
- Nama widget: RecentFilesWidget
- Tampilkan 5 file terbaru diupload
- Per item: nama file, tipe (icon), ukuran, project terkait, waktu
- Icon per tipe: PDF, image, video, doc, zip
- Link ke File Manager

---

## DASHBOARD PER ROLE

Semua widget di atas = tampilan Owner/Admin (full access)

Sesuaikan tampilan berdasarkan role:

### Project Manager
Sembunyikan: Revenue Card, Invoice Card, Finance Charts, Keuangan Summary
Tampilkan: Proyek Card, Tugas Card, Tim Card, Project Progress Chart, Jadwal

### Marketing
Sembunyikan: Revenue Card, Invoice Card, Finance Charts, Keuangan Summary
Tampilkan: Leads Card, Konversi Leads Chart, Marketing Summary, Campaign

### Finance
Sembunyikan: Proyek Card, Tugas Card, Tim Card, Project Progress
Tampilkan: Revenue Card, Invoice Card, Finance Charts, Keuangan Summary

Implementasi via:
public static function canView(): bool
{
    return auth()->user()->hasRole(['owner', 'admin']);
}

---

## ATURAN UI WAJIB

1. Padding kiri-kanan semua widget HARUS sama rata
2. Tidak ada scrollbar di dalam widget
3. Tidak ada horizontal scroll di halaman dashboard
4. Gap antar widget: 1.5rem konsisten
5. Chart height: 300px untuk semua chart
6. Card KPI: height sama semua (tidak ada yang lebih tinggi)
7. Responsive: di mobile stack jadi 1 kolom
8. Loading state: skeleton loader saat data loading
9. Empty state: tampilkan pesan jika belum ada data
10. Semua angka currency: format Rupiah (Rp 1.500.000)
11. Semua angka desimal: 2 digit (contoh: 85.50%)
12. Waktu: format Indonesia (10 Mei 2026, 18:30 WIB)

---

## NAMA WIDGET FILES YANG HARUS DIBUAT

app/Filament/Widgets/
├── StatsOverviewWidget.php         ← 8 KPI cards
├── RevenueChartWidget.php          ← Line chart revenue
├── LeadsConversionWidget.php       ← Bar chart leads
├── ProjectProgressWidget.php       ← Bar chart projects
├── MonthlyGrowthWidget.php         ← Area chart growth
├── QuickActionsWidget.php          ← 5 quick action buttons
├── CategorySummaryWidget.php       ← 4 kategori summary
├── RecentActivityWidget.php        ← Activity feed
├── UpcomingMeetingsWidget.php      ← Jadwal meeting
├── MiniCalendarWidget.php          ← Kalender mini
└── RecentFilesWidget.php           ← File terbaru

---

## REGISTRASI DI DASHBOARD PAGE

```php
// app/Filament/Pages/Dashboard.php

protected function getHeaderWidgets(): array
{
    return [
        StatsOverviewWidget::class,
    ];
}

public function getWidgets(): array
{
    return [
        RevenueChartWidget::class,
        LeadsConversionWidget::class,
        ProjectProgressWidget::class,
        MonthlyGrowthWidget::class,
        QuickActionsWidget::class,
        CategorySummaryWidget::class,
        RecentActivityWidget::class,
        UpcomingMeetingsWidget::class,
        MiniCalendarWidget::class,
        RecentFilesWidget::class,
    ];
}

public function getColumns(): int | array
{
    return [
        'sm' => 1,
        'md' => 2,
        'lg' => 12,
    ];
}
```
```

---

## CHECKLIST SETELAH SELESAI

- [ ] 8 KPI cards tampil dengan trend indicator
- [ ] 4 charts tampil dengan toggle filter
- [ ] Quick Actions 5 tombol berfungsi
- [ ] Category Summary 4 kategori tampil
- [ ] Recent Activity realtime update
- [ ] Upcoming Meetings tampil
- [ ] Mini Calendar tampil dengan dot indicator
- [ ] Recent Files tampil
- [ ] Padding kiri-kanan rata semua widget
- [ ] Tidak ada scrollbar ganda
- [ ] Tidak ada horizontal scroll
- [ ] Dashboard per role berfungsi
- [ ] Format Rupiah benar
- [ ] Format tanggal Indonesia
- [ ] Loading skeleton ada
- [ ] Empty state ada
