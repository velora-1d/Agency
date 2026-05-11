# KANTOR DIGITAL — Dokumentasi Lengkap

> Sistem Manajemen Digital Agency untuk Volora & Maven
> Laravel 13 + PostgreSQL + Inertia.js + Vue 3 + n8n + WhatsApp

---

## DAFTAR ISI

1. Overview & Tech Stack
2. Modul & Fitur Lengkap
3. Relasi Antar Modul
4. Database Schema / ERD (PostgreSQL)
5. Struktur Folder Laravel 13
6. Mindmap Sistem
7. Roadmap Development

---

# BAB 1 — OVERVIEW & TECH STACK

## Deskripsi Sistem

Kantor Digital adalah aplikasi manajemen operasional untuk 2 agensi digital:

- **Velora** — workspace pertama
- **Maven** — workspace kedua

Sistem ini mencakup CRM, Project Management, Finance, Automation, Marketing, Communication, dan Digital Services dalam satu platform terintegrasi.

## Tech Stack

| Layer         | Teknologi                                 |
| ------------- | ----------------------------------------- |
| Backend       | Laravel 13 (PHP 8.3+)                     |
| Frontend      | Inertia.js + Vue 3 + Tailwind CSS         |
| UI Components | Shadcn-Vue + Custom Enterprise Components |
| Database      | PostgreSQL                                |
| Queue         | Redis + Laravel Horizon                   |
| Realtime      | Laravel Reverb (WebSocket)                |
| Automation    | n8n (self-hosted)                         |
| WhatsApp      | WhatsApp Business API                     |
| Payment       | Pakasir (QRIS, VA) + Manual Transfer      |
| Storage       | S3 / MinIO                                |
| AI            | Laravel AI SDK (built-in Laravel 13)      |
| Auth          | Laravel Sanctum + Spatie Permission       |

## Multi-Tenant

- 2 workspace fixed: Velora & Maven
- Setiap workspace isolated (data tidak tercampur)
- Multi-workspace switcher di navbar
- Config berbeda per workspace (logo, warna, SMTP, WA API, n8n)

---

# BAB 2 — MODUL & FITUR LENGKAP

## SECTION A: MAIN

### 1. DASHBOARD

**Top KPI Cards (dengan trend indicator % vs bulan lalu):**

- Revenue bulan ini
- Active clients
- Active projects
- Pending tasks
- Unpaid invoices
- Automation runs
- Team productivity
- Leads masuk

**Charts (toggle: 7d / 30d / 3m / 1y):**

- Revenue chart (line)
- Leads conversion (funnel)
- Project progress (bar)
- Monthly growth (area)

**Quick Actions:**

- Add client
- Create project
- Create invoice
- Trigger automation
- Send broadcast WA

**Recent Activity:**

- Project update
- Payment masuk
- Task selesai
- Client baru
- Automation logs

**Category Summary:**

- CRM: total leads, hot leads
- Project: ongoing, overdue tasks
- Finance: income, unpaid
- Marketing: campaign performance

**Bottom Section:**

- Upcoming meetings
- Calendar mini
- Recent files
- Notifications

**Dashboard per Role:**

- Owner/Admin: semua KPI + finance
- Project Manager: projects & tasks
- Marketing: leads & campaigns
- Finance: revenue & invoices

---

### 2. ACTIVITY FEED

- Campuran audit log + social feed
- Per workspace (isolated)
- Timeline view (terbaru di atas)
- Filter: per modul / per user / per tanggal
- Setiap item: icon, deskripsi, trigger, waktu, link ke item
- Comment & reply per activity
- Mention @user
- Realtime via WebSocket

---

### 3. CALENDAR

- Views: Month / Week / Day / Agenda
- Mini calendar di sidebar
- Event types: meetings, task due date, invoice due, follow-up CRM, campaign schedule, automation schedule
- Drag & drop reschedule
- Klik event → detail popup
- Create event langsung dari calendar
- Filter: by kategori / by assignee
- Internal only (no external sync)

---

## SECTION B: CRM & CLIENT

### 4. LEADS / CRM

**Views:** Kanban + Table

**Kanban Stages (multiple pipeline, custom per service):**

- New → Contacted → Proposal → Negotiation → Won / Lost

**Data per Lead:**

- Nama, kontak (WA, email)
- Source (Instagram, referral, website form, ads, dll)
- Nilai estimasi deal
- Assignee
- Notes & activity history
- Status & priority
- Pipeline (Web Dev, Social Media, dll)

**Fitur:**

- Multiple pipeline per service/produk
- Custom stages per pipeline
- Lead scoring (AI)
- Auto-create lead dari Forms submission
- Auto WA via n8n (bisa ON/OFF per admin)
- Filter: by stage, source, assignee, date, value
- Export CSV

---

### 5. CLIENTS

**Data:**

- Nama perusahaan, PIC, kontak (WA, email, telp)
- Industry, lokasi
- Status: Active / Inactive / On Hold
- Linked: projects, invoices, contracts, tickets

**Detail Client (tabs):**

- Overview
- Projects
- Invoices
- Contracts
- Support Tickets
- Activity history
- Notes
  --Wajib ada fitur CRUD
  **Filter:** by status, industry, assignee, date joined

---

### 6. CONTRACTS

- Generate dari template
- Status: Draft → Sent → Signed → Expired
- Upload signed document (PDF)
- Link ke client & project
- Reminder otomatis sebelum expired
- E-sign support
  --Wajib ada fitur CRUD

---

### 7. SUPPORT TICKETS

- Submit via portal client atau WA
- Priority: Low / Medium / High / Urgent
- Status: Open → In Progress → Resolved → Closed
- Assignee ke tim
- SLA timer
- Auto-reply via n8n
- AI reply suggestion
  --Wajib ada fitur CRUD

---

## SECTION C: PROJECT MANAGEMENT

### 8. PROJECTS

**Views:** Kanban + Table + Grid

**Kanban Status:** Planning → Active → On Hold → Completed

**Data per Project:**

- Nama, deskripsi
- Client (linked)
- Timeline (start & end date)
- Budget & actual cost
- Team members
- Progress % (auto dari tasks)
- Tags/labels

**Detail Project (tabs):**

- Overview
- Tasks
- Files
- Notes/SOP
- Meetings
- Invoices
- Activity log

**Fitur Tambahan:**

- Project template (Web Dev, Social Media, dll)
- Client portal (read-only, approve deliverable, download invoice)
- Approval system deliverable
- Filter: by client, status, assignee, deadline, budget
  --Wajib ada fitur CRUD

---

### 9. TASKS

- Nested tasks (task → subtask)
- Assignee, due date, priority, tags
- Status: To Do → In Progress → Review → Done
- Dependencies antar task
- Time tracking (log jam kerja)
- Comment & mention
- Recurring tasks (harian/mingguan/bulanan)
- Task template per jenis project
- Views: List / Kanban / Calendar / Gantt
  --Wajib ada fitur CRUD

---

### 10. TEAM MANAGEMENT

- List semua member per workspace
- Role & kapasitas kerja
- Workload view (overload vs available)
- Invite via email
- Per member: assigned tasks, projects, performance
- Temporary role (auto expire)
- Permission per client
  --Wajib ada fitur CRUD

