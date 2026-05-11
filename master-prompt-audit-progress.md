# MASTER PROMPT — AUDIT PROGRESS MODUL KANTOR DIGITAL
> Copy paste ke Codex/Gemini/Claude untuk audit progress development

---

## PROMPT UTAMA

```
Kamu adalah QA Engineer untuk project Laravel 13 + Filament v5 
bernama Kantor Digital — sistem manajemen digital agency.

Tugasmu: Audit progress development dan laporkan status setiap modul.

---

## TOTAL MODUL PRD: 43 MODUL

Cek satu per satu apakah modul berikut sudah ada di codebase:

### SECTION A — MAIN
1. Dashboard (KPI cards, charts, quick actions, per role)
2. Activity Feed (audit log + social feed, realtime)
3. Calendar (month/week/day/agenda, drag & drop)

### SECTION B — CRM & CLIENT
4. Leads / CRM (kanban + table, multiple pipeline, AI scoring)
5. Clients (CRUD, detail tabs: projects, invoices, contracts, tickets)
6. Contracts (generate template, e-sign, reminder expiry)
7. Support Tickets (SLA timer, AI reply, multi-channel)

### SECTION C — PROJECT MANAGEMENT
8. Projects (kanban + table + grid, template, client portal)
9. Tasks (nested, gantt, time tracking, recurring, dependencies)
10. Team Management (workload view, temporary role)
11. Meetings (linked project/client, auto create tasks dari notes)
12. Notes / SOP (rich text, version history, template)
13. File Manager (versioning, approval, share link expiry)

### SECTION D — FINANCE
14. Finance Overview (dashboard income/expense/profit)
15. Quotation / Proposal (itemized, PDF, client approve via link)
16. Invoices (proforma, credit note, Pakasir, recurring, PDF)
17. Transactions (income & expense log, attachment)
18. Subscriptions (track tools, reminder renewal)
19. Billing (retainer, billing cycle, auto generate invoice)
20. Payroll & Fee Tim (bagi hasil per project, komisi, template)
21. Kas & Bank (multi rekening, mutasi, rekonsiliasi)
22. Expense & Reimbursement (submit, approve, petty cash, budget)
23. Laporan Keuangan (P&L, cash flow, neraca, pajak)

### SECTION E — AUTOMATION & AI
24. Automation (visual builder linked n8n, trigger, log runs)
25. AI Tools (writing, summary, reply, scoring, chat assistant)
26. Integrations (WA, Google, Pakasir, Meta, dll + health check)
27. API Keys (generate, scope, revoke, IP whitelist, rate limit)

### SECTION F — DIGITAL SERVICES
28. Website Manager (uptime, SSL monitor, linked project/client)
29. Deployments (VPS + cloud, history, rollback, env variables)
30. Domains (DNS, SSL, expiry reminder, linked client)
31. Hosting / VPS (resource monitor, SSH keys, renewal)
32. Forms (builder drag & drop, → Lead, embed, captcha)

### SECTION G — MARKETING
33. Marketing Overview (ROI, performance summary)
34. Social Media Planner (kalender konten, schedule, AI caption)
35. Email Campaigns (blast, drip, A/B test, track open/click)
36. Analytics (GA, Meta Ads, TikTok Ads, funnel)

### SECTION H — COMMUNICATION
37. Chat / Inbox (internal DM + WA inbox multi-agent, unified view)
38. Notifications (realtime bell, preferences, push, email digest)

### SECTION I — SYSTEM
39. Role & Permissions (custom role builder, inheritance, temporary)
40. Workspace Settings (logo, warna, SMTP, WA config, n8n config)
41. Audit Logs (realtime stream, filter, alert, export)
42. Security (2FA, SSO, session mgmt, device mgmt, GDPR)
43. Help Center (dokumentasi, FAQ, onboarding checklist, status page)

---

## CARA CEK PER MODUL

Untuk setiap modul, cek:

### A. Resource / File Ada?
- Ada file Resource di app/Filament/Resources/?
- Ada Model di app/Domain/.../Models/?
- Ada Migration di database/migrations/?

### B. Fitur Dasar Lengkap?
- List page dengan kolom yang benar
- Create & Edit form dengan field yang benar
- Delete dengan konfirmasi
- Filter & search

### C. Fitur Khusus Ada?
- Sesuai deskripsi PRD masing-masing modul
- Relasi ke modul lain tersambung
- Actions khusus (convert, approve, generate PDF, dll)

### D. Multi-Tenant Safe?
- Ada global scope by workspace_id
- Query tidak bocor antar tenant

---

## FORMAT LAPORAN YANG DIHARAPKAN

Jawab dengan format ini:

---
## 📊 PROGRESS REPORT — KANTOR DIGITAL
**Total Modul PRD: 43**
**Selesai: X modul (X%)**
**Belum: X modul (X%)**
**Partial: X modul (X%)**

---

### ✅ SELESAI (fitur lengkap sesuai PRD)
- [nomor]. [NamaModul] — [catatan singkat jika ada]

### ⚠️ PARTIAL (ada tapi belum lengkap)
- [nomor]. [NamaModul]
  - ✅ Sudah: [apa yang sudah ada]
  - ❌ Belum: [apa yang kurang]

### ❌ BELUM ADA (tidak ada file/resource sama sekali)
- [nomor]. [NamaModul]

---

### 🔴 PRIORITAS FIX (urutkan dari yang paling kritikal)
1. [NamaModul] — alasan kenapa kritikal
2. dst...

### 📋 REKOMENDASI LANGKAH SELANJUTNYA
[Saran konkret apa yang harus dikerjakan dulu]
---

---

## KONTEKS PROJECT

- Framework: Laravel 13 + Filament v5
- Database: PostgreSQL
- Multi-tenant: 2 workspace (Volora & Maven)
- Auth: Laravel Sanctum + Spatie Permission
- Queue: Redis + Laravel Horizon
- Realtime: Laravel Reverb (WebSocket)
- Automation: n8n (self-hosted)
- WhatsApp: WhatsApp Business API
- Payment: Pakasir (QRIS, VA)
- Storage: S3 / MinIO
- AI: Laravel AI SDK (built-in Laravel 13)

Struktur folder Resources ada di:
app/Filament/Resources/

Struktur folder Domain ada di:
app/Domain/

Scan semua file yang ada lalu laporkan progress sesuai format di atas.
```

---

## CARA PAKAI

1. Copy seluruh prompt di atas
2. Paste ke Codex/Gemini/Claude
3. Pastikan AI punya akses ke codebase (Codex otomatis, Gemini/Claude perlu paste struktur folder)
4. AI akan scan dan laporkan progress lengkap

## TIPS

- **Codex** → paling akurat karena bisa langsung baca file
- **Gemini** → paste output `tree app/` dulu biar dia bisa analisis
- **Claude** → paste struktur folder + list file yang ada

## TAMBAHAN — PROMPT UNTUK LIHAT STRUKTUR FOLDER

Jalankan ini di terminal dulu sebelum kasih ke Gemini/Claude:

```bash
# Windows (PowerShell)
tree app /f

# Mac/Linux
find app -type f -name "*.php" | sort

# Atau khusus Filament Resources
ls app/Filament/Resources/
```

Paste hasilnya ke AI bersama prompt di atas.
