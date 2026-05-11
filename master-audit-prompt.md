# MASTER AUDIT PROMPT — KANTOR DIGITAL
> Copy paste ke Codex/Gemini/Claude per Resource

---

## CARA PAKAI
1. Ganti `[NamaResource]` dengan nama Resource yang mau diaudit
2. Paste PRD bagian yang relevan di bagian bawah
3. Jalankan satu Resource per prompt — jangan sekaligus
4. AI akan laporkan yang BELUM sesuai saja

---

## TEMPLATE PROMPT

```
Kamu adalah QA Engineer senior untuk project Laravel 13 + Filament v5 
multi-tenant agency management bernama Kantor Digital.
Ada 2 workspace: Velora & Maven — data harus totally isolated.

Audit Resource: [NamaResource]

Laporkan HANYA yang bermasalah atau belum sesuai PRD.
Jangan laporkan yang sudah benar.
Berikan solusi konkret + kode untuk setiap masalah.

---

## 1. HALAMAN

### List Page
- [ ] Halaman tampil tanpa error
- [ ] Kolom tabel sesuai PRD
- [ ] Default sort sudah benar
- [ ] Empty state ada (tidak blank kosong)
- [ ] Pagination berfungsi

### Create Page
- [ ] Semua field form sesuai PRD
- [ ] Field required sudah divalidasi
- [ ] Field optional tidak wajib diisi
- [ ] Dropdown/select load data dengan benar
- [ ] Setelah submit redirect ke halaman yang benar

### Edit Page
- [ ] Data existing ter-load dengan benar
- [ ] Semua field bisa diedit
- [ ] Setelah update data tersimpan benar

### Detail/View Page
- [ ] Semua field tampil
- [ ] Relasi ke modul lain tampil (tabs)
- [ ] Data tidak campur antar tenant

---

## 2. FITUR

### Table Features
- [ ] Search berfungsi (semua kolom yang relevan)
- [ ] Filter by status berfungsi
- [ ] Filter by date range berfungsi
- [ ] Filter by assignee berfungsi
- [ ] Filter by workspace/tenant berfungsi
- [ ] Bulk action ada dan berfungsi
- [ ] Export CSV berfungsi
- [ ] Column toggle berfungsi

### Actions
- [ ] View action ada
- [ ] Edit action ada
- [ ] Delete action ada (dengan konfirmasi)
- [ ] Custom actions sesuai PRD ada dan berfungsi
- [ ] Bulk delete ada

---

## 3. RELASI ANTAR MODUL

### CRM Flow
- [ ] Lead → bisa convert ke Client
- [ ] Lead → bisa buat Quotation langsung
- [ ] Client → linked ke Projects (tab)
- [ ] Client → linked ke Invoices (tab)
- [ ] Client → linked ke Contracts (tab)
- [ ] Client → linked ke Support Tickets (tab)
- [ ] Client → linked ke Activity History (tab)

### Project Flow
- [ ] Project → linked ke Tasks (tab)
- [ ] Project → linked ke Files (tab)
- [ ] Project → linked ke Meetings (tab)
- [ ] Project → linked ke Notes/SOP (tab)
- [ ] Project → linked ke Invoices (tab)
- [ ] Project → progress % auto update dari Tasks
- [ ] Meeting notes → auto create Tasks (action items)

### Finance Flow
- [ ] Quotation → bisa convert ke Invoice + Project sekaligus
- [ ] Invoice → linked ke Transactions
- [ ] Invoice → payment status auto update setelah Pakasir webhook
- [ ] Transaction → update balance Bank Account
- [ ] Project → bisa lihat budget vs actual cost

### Support Flow
- [ ] Support Ticket → bisa submit dari WA atau portal
- [ ] Support Ticket → SLA timer berjalan
- [ ] Support Ticket → assignee bisa diubah

---

## 4. LOGIC & BUSINESS RULES

### Multi-Tenant Isolation
- [ ] Data Volora TIDAK muncul di Maven (dan sebaliknya)
- [ ] Query semua model sudah scope by workspace_id
- [ ] Global scope tenant sudah terpasang di semua Model
- [ ] File/storage juga ter-isolasi per tenant

### Permission & Role
- [ ] Owner/Admin → akses semua modul
- [ ] Project Manager → hanya Projects, Tasks, Team, Files, Meetings, Notes
- [ ] Marketing → hanya Leads, Clients, Marketing, Calendar
- [ ] Finance → hanya Invoices, Transactions, Quotations, Reports
- [ ] Menu sidebar otomatis hide sesuai role
- [ ] Direct URL access dicegah untuk role yang tidak punya akses

### Validasi
- [ ] Form tidak bisa submit kalau field required kosong
- [ ] Email format divalidasi
- [ ] Nomor telepon divalidasi
- [ ] Angka/currency tidak bisa negatif
- [ ] Date end tidak boleh sebelum date start

### Automation
- [ ] Lead baru → trigger WA otomatis via n8n (bisa ON/OFF)
- [ ] Invoice due → kirim reminder otomatis
- [ ] Contract expired → kirim reminder otomatis
- [ ] Support ticket baru → auto-reply via n8n
- [ ] Event/Listener terpasang dengan benar
- [ ] Queue job tidak blocking request

---

## 5. UI/UX

### Layout & Spacing
- [ ] Padding & margin tidak terlalu besar (tidak banyak whitespace)
- [ ] Padding & margin tidak terlalu kecil (tidak cramped)
- [ ] Spacing antar section konsisten di semua halaman
- [ ] Tidak ada double scrollbar di sidebar
- [ ] Tidak ada double scrollbar di konten halaman
- [ ] Sidebar height pas, tidak overflow
- [ ] Konten halaman tidak overflow horizontal
- [ ] Tabel tidak perlu scroll horizontal di layar normal (1366px+)

### Form UI
- [ ] Form tidak terlalu wide (tidak full 100% layar)
- [ ] Form tidak terlalu narrow
- [ ] Label field jelas dan konsisten
- [ ] Error message tampil di bawah field yang salah
- [ ] Loading state ada saat submit

### Responsif
- [ ] Tabel stack dengan benar di mobile
- [ ] Form tidak berantakan di mobile
- [ ] Sidebar collapse di mobile

### Konsistensi
- [ ] Warna status badge konsisten (hijau=active, merah=inactive, dll)
- [ ] Icon konsisten di semua halaman
- [ ] Tombol aksi posisinya konsisten
- [ ] Empty state design konsisten

---

## 6. PERFORMA

- [ ] Tidak ada N+1 query (gunakan eager loading)
- [ ] Index database pada kolom yang sering di-filter
- [ ] Pagination digunakan untuk data besar
- [ ] Tidak ada query berat yang blocking UI
- [ ] Cache digunakan untuk data yang jarang berubah

---

## PRD REFERENSI

[PASTE BAGIAN PRD YANG RELEVAN UNTUK RESOURCE INI]

---

## OUTPUT YANG DIHARAPKAN

Format jawaban:
❌ [Nama masalah] — [penjelasan singkat]
✅ Fix: [kode atau langkah konkret]

Prioritas:
🔴 Critical (data bocor, error fatal, logic salah)
🟡 Medium (fitur belum lengkap, relasi belum nyambung)
🟢 Minor (UI spacing, kosmetik)
```

---

## URUTAN AUDIT (Prioritas)

### 🔴 Core Flow Dulu
1. Workspaces (multi-tenant isolation)
2. Users + Roles + Permissions
3. Leads → Clients (CRM flow)
4. Projects → Tasks (PM flow)
5. Quotations → Invoices → Transactions (Finance flow)

### 🟡 Supporting Modules
6. Contracts
7. SupportTickets
8. Meetings → Tasks (auto create)
9. Notes
10. Files
11. BankAccounts
12. Vendors
13. Subscriptions

### 🟢 Advanced Features
14. AutomationWorkflow
15. WaMessages + WaSessions
16. MarketingCampaigns
17. Newsletters
18. Pipelines
19. Servers + Websites + Domains
20. ServiceCredentials
21. CalendarEvents
22. Roles + Permissions (fine-tuning)

---

## TIPS PENGGUNAAN

- **Codex** → pakai buat fix kode yang spesifik
- **Gemini** → pakai buat audit logic & relasi
- **Claude** → pakai buat review arsitektur & edge cases

- Audit 1 Resource per prompt
- Setelah fix, audit lagi Resource yang sama untuk konfirmasi
- Simpan hasil audit sebagai checklist progress