---

### 11. MEETINGS

- Schedule meeting (linked ke project/client)
- Peserta: internal + external/client
- Agenda & notes
- Recording link (Zoom/Meet URL)
- Meeting notes → auto jadi Tasks (action items)
- Linked ke Calendar
  --Wajib ada fitur CRUD

---

### 12. NOTES / SOP

- Rich text editor (Notion-like)
- Folder/kategori structure
- Link ke project atau global
- Version history
- Share ke tim atau private
- Template SOP
- SOP linked ke task
  --Wajib ada fitur CRUD

---

### 13. FILE MANAGER

- Upload, organize per folder
- Link ke project/client
- Preview: image, PDF, video
- Storage quota per workspace
- Share link dengan expiry
- File versioning (v1, v2, v3)
- File approval (client approve/reject asset)
  --Wajib ada fitur CRUD

---

## SECTION D: FINANCE

### 14. FINANCE OVERVIEW

- Total income, expense, profit bulan ini
- Outstanding invoices
- Cash flow chart
- Revenue per client / per project
- Tax summary
  --Wajib ada fitur CRUD

---

### 15. QUOTATION / PROPOSAL

**Flow:** Lead → Buat Proposal → Kirim ke Client → Approved → Invoice + Project

**Isi Proposal:**

- Cover letter / intro
- Scope of work
- Timeline project
- Rincian biaya itemized:
    - Biaya development
    - Biaya design
    - Biaya server/hosting
    - Biaya domain
    - Biaya maintenance
    - Biaya SSL
    - Biaya revisi tambahan
    - Biaya konsultasi
    - Custom item (bebas tambah)
- Per item: nama, deskripsi, qty, satuan, harga, diskon, subtotal
- Summary: subtotal, diskon global, tax PPN 11%, total, DP, sisa
- Terms & conditions
- Signature section (e-sign / manual)
- Valid until date

**Fitur:**

- Template per jenis project
- Versi proposal (v1, v2 jika revisi)
- Client approve via link (tanpa login)
- Internal approval sebelum kirim
- Reminder otomatis jika belum di-approve
- Export PDF (1 file lengkap)
- Notif WA/email saat dikirim
  --Wajib ada fitur CRUD

---

### 16. INVOICES

- Generate dari project/contract/quotation approved
- Template invoice branded per workspace
- Status: Draft → Sent → Partial → Paid → Overdue
- Proforma invoice (DP)
- Credit note (refund/koreksi)
- Internal approval sebelum kirim
- Multi-currency
- Tax & discount
- Payment link via Pakasir (QRIS, VA)
- Manual transfer + konfirmasi
- Auto reminder WA/email sebelum due date
- Recurring invoice (retainer)
- PDF export
  --Wajib ada fitur CRUD

---

### 17. TRANSACTIONS

- Income & expense log
- Kategori: operasional, gaji, tools, ads, dll
- Linked ke invoice atau manual entry
- Attachment (bukti transfer/kwitansi)
- Filter: by date, kategori, client, project
- Export PDF/Excel
  --Wajib ada fitur CRUD

---

### 18. SUBSCRIPTIONS

- Track langganan tools tim
- Reminder sebelum renewal
- Status: Active / Expired / Cancelled
- Biaya per bulan/tahun
- Linked ke expense transactions
- Vendor management (kontak, kontrak, payment terms)
  --Wajib ada fitur CRUD

---

### 19. BILLING

- Billing per client (retainer / project-based)
- Billing cycle management
- Auto generate invoice dari billing schedule
- Payment history per client

---Wajib ada fitur CRUD--

### 20. PAYROLL & FEE TIM

**Model: Bagi Hasil Per Project**

```
Total Nilai Project
    ↓ Step 1 — Potong Biaya Operasional (flat)
    ↓ Step 2 — Potong Kas Kantor (%)
    ↓ Step 3 — Bagi ke Tim (% atau flat per posisi)
    ↓ Step 4 — Setting waktu bayar
```

**Komponen per anggota:**

- Fee pokok (% atau flat)
- Bonus project
- Komisi closing (sales) — auto + override manual
- Potongan jika ada

**Setting:** beda per project, bisa save sebagai template

**Pembayaran:** saat DP masuk / project selesai / client lunas / custom

**Laporan per anggota:**

- Fee pending & sudah dibayar
- Total earning per bulan/kuartal/tahun
- Rekap komisi
  --Wajib ada fitur CRUD

---

### 21. KAS & BANK

- Multi rekening (BCA, Mandiri, BRI, dll)
- Kas tunai
- Mutasi per rekening
- Transfer antar rekening/kas
- Saldo real-time
- Rekonsiliasi bank

---Wajib ada fitur CRUD--

### 22. EXPENSE & REIMBURSEMENT

- Tim submit pengeluaran
- Admin approve/reject
- Petty cash management
- Budget per project (alert overbudget)
- Budget per divisi

----Wajib ada fitur CRUD-

### 23. LAPORAN KEUANGAN

- P&L (Profit & Loss) bulanan/kuartalan/tahunan
- Cash flow statement
- Balance sheet / Neraca
- HPP per project
- Laporan pajak (PPh 21, PPh 23, PPN)
- Laporan per divisi
- Laporan per karyawan
- Chart of Accounts

----Wajib ada fitur CRUD-

## SECTION E: AUTOMATION & AI

### 24. AUTOMATION

- Visual workflow builder (linked ke n8n)
- Trigger: lead masuk, invoice due, task selesai, payment masuk, form submission, dll
- Action: kirim WA, email, buat task, update status, notify tim
- Multi-step workflow (chain of actions)
- Conditional logic (if/else)
- Schedule trigger (cron-based)
- Template automation siap pakai
- Retry otomatis jika gagal
- Log runs (sukses/gagal)
- Enable/disable per automation
- Per workspace (Volora & Maven beda config)

---Wajib ada fitur CRUD--

### 25. AI TOOLS

- AI Writing: caption, email, proposal, SOP
- AI Summary: ringkas meeting notes, dokumen
- AI Reply Suggestion: di chat/inbox & support ticket
- AI Report: generate laporan otomatis
- AI Lead Scoring: nilai lead dari behavior
- AI Chat Assistant: tanya seputar data project/client
- AI Invoice Reader: extract data dari invoice PDF
- AI Sentiment Analysis: support ticket & chat client
- AI Content Calendar Suggestion: untuk marketing
- Powered by: Laravel AI SDK (Laravel 13 built-in)

----Wajib ada fitur CRUD-

### 26. INTEGRATIONS

- WhatsApp Business API
- Google (Calendar, Drive, Gmail)
- Pakasir (payment)
- Mailchimp / SendGrid (email)
- Meta Ads (leads)
- Instagram & TikTok (social media)
- OpenAI / Claude API
- Zapier / n8n webhook
- Slack / Discord notifikasi
- Cloudflare (domain & hosting)
- S3 / Backblaze (storage)
- Status: Connected / Disconnected + health check ping

-----Wajib ada fitur CRUD

### 27. API KEYS

- Generate & manage API keys
- Per key: nama, permission scope, last used, expire date
- Revoke anytime
- Log penggunaan API
- Webhook inbound
- IP whitelist per key
- Rate limiting per key

-----Wajib ada fitur CRUD

## SECTION F: DIGITAL SERVICES

### 28. WEBSITE MANAGER

- List semua website client
- Status: Live / Staging / Maintenance / Down
- Link ke domain, hosting, deployment
- Tech stack info
- SSL status & expiry
- Uptime monitoring
- Linked ke project & client

-----Wajib ada fitur CRUD

### 29. DEPLOYMENTS

- Deploy ke VPS sendiri atau cloud (Vercel, Netlify, Cloudflare Pages)
- Deployment history & rollback
- Status: Pending → Building → Success / Failed
- Auto deploy via Git webhook
- Environment variables manager
- Log output real-time

-----Wajib ada fitur CRUD

### 30. DOMAINS

- List semua domain client
- Expiry date + auto reminder
- DNS management (A, CNAME, MX, TXT)
- Registrar info
- SSL certificate status & expiry
- Linked ke client & website
- Automation reminder expiry
  --Wajib ada fitur CRUD

---

### 31. HOSTING / VPS

- List semua server/VPS
- Provider info (DigitalOcean, Vultr, Hetzner, AWS, dll)
- Resource monitor (CPU, RAM, disk, bandwidth)
- SSH key management
- Renewal date & cost
- Linked ke client & domain

---Wajib ada fitur CRUD--

### 32. FORMS

- Form builder (drag & drop fields)
- Embed di website client
- Submission → auto jadi Lead di CRM
- Notif WA/email saat submission
- Anti-spam (captcha)
- Export submissions CSV

----Wajib ada fitur CRUD-

## SECTION G: MARKETING

### 33. MARKETING OVERVIEW

- Total campaign aktif
- Email open rate, click rate
- Social media reach & engagement
- Lead dari marketing (source breakdown)
- ROI per campaign
- Top performing content

----Wajib ada fitur CRUD-

### 34. SOCIAL MEDIA PLANNER

**Planning:**

- Kalender konten (month/week view)
- Pipeline: Idea → Draft → Review → Scheduled → Posted
- Per platform: Instagram, Facebook, TikTok, LinkedIn, Twitter/X
- Per client

**Posting:**

- Buat & schedule post langsung
- Upload image/video
- Caption + hashtag generator (AI)
- Auto post via API platform
- Approval flow
- Analytics per post

---Wajib ada fitur CRUD--

### 35. EMAIL CAMPAIGNS

**Manage:**

- List campaign, status, performance
- Segmentasi audience (by tag, status, source)
- Template email (drag & drop)
- A/B testing subject line

**Kirim:**

- Blast via SMTP / SendGrid / Mailchimp
- Schedule kirim
- Track: open rate, click rate, bounce, unsubscribe
- Auto follow-up sequence (drip campaign)
- Unsubscribe management

---Wajib ada fitur CRUD--

### 36. ANALYTICS

- Traffic website client (Google Analytics embed)
- Meta Ads performance
- TikTok Ads performance
- Email campaign analytics
- Lead source breakdown
- Conversion funnel
- Custom date range & filter per client

----Wajib ada fitur CRUD-

## SECTION H: COMMUNICATION

### 37. CHAT / INBOX

**Internal Chat:**

- Direct message antar tim
- Group channel (per project, per divisi)
- Mention @nama
- Reply thread
- Share file, image, link
- Pin pesan penting
- Realtime via Laravel Reverb

**WA Inbox:**

- Semua pesan WA masuk ke satu inbox
- Assign conversation ke agent
- Label/tag conversation (Lead, Client, Support, dll)
- Quick reply template
- AI reply suggestion
- Status: Open / Pending / Resolved
- Linked ke CRM (auto-match kontak)
- Broadcast WA langsung
- Multi-agent

**Unified View:**

- Tab: Internal / WhatsApp / All
- Search across semua conversation
- Filter: by assignee, label, status, tanggal

----Wajib ada fitur CRUD-

### 38. NOTIFICATIONS

- Realtime bell notif (in-app)
- Kategori: mention, task assigned, invoice due, payment masuk, lead baru, automation run
- Mark as read / clear all
- Preferences per user
- Push notification (mobile)
- Email digest (daily/weekly)

---Wajib ada fitur CRUD--

## SECTION I: SYSTEM

### 39. ROLE & PERMISSIONS

- Default roles: Owner, Admin, Manager, Staff, Finance, Marketing, Client
- Custom role builder
- Permission per modul (view, create, edit, delete)
- Role inheritance
- Temporary role (auto expire)
- Permission per client
- Per workspace

----Wajib ada fitur CRUD-

### 40. WORKSPACE SETTINGS

- Nama, logo, warna brand per workspace
- Timezone, currency, bahasa
- Working hours & holiday calendar
- Custom domain (app.volora.com)
- Email SMTP settings
- WA API config
- n8n webhook config
- Notification template (WA/email)
- Data backup & restore manual
- Storage management
- Multi-workspace switcher

----Wajib ada fitur CRUD-

### 41. AUDIT LOGS

- Log semua aksi: siapa, apa, kapan, IP
- Real-time log stream
- Filter: by user, modul, aksi, tanggal
- Alert aksi mencurigakan
- Log per integrasi (n8n, WA, API)
- Export PDF/CSV
- Retention policy

---Wajib ada fitur CRUD--

### 42. SECURITY

- 2FA (Two Factor Authentication)
- SSO (Google login)
- Session management (lihat & revoke)
- Device management
- IP whitelist
- Password policy
- Login history
- Auto logout idle
- Brute force protection
- Data encryption at rest
- GDPR compliance tools

----Wajib ada fitur CRUD-

### 43. HELP CENTER

- Dokumentasi internal (artikel & tutorial)
- FAQ
- Video tutorial embed
- Submit ticket ke developer/support
- Changelog (update fitur)
- Onboarding checklist
- In-app tooltip & feature tour
- Status page (uptime sistem)
- Live chat support embed

-----Wajib ada fitur CRUD

# BAB 3 — RELASI ANTAR MODUL

## Alur Utama (Core Chain)

```
FORM (submission)
    ↓
LEADS/CRM
    ↓ (won)
CLIENTS
    ↓
QUOTATION/PROPOSAL
    ↓ (approved)
CONTRACTS + PROJECTS + INVOICE (DP)
    ↓
TASKS → TEAM → MEETINGS → FILES → NOTES
    ↓ (selesai)
INVOICE (final) → PAYMENT (Pakasir/Manual)
    ↓
TRANSACTIONS → PROJECT FINANCE SPLITTING
    ↓
FEE TIM + KAS KANTOR + P&L
```

## Relasi Lengkap Per Modul

| Modul             | Berhubungan Dengan                                                                                                               |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| LEADS             | Forms, Clients, Automation, Marketing, Calendar, Activity Feed                                                                   |
| CLIENTS           | Leads, Projects, Contracts, Invoices, Support Tickets, Quotation, Chat/WA, Files, Client Portal                                  |
| PROJECTS          | Clients, Tasks, Team, Meetings, Files, Notes, Invoices, Finance Splitting, Deployments, Website Manager, Activity Feed, Calendar |
| TASKS             | Projects, Team, Calendar, Meetings, Notes/SOP, Activity Feed, Automation                                                         |
| INVOICES          | Clients, Projects, Quotation, Contracts, Transactions, Finance Splitting, Pakasir, Automation, Calendar                          |
| FINANCE SPLITTING | Projects, Invoices, Team, Transactions, Kas Kantor, Payroll                                                                      |
| AUTOMATION        | Leads, Invoices, Tasks, Support Tickets, Forms, Chat/WA, Calendar, Deployments, Email Campaigns                                  |
| CHAT/WA INBOX     | Leads, Clients, Support Tickets, Automation, Team, Activity Feed                                                                 |
| MARKETING         | Leads, Analytics, Social Media, Email Campaigns, Automation, Forms                                                               |
| DOMAINS & HOSTING | Clients, Website Manager, Deployments, Subscriptions, Transactions, Automation                                                   |
| NOTIFICATIONS     | Semua modul, WA Inbox, Email, Activity Feed                                                                                      |
| AUDIT LOGS        | Semua modul, Security, Role & Permissions                                                                                        |
| CLIENT PORTAL     | Projects, Invoices, Quotation, Files, Support Tickets, Contracts                                                                 |

---

# BAB 4 — DATABASE SCHEMA / ERD (PostgreSQL)

## CORE TABLES

### workspaces

```sql
CREATE TABLE workspaces (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,           -- 'Volora', 'Maven'
    slug VARCHAR(100) UNIQUE NOT NULL,
    logo VARCHAR(255),
    primary_color VARCHAR(7),
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    currency VARCHAR(3) DEFAULT 'IDR',
    language VARCHAR(5) DEFAULT 'id',
    custom_domain VARCHAR(255),
    smtp_host VARCHAR(255),
    smtp_port INTEGER,
    smtp_username VARCHAR(255),
    smtp_password VARCHAR(255),
    wa_api_key VARCHAR(255),
    wa_phone_number VARCHAR(20),
    n8n_webhook_url VARCHAR(255),
    working_hours_start TIME DEFAULT '08:00',
    working_hours_end TIME DEFAULT '17:00',
    storage_quota_gb INTEGER DEFAULT 50,
    settings JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### users

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    email_verified_at TIMESTAMP,
    two_factor_secret VARCHAR(255),
    two_factor_enabled BOOLEAN DEFAULT false,
    last_login_at TIMESTAMP,
    last_login_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### workspace_users

```sql
CREATE TABLE workspace_users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    role_id UUID REFERENCES roles(id),
    is_owner BOOLEAN DEFAULT false,
    joined_at TIMESTAMP DEFAULT NOW(),
    expires_at TIMESTAMP,                 -- temporary role
    UNIQUE(workspace_id, user_id)
);
```

### roles

```sql
CREATE TABLE roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(50) NOT NULL,            -- 'Owner', 'Admin', 'Manager', dll
    slug VARCHAR(50) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT false,
    parent_role_id UUID REFERENCES roles(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### permissions

```sql
CREATE TABLE permissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    module VARCHAR(50) NOT NULL,          -- 'leads', 'projects', 'finance', dll
    action VARCHAR(20) NOT NULL,          -- 'view', 'create', 'edit', 'delete'
    description VARCHAR(255)
);
```

### role_permissions

```sql
CREATE TABLE role_permissions (
    role_id UUID REFERENCES roles(id) ON DELETE CASCADE,
    permission_id UUID REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);
```

---

## CRM TABLES

### leads

```sql
CREATE TABLE leads (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    pipeline_id UUID REFERENCES pipelines(id),
    stage_id UUID REFERENCES pipeline_stages(id),
    name VARCHAR(100) NOT NULL,
    company VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    source VARCHAR(50),                   -- 'instagram', 'referral', 'form', 'ads'
    estimated_value DECIMAL(15,2),
    priority VARCHAR(10) DEFAULT 'medium', -- 'low', 'medium', 'high'
    assigned_to UUID REFERENCES users(id),
    ai_score INTEGER,                     -- 0-100
    notes TEXT,
    converted_at TIMESTAMP,
    converted_to_client_id UUID,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### pipelines

```sql
CREATE TABLE pipelines (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,           -- 'Web Dev', 'Social Media', dll
    description TEXT,
    is_default BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### pipeline_stages

```sql
CREATE TABLE pipeline_stages (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    pipeline_id UUID REFERENCES pipelines(id) ON DELETE CASCADE,
    name VARCHAR(50) NOT NULL,            -- 'New', 'Contacted', dll
    order_index INTEGER NOT NULL,
    color VARCHAR(7),
    is_won BOOLEAN DEFAULT false,
    is_lost BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### clients

```sql
CREATE TABLE clients (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    lead_id UUID REFERENCES leads(id),
    company_name VARCHAR(100) NOT NULL,
    pic_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    industry VARCHAR(50),
    address TEXT,
    city VARCHAR(50),
    province VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active',  -- 'active', 'inactive', 'on_hold'
    assigned_to UUID REFERENCES users(id),
    notes TEXT,
    portal_access BOOLEAN DEFAULT false,
    portal_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### contracts

```sql
CREATE TABLE contracts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    project_id UUID REFERENCES projects(id),
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'draft',   -- 'draft', 'sent', 'signed', 'expired'
    content TEXT,
    file_path VARCHAR(255),
    signed_file_path VARCHAR(255),
    start_date DATE,
    end_date DATE,
    value DECIMAL(15,2),
    reminder_days_before INTEGER DEFAULT 30,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### support_tickets

```sql
CREATE TABLE support_tickets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    assigned_to UUID REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority VARCHAR(10) DEFAULT 'medium', -- 'low', 'medium', 'high', 'urgent'
    status VARCHAR(20) DEFAULT 'open',    -- 'open', 'in_progress', 'resolved', 'closed'
    source VARCHAR(20) DEFAULT 'portal',  -- 'portal', 'whatsapp', 'email'
    sla_due_at TIMESTAMP,
    resolved_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

---

## PROJECT TABLES

### projects

```sql
CREATE TABLE projects (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'planning', -- 'planning', 'active', 'on_hold', 'completed'
    start_date DATE,
    end_date DATE,
    budget DECIMAL(15,2),
    actual_cost DECIMAL(15,2) DEFAULT 0,
    progress INTEGER DEFAULT 0,           -- 0-100
    template_id UUID REFERENCES project_templates(id),
    tags TEXT[],
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### project_members

```sql
CREATE TABLE project_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID REFERENCES projects(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50),                     -- 'pm', 'developer', 'designer', dll
    joined_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(project_id, user_id)
);
```

### project_templates

```sql
CREATE TABLE project_templates (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    default_tasks JSONB,
    default_finance_split JSONB,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### tasks

```sql
CREATE TABLE tasks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    project_id UUID REFERENCES projects(id) ON DELETE CASCADE,
    parent_task_id UUID REFERENCES tasks(id),
    assigned_to UUID REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'todo',    -- 'todo', 'in_progress', 'review', 'done'
    priority VARCHAR(10) DEFAULT 'medium',
    due_date TIMESTAMP,
    estimated_hours DECIMAL(5,2),
    actual_hours DECIMAL(5,2) DEFAULT 0,
    order_index INTEGER DEFAULT 0,
    is_recurring BOOLEAN DEFAULT false,
    recurrence_rule VARCHAR(100),         -- cron expression
    template_id UUID REFERENCES task_templates(id),
    sop_note_id UUID REFERENCES notes(id),
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### task_dependencies

```sql
CREATE TABLE task_dependencies (
    task_id UUID REFERENCES tasks(id) ON DELETE CASCADE,
    depends_on_task_id UUID REFERENCES tasks(id) ON DELETE CASCADE,
    PRIMARY KEY (task_id, depends_on_task_id)
);
```

### task_time_logs

```sql
CREATE TABLE task_time_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    task_id UUID REFERENCES tasks(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id),
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP,
    hours DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### meetings

```sql
CREATE TABLE meetings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    project_id UUID REFERENCES projects(id),
    client_id UUID REFERENCES clients(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    agenda TEXT,
    notes TEXT,
    recording_url VARCHAR(255),
    meeting_url VARCHAR(255),
    scheduled_at TIMESTAMP NOT NULL,
    duration_minutes INTEGER,
    status VARCHAR(20) DEFAULT 'scheduled',
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### meeting_attendees

```sql
CREATE TABLE meeting_attendees (
    meeting_id UUID REFERENCES meetings(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    is_external BOOLEAN DEFAULT false,
    external_name VARCHAR(100),
    external_email VARCHAR(255),
    PRIMARY KEY (meeting_id, user_id)
);
```

### notes

```sql
CREATE TABLE notes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    project_id UUID REFERENCES projects(id),
    folder_id UUID REFERENCES note_folders(id),
    title VARCHAR(255) NOT NULL,
    content TEXT,
    type VARCHAR(20) DEFAULT 'note',      -- 'note', 'sop', 'template'
    is_private BOOLEAN DEFAULT false,
    version INTEGER DEFAULT 1,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### files

```sql
CREATE TABLE files (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    project_id UUID REFERENCES projects(id),
    client_id UUID REFERENCES clients(id),
    folder_id UUID REFERENCES file_folders(id),
    name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    path VARCHAR(500),
    mime_type VARCHAR(100),
    size_bytes BIGINT,
    version INTEGER DEFAULT 1,
    parent_file_id UUID REFERENCES files(id),
    approval_status VARCHAR(20),          -- 'pending', 'approved', 'rejected'
    approved_by UUID REFERENCES users(id),
    share_token VARCHAR(255),
    share_expires_at TIMESTAMP,
    uploaded_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

## FINANCE TABLES

### quotations

```sql
CREATE TABLE quotations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    lead_id UUID REFERENCES leads(id),
    number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    cover_letter TEXT,
    scope_of_work TEXT,
    timeline TEXT,
    terms_conditions TEXT,
    status VARCHAR(20) DEFAULT 'draft',   -- 'draft', 'sent', 'approved', 'rejected', 'revised'
    version INTEGER DEFAULT 1,
    parent_quotation_id UUID REFERENCES quotations(id),
    subtotal DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 11,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    dp_percentage DECIMAL(5,2),
    dp_amount DECIMAL(15,2),
    valid_until DATE,
    approved_at TIMESTAMP,
    approval_token VARCHAR(255),
    sent_at TIMESTAMP,
    created_by UUID REFERENCES users(id),
    approved_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### quotation_items

```sql
CREATE TABLE quotation_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    quotation_id UUID REFERENCES quotations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50),                 -- 'development', 'server', 'domain', dll
    quantity DECIMAL(10,2) DEFAULT 1,
    unit VARCHAR(20),
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL,
    order_index INTEGER DEFAULT 0
);
```

### invoices

```sql
CREATE TABLE invoices (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    project_id UUID REFERENCES projects(id),
    quotation_id UUID REFERENCES quotations(id),
    contract_id UUID REFERENCES contracts(id),
    number VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(20) DEFAULT 'invoice',   -- 'proforma', 'invoice', 'credit_note'
    status VARCHAR(20) DEFAULT 'draft',   -- 'draft', 'sent', 'partial', 'paid', 'overdue'
    subtotal DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT 11,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total DECIMAL(15,2) DEFAULT 0,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'IDR',
    due_date DATE,
    is_recurring BOOLEAN DEFAULT false,
    recurrence_rule VARCHAR(100),
    payment_method VARCHAR(20),           -- 'pakasir', 'manual'
    pakasir_order_id VARCHAR(100),
    pakasir_payment_url VARCHAR(500),
    internal_approved_at TIMESTAMP,
    internal_approved_by UUID REFERENCES users(id),
    sent_at TIMESTAMP,
    paid_at TIMESTAMP,
    notes TEXT,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### invoice_items

```sql
CREATE TABLE invoice_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    invoice_id UUID REFERENCES invoices(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    order_index INTEGER DEFAULT 0
);
```

### payments

```sql
CREATE TABLE payments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    invoice_id UUID REFERENCES invoices(id),
    amount DECIMAL(15,2) NOT NULL,
    method VARCHAR(20) NOT NULL,          -- 'pakasir_qris', 'pakasir_va', 'manual_tf'
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'verified', 'rejected'
    pakasir_transaction_id VARCHAR(100),
    proof_file_path VARCHAR(255),
    notes TEXT,
    verified_by UUID REFERENCES users(id),
    verified_at TIMESTAMP,
    paid_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### transactions

```sql
CREATE TABLE transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    account_id UUID REFERENCES bank_accounts(id),
    invoice_id UUID REFERENCES invoices(id),
    project_id UUID REFERENCES projects(id),
    type VARCHAR(10) NOT NULL,            -- 'income', 'expense'
    category VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL,
    description TEXT,
    attachment_path VARCHAR(255),
    date DATE NOT NULL,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### bank_accounts

```sql
CREATE TABLE bank_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    bank_name VARCHAR(50),
    account_number VARCHAR(50),
    account_holder VARCHAR(100),
    type VARCHAR(20) DEFAULT 'bank',      -- 'bank', 'cash', 'e-wallet'
    balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### project_finance_splits

```sql
CREATE TABLE project_finance_splits (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    project_id UUID REFERENCES projects(id) ON DELETE CASCADE,
    template_name VARCHAR(100),
    kas_kantor_percentage DECIMAL(5,2),
    kas_kantor_amount DECIMAL(15,2),
    payment_trigger VARCHAR(20),          -- 'dp', 'completion', 'full_paid', 'custom'
    payment_trigger_custom TEXT,
    total_project_value DECIMAL(15,2),
    total_operational_cost DECIMAL(15,2),
    total_kas_kantor DECIMAL(15,2),
    total_team_fee DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### project_finance_split_items

```sql
CREATE TABLE project_finance_split_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    split_id UUID REFERENCES project_finance_splits(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL,            -- 'operational', 'team_fee'
    label VARCHAR(100),                   -- 'Biaya Server', 'Developer Fee', dll
    user_id UUID REFERENCES users(id),
    calculation_type VARCHAR(10),         -- 'percentage', 'flat'
    percentage DECIMAL(5,2),
    flat_amount DECIMAL(15,2),
    final_amount DECIMAL(15,2),
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'paid'
    paid_at TIMESTAMP
);
```

### subscriptions

```sql
CREATE TABLE subscriptions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    vendor_id UUID REFERENCES vendors(id),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    billing_cycle VARCHAR(10),            -- 'monthly', 'yearly'
    status VARCHAR(20) DEFAULT 'active',
    next_renewal_date DATE,
    reminder_days_before INTEGER DEFAULT 7,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### vendors

```sql
CREATE TABLE vendors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    payment_terms TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

## COMMUNICATION TABLES

### conversations

```sql
CREATE TABLE conversations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL,            -- 'internal', 'whatsapp'
    name VARCHAR(100),
    wa_contact_phone VARCHAR(20),
    wa_contact_name VARCHAR(100),
    client_id UUID REFERENCES clients(id),
    lead_id UUID REFERENCES leads(id),
    assigned_to UUID REFERENCES users(id),
    status VARCHAR(20) DEFAULT 'open',    -- 'open', 'pending', 'resolved'
    label VARCHAR(50),
    last_message_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### messages

```sql
CREATE TABLE messages (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    conversation_id UUID REFERENCES conversations(id) ON DELETE CASCADE,
    sender_id UUID REFERENCES users(id),
    content TEXT,
    type VARCHAR(20) DEFAULT 'text',      -- 'text', 'image', 'file', 'audio'
    file_path VARCHAR(255),
    wa_message_id VARCHAR(100),
    is_from_client BOOLEAN DEFAULT false,
    read_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### notifications

```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    data JSONB,
    read_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

## DIGITAL SERVICES TABLES

### websites

```sql
CREATE TABLE websites (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    project_id UUID REFERENCES projects(id),
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    tech_stack VARCHAR(100),
    status VARCHAR(20) DEFAULT 'live',    -- 'live', 'staging', 'maintenance', 'down'
    ssl_expiry DATE,
    uptime_percentage DECIMAL(5,2),
    last_checked_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### domains

```sql
CREATE TABLE domains (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    website_id UUID REFERENCES websites(id),
    name VARCHAR(255) NOT NULL,
    registrar VARCHAR(100),
    expiry_date DATE,
    reminder_days_before INTEGER DEFAULT 30,
    ssl_expiry DATE,
    dns_records JSONB,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT NOW()
);
```

### servers

```sql
CREATE TABLE servers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    name VARCHAR(100) NOT NULL,
    provider VARCHAR(50),
    ip_address VARCHAR(45),
    ssh_port INTEGER DEFAULT 22,
    specs JSONB,                          -- CPU, RAM, disk, bandwidth
    monthly_cost DECIMAL(10,2),
    renewal_date DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT NOW()
);
```

### deployments

```sql
CREATE TABLE deployments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    website_id UUID REFERENCES websites(id),
    server_id UUID REFERENCES servers(id),
    environment VARCHAR(20),              -- 'production', 'staging'
    platform VARCHAR(20),                 -- 'vps', 'vercel', 'netlify', 'cloudflare'
    git_repo VARCHAR(255),
    git_branch VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'building', 'success', 'failed'
    log TEXT,
    deployed_by UUID REFERENCES users(id),
    deployed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### forms

```sql
CREATE TABLE forms (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    fields JSONB NOT NULL,
    settings JSONB,
    embed_code TEXT,
    auto_create_lead BOOLEAN DEFAULT true,
    submission_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### form_submissions

```sql
CREATE TABLE form_submissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    form_id UUID REFERENCES forms(id) ON DELETE CASCADE,
    data JSONB NOT NULL,
    lead_id UUID REFERENCES leads(id),
    ip_address VARCHAR(45),
    submitted_at TIMESTAMP DEFAULT NOW()
);
```

---

## MARKETING TABLES

### social_posts

```sql
CREATE TABLE social_posts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    client_id UUID REFERENCES clients(id),
    title VARCHAR(255),
    caption TEXT,
    hashtags TEXT,
    platforms TEXT[],                     -- ['instagram', 'tiktok', dll]
    media_files JSONB,
    status VARCHAR(20) DEFAULT 'idea',    -- 'idea', 'draft', 'review', 'scheduled', 'posted'
    scheduled_at TIMESTAMP,
    posted_at TIMESTAMP,
    analytics JSONB,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### email_campaigns

```sql
CREATE TABLE email_campaigns (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    preview_text VARCHAR(255),
    content TEXT,
    status VARCHAR(20) DEFAULT 'draft',
    scheduled_at TIMESTAMP,
    sent_at TIMESTAMP,
    recipient_count INTEGER DEFAULT 0,
    open_count INTEGER DEFAULT 0,
    click_count INTEGER DEFAULT 0,
    bounce_count INTEGER DEFAULT 0,
    unsubscribe_count INTEGER DEFAULT 0,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

## SYSTEM TABLES

### audit_logs

```sql
CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id),
    user_id UUID REFERENCES users(id),
    module VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    model_type VARCHAR(100),
    model_id UUID,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### activity_feed

```sql
CREATE TABLE activity_feed (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id),
    type VARCHAR(50) NOT NULL,
    subject_type VARCHAR(100),
    subject_id UUID,
    description TEXT,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### activity_comments

```sql
CREATE TABLE activity_comments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    activity_id UUID REFERENCES activity_feed(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id),
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);
```

### calendar_events

```sql
CREATE TABLE calendar_events (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    created_by UUID REFERENCES users(id),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(30),                     -- 'meeting', 'task_due', 'invoice_due', 'follow_up', 'campaign', 'automation'
    related_type VARCHAR(100),
    related_id UUID,
    start_at TIMESTAMP NOT NULL,
    end_at TIMESTAMP,
    all_day BOOLEAN DEFAULT false,
    color VARCHAR(7),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### automation_workflows

```sql
CREATE TABLE automation_workflows (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    trigger_type VARCHAR(50),
    trigger_config JSONB,
    actions JSONB,
    conditions JSONB,
    is_active BOOLEAN DEFAULT true,
    n8n_workflow_id VARCHAR(100),
    run_count INTEGER DEFAULT 0,
    last_run_at TIMESTAMP,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### automation_logs

```sql
CREATE TABLE automation_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workflow_id UUID REFERENCES automation_workflows(id) ON DELETE CASCADE,
    status VARCHAR(20),                   -- 'success', 'failed', 'retrying'
    trigger_data JSONB,
    result JSONB,
    error_message TEXT,
    executed_at TIMESTAMP DEFAULT NOW()
);
```

### api_keys

```sql
CREATE TABLE api_keys (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workspace_id UUID REFERENCES workspaces(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id),
    name VARCHAR(100) NOT NULL,
    key_hash VARCHAR(255) UNIQUE NOT NULL,
    scopes TEXT[],
    ip_whitelist TEXT[],
    rate_limit_per_hour INTEGER DEFAULT 1000,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);
```

---

# BAB 5 — STRUKTUR FOLDER LARAVEL 13

```
kantor-digital/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── SendInvoiceReminders.php
│   │       ├── ProcessRecurringInvoices.php
│   │       ├── CheckDomainExpiry.php
│   │       └── SyncAutomationLogs.php
│   │
│   ├── Domain/                          ← Domain-based architecture
│   │   ├── Auth/
│   │   │   ├── Actions/
│   │   │   ├── Models/
│   │   │   └── Services/
│   │   │
│   │   ├── Workspace/
│   │   │   ├── Actions/
│   │   │   ├── Models/
│   │   │   │   └── Workspace.php
│   │   │   └── Services/
│   │   │       └── WorkspaceService.php
│   │   │
│   │   ├── CRM/
│   │   │   ├── Actions/
│   │   │   │   ├── CreateLead.php
│   │   │   │   ├── ConvertLeadToClient.php
│   │   │   │   └── MovePipelineStage.php
│   │   │   ├── Models/
│   │   │   │   ├── Lead.php
│   │   │   │   ├── Pipeline.php
│   │   │   │   ├── PipelineStage.php
│   │   │   │   ├── Client.php
│   │   │   │   ├── Contract.php
│   │   │   │   └── SupportTicket.php
│   │   │   └── Services/
│   │   │       ├── LeadService.php
│   │   │       └── ClientService.php
│   │   │
│   │   ├── Project/
│   │   │   ├── Actions/
│   │   │   │   ├── CreateProject.php
│   │   │   │   ├── CalculateProjectProgress.php
│   │   │   │   └── GenerateActionItemsFromMeeting.php
│   │   │   ├── Models/
│   │   │   │   ├── Project.php
│   │   │   │   ├── Task.php
│   │   │   │   ├── TaskTimelog.php
│   │   │   │   ├── Meeting.php
│   │   │   │   ├── Note.php
│   │   │   │   └── File.php
│   │   │   └── Services/
│   │   │       ├── ProjectService.php
│   │   │       ├── TaskService.php
│   │   │       └── FileService.php
│   │   │
│   │   ├── Finance/
│   │   │   ├── Actions/
│   │   │   │   ├── CreateQuotation.php
│   │   │   │   ├── ApproveQuotation.php
│   │   │   │   ├── CreateInvoiceFromQuotation.php
│   │   │   │   ├── ProcessPakasirPayment.php
│   │   │   │   ├── VerifyManualPayment.php
│   │   │   │   └── CalculateProjectFinanceSplit.php
│   │   │   ├── Models/
│   │   │   │   ├── Quotation.php
│   │   │   │   ├── QuotationItem.php
│   │   │   │   ├── Invoice.php
│   │   │   │   ├── InvoiceItem.php
│   │   │   │   ├── Payment.php
│   │   │   │   ├── Transaction.php
│   │   │   │   ├── BankAccount.php
│   │   │   │   ├── ProjectFinanceSplit.php
│   │   │   │   ├── ProjectFinanceSplitItem.php
│   │   │   │   ├── Subscription.php
│   │   │   │   └── Vendor.php
│   │   │   └── Services/
│   │   │       ├── QuotationService.php
│   │   │       ├── InvoiceService.php
│   │   │       ├── PaymentService.php
│   │   │       ├── PakasirService.php
│   │   │       ├── FinanceSplitService.php
│   │   │       └── ReportService.php
│   │   │
│   │   ├── Automation/
│   │   │   ├── Models/
│   │   │   │   ├── AutomationWorkflow.php
│   │   │   │   └── AutomationLog.php
│   │   │   └── Services/
│   │   │       ├── N8nService.php
│   │   │       └── AutomationService.php
│   │   │
│   │   ├── Marketing/
│   │   │   ├── Models/
│   │   │   │   ├── SocialPost.php
│   │   │   │   └── EmailCampaign.php
│   │   │   └── Services/
│   │   │       ├── SocialMediaService.php
│   │   │       └── EmailCampaignService.php
│   │   │
│   │   ├── Communication/
│   │   │   ├── Models/
│   │   │   │   ├── Conversation.php
│   │   │   │   ├── Message.php
│   │   │   │   └── Notification.php
│   │   │   └── Services/
│   │   │       ├── ChatService.php
│   │   │       └── WhatsAppService.php
│   │   │
│   │   └── DigitalServices/
│   │       ├── Models/
│   │       │   ├── Website.php
│   │       │   ├── Domain.php
│   │       │   ├── Server.php
│   │       │   ├── Deployment.php
│   │       │   ├── Form.php
│   │       │   └── FormSubmission.php
│   │       └── Services/
│   │           ├── DeploymentService.php
│   │           └── DomainService.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── Dashboard/
│   │   │   ├── CRM/
│   │   │   │   ├── LeadController.php
│   │   │   │   ├── ClientController.php
│   │   │   │   ├── ContractController.php
│   │   │   │   └── SupportTicketController.php
│   │   │   ├── Project/
│   │   │   │   ├── ProjectController.php
│   │   │   │   ├── TaskController.php
│   │   │   │   ├── MeetingController.php
│   │   │   │   ├── NoteController.php
│   │   │   │   └── FileController.php
│   │   │   ├── Finance/
│   │   │   │   ├── QuotationController.php
│   │   │   │   ├── InvoiceController.php
│   │   │   │   ├── PaymentController.php
│   │   │   │   ├── TransactionController.php
│   │   │   │   └── FinanceSplitController.php
│   │   │   ├── Automation/
│   │   │   ├── Marketing/
│   │   │   ├── Communication/
│   │   │   ├── DigitalServices/
│   │   │   ├── System/
│   │   │   └── Webhook/
│   │   │       ├── PakasirWebhookController.php
│   │   │       └── N8nWebhookController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsureWorkspaceAccess.php
│   │   │   ├── SetWorkspaceContext.php
│   │   │   └── CheckPermission.php
│   │   │
│   │   └── Requests/
│   │       ├── CRM/
│   │       ├── Project/
│   │       ├── Finance/
│   │       └── ...
│   │
│   ├── Jobs/
│   │   ├── SendInvoiceReminderJob.php
│   │   ├── ProcessPakasirWebhookJob.php
│   │   ├── TriggerAutomationJob.php
│   │   └── SendWhatsAppMessageJob.php
│   │
│   ├── Events/
│   │   ├── LeadCreated.php
│   │   ├── InvoicePaid.php
│   │   ├── ProjectUpdated.php
│   │   └── MessageSent.php
│   │
│   ├── Listeners/
│   │   ├── CreateActivityFeedEntry.php
│   │   ├── TriggerLeadAutomation.php
│   │   └── CalculateFinanceSplit.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── EventServiceProvider.php
│
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Dashboard/
│   │   │   ├── CRM/
│   │   │   │   ├── Leads/
│   │   │   │   ├── Clients/
│   │   │   │   ├── Contracts/
│   │   │   │   └── SupportTickets/
│   │   │   ├── Project/
│   │   │   │   ├── Projects/
│   │   │   │   ├── Tasks/
│   │   │   │   ├── Meetings/
│   │   │   │   ├── Notes/
│   │   │   │   └── Files/
│   │   │   ├── Finance/
│   │   │   │   ├── Quotations/
│   │   │   │   ├── Invoices/
│   │   │   │   ├── Transactions/
│   │   │   │   └── Reports/
│   │   │   ├── Automation/
│   │   │   ├── Marketing/
│   │   │   ├── Communication/
│   │   │   ├── DigitalServices/
│   │   │   └── System/
│   │   │
│   │   ├── Components/
│   │   │   ├── UI/               ← Shadcn-Vue components
│   │   │   ├── Layout/
│   │   │   │   ├── AppLayout.vue
│   │   │   │   ├── Sidebar.vue
│   │   │   │   ├── Navbar.vue
│   │   │   │   └── WorkspaceSwitcher.vue
│   │   │   ├── Charts/
│   │   │   ├── Tables/
│   │   │   ├── Forms/
│   │   │   └── Shared/
│   │   │
│   │   └── Composables/
│   │       ├── useWorkspace.js
│   │       ├── usePermission.js
│   │       └── useRealtime.js
│   │
│   └── views/
│       └── app.blade.php
│
├── database/
│   ├── migrations/
│   │   ├── 2026_01_01_create_workspaces_table.php
│   │   ├── 2026_01_02_create_users_table.php
│   │   ├── 2026_01_03_create_roles_tables.php
│   │   ├── 2026_01_04_create_leads_tables.php
│   │   ├── 2026_01_05_create_clients_tables.php
│   │   ├── 2026_01_06_create_projects_tables.php
│   │   ├── 2026_01_07_create_finance_tables.php
│   │   └── ...
│   │
│   └── seeders/
│       ├── WorkspaceSeeder.php         ← Volora & Maven
│       ├── RoleSeeder.php
│       └── PermissionSeeder.php
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php                   ← WebSocket channels
│
└── config/
    ├── pakasir.php
    ├── n8n.php
    └── whatsapp.php
```

---

# BAB 6 — MINDMAP SISTEM

```
KANTOR DIGITAL
│
├── MAIN
│   ├── Dashboard (KPI, Charts, Quick Actions, Activity, Calendar Mini)
│   ├── Activity Feed (Audit + Social, Per Workspace)
│   └── Calendar (Month/Week/Day/Agenda, Semua Event Types)
│
├── CRM & CLIENT
│   ├── Leads (Multiple Pipeline, Kanban+Table, AI Score, n8n WA)
│   ├── Clients (Detail Tabs, Client Portal)
│   ├── Contracts (E-Sign, Auto Reminder)
│   └── Support Tickets (SLA, AI Reply, Multi-Channel)
│
├── PROJECT MANAGEMENT
│   ├── Projects (Template, Client Portal, Approval)
│   ├── Tasks (Nested, Gantt, Recurring, Time Track)
│   ├── Team (Workload View, Temporary Role)
│   ├── Meetings (→ Auto Tasks)
│   ├── Notes/SOP (Rich Text, Version, Template)
│   └── File Manager (Versioning, Approval, Share Link)
│
├── FINANCE
│   ├── Quotation/Proposal (Itemized, PDF, Client Approve)
│   ├── Invoices (Proforma, Credit Note, Pakasir, Recurring)
│   ├── Transactions (Income/Expense, Multi-Account)
│   ├── Subscriptions + Vendor Management
│   ├── Billing (Retainer, Auto Invoice)
│   ├── Payroll (Bagi Hasil Per Project)
│   │   ├── Biaya Operasional (flat)
│   │   ├── Kas Kantor (%)
│   │   └── Fee Tim (% / flat per posisi)
│   ├── Kas & Bank (Multi-Account, Rekonsiliasi)
│   ├── Expense & Reimbursement
│   └── Laporan (P&L, Cash Flow, Neraca, Pajak)
│
├── AUTOMATION & AI
│   ├── Automation (n8n, Visual Builder, Multi-Step, Conditional)
│   ├── AI Tools (Writing, Summary, Reply, Report, Lead Scoring)
│   ├── Integrations (WA, Google, Pakasir, Meta, dll)
│   └── API Keys (Scoped, IP Whitelist, Rate Limit)
│
├── DIGITAL SERVICES
│   ├── Website Manager (Uptime, SSL Monitor)
│   ├── Deployments (VPS + Cloud, Auto Deploy, Rollback)
│   ├── Domains (DNS, SSL, Auto Reminder)
│   ├── Hosting/VPS (Resource Monitor, SSH Keys)
│   └── Forms (Builder, → Lead, Embed)
│
├── MARKETING
│   ├── Overview (ROI, Performance Summary)
│   ├── Social Media Planner (Multi-Platform, Schedule, AI Caption)
│   ├── Email Campaigns (Blast, Drip, A/B Test)
│   └── Analytics (GA, Meta Ads, TikTok Ads, Funnel)
│
├── COMMUNICATION
│   ├── Chat/Inbox
│   │   ├── Internal (DM, Group Channel, Thread)
│   │   └── WA Inbox (Multi-Agent, Auto-Reply, Assign)
│   └── Notifications (Realtime, Preferences, Push, Email Digest)
│
└── SYSTEM
    ├── Role & Permissions (Custom, Inheritance, Temporary)
    ├── Workspace Settings (Per Tenant, Custom Domain)
    ├── Audit Logs (Realtime Stream, Alert, Export)
    ├── Security (2FA, SSO, Device Mgmt, GDPR)
    └── Help Center (Docs, Onboarding, Status Page)
```

---

# BAB 7 — ROADMAP DEVELOPMENT

## Phase 1 — Foundation

- Setup Laravel 13 + PostgreSQL + Inertia.js + Vue 3
- Auth (login, 2FA, SSO Google)
- Multi-workspace (Volora & Maven)
- Role & Permissions (Spatie)
- Layout & Design System (Sidebar, Navbar, Enterprise UI)
- Workspace Settings dasar
- Audit Logs

## Phase 2 — CRM & Client

- Leads (Multiple Pipeline, Kanban, Table)
- Clients (CRUD, Detail Tabs)
- Contracts
- Support Tickets
- Activity Feed
- Calendar dasar

## Phase 3 — Project Management

- Projects (Kanban, Template, Client Portal)
- Tasks (Nested, Gantt, Time Tracking)
- Team Management + Workload
- Meetings → Auto Tasks
- Notes/SOP
- File Manager + Versioning

## Phase 4 — Finance

- Quotation/Proposal (PDF, Client Approve)
- Invoices + Pakasir Integration
- Manual Transfer + Konfirmasi
- Transactions + Bank Accounts
- Project Finance Splitting
- Payroll & Fee Tim
- Laporan P&L, Cash Flow

## Phase 5 — Communication

- Internal Chat (Laravel Reverb WebSocket)
- WA Inbox (Multi-Agent)
- Notifications Realtime
- Broadcast WA

## Phase 6 — Automation & AI

- n8n Integration
- Automation Workflows
- Laravel AI SDK (Writing, Summary, Reply, Scoring)
- AI Tools

## Phase 7 — Digital Services & Marketing

- Website Manager + Uptime Monitor
- Domains + DNS
- Deployments (VPS + Cloud)
- Forms Builder → Lead
- Social Media Planner
- Email Campaigns
- Analytics Dashboard

## Phase 8 — Polish & Launch

- Security hardening (GDPR, Brute Force, Device Mgmt)
- Performance optimization
- Help Center + Onboarding
- Testing & QA
- Staging → Production deploy

---

_Dokumen ini dibuat berdasarkan hasil diskusi lengkap sistem Kantor Digital._
_Stack: Laravel 13 + PostgreSQL + Inertia.js + Vue 3 + n8n + WhatsApp Business API + Pakasir_
_Workspace: Volora & Maven_
