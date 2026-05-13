<template>
  <WorkspaceLayout
    title="Tagihan"
    subtitle="Pusat penagihan operasional: kelola draf sampai lunas, proforma, nota kredit, persetujuan internal, dan tautan pembayaran Pakasir."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openInvoiceModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Buat Tagihan Baru</span>
        </button>
        <button
          type="button"
          @click="exportSelectedInvoicePdf"
          :disabled="!selectedInvoice"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950 disabled:cursor-not-allowed disabled:opacity-50"
        >
          <Printer class="h-4 w-4" />
          <span>Cetak / PDF</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 16 / Tagihan</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Tagihan workspace yang rapi: mudah dicek, mudah dibayar, dan mudah ditagih saat jatuh tempo mendekat.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Persetujuan, pengiriman, pengingat, dan konfirmasi pembayaran tetap satu alur, tapi ringkasan di atas sekarang lebih hemat ruang.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tertunggak</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.outstanding_total_label }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.total_invoices }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terkirim</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.sent_invoices }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Lunas</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.paid_invoices }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-4">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Telat Bayar</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.overdue_invoices }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Menunggu Persetujuan</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.pending_internal_approval }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Proforma</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.proforma_invoices }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Berulang</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ invoiceSummary.recurring_invoices }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari tagihan berdasarkan nomor, klien, proyek, status, tipe, mata uang, atau metode pembayaran.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ invoiceItems.length }}</span> tagihan tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-6">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
            <select v-model="filterState.client" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Klien</option>
              <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
            <select v-model="filterState.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Tipe</option>
              <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Mata Uang</span>
            <select v-model="filterState.currency" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Mata Uang</option>
              <option v-for="currency in filterOptions.currencies" :key="currency.value" :value="currency.value">{{ currency.label }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
            <select v-model="filterState.project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Proyek</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pembayaran</span>
            <select v-model="filterState.payment_method" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Metode</option>
              <option v-for="method in filterOptions.paymentMethods" :key="method.value" :value="method.value">{{ method.label }}</option>
            </select>
          </label>
        </div>

        <div class="filter-actions mt-5 flex flex-wrap items-center gap-3">
          <button type="button" @click="applyFilters" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800">
            <Filter class="h-4 w-4" />
            <span>Terapkan Filter</span>
          </button>
          <button type="button" @click="resetFilters" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900">
            <RotateCcw class="h-4 w-4" />
            <span>Atur Ulang</span>
          </button>
        </div>
      </section>

      <section class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Daftar Tagihan</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua tagihan workspace, dari DP proforma sampai pelunasan akhir.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ invoiceItems.length }} dokumen
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="invoice in invoiceItems"
              :key="invoice.id"
              class="rounded-[1.6rem] border p-5 transition"
              :class="selectedInvoice?.id === invoice.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openInvoiceDetail(invoice.id)" class="text-left text-base font-semibold transition" :class="selectedInvoice?.id === invoice.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                      {{ invoice.number }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="invoiceTypeClass(invoice.type)">
                      {{ invoice.type_label }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="invoiceStatusClass(invoice.effective_status)">
                      {{ invoice.effective_status_label }}
                    </span>
                  </div>

                  <p class="mt-2 text-sm" :class="selectedInvoice?.id === invoice.id ? 'text-stone-300' : 'text-stone-500'">
                    {{ invoice.client?.name || 'Belum tertaut ke klien' }}<span v-if="invoice.project"> · {{ invoice.project.name }}</span>
                  </p>

                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedInvoice?.id === invoice.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ invoice.counts.items }} item</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedInvoice?.id === invoice.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ invoice.due_date_label || 'Belum ada tenggat' }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedInvoice?.id === invoice.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ invoice.currency }}</span>
                  </div>
                </div>

                <div class="text-right">
                  <p class="text-lg font-semibold" :class="selectedInvoice?.id === invoice.id ? 'text-white' : 'text-stone-950'">{{ invoice.total_label }}</p>
                  <p class="mt-1 text-sm" :class="selectedInvoice?.id === invoice.id ? 'text-stone-300' : 'text-stone-500'">Terbayar {{ invoice.paid_amount_label }}</p>
                </div>
              </div>

              <div class="mt-5">
                <div class="h-2 overflow-hidden rounded-full" :class="selectedInvoice?.id === invoice.id ? 'bg-white/10' : 'bg-stone-200'">
                  <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${invoice.payment_progress}%` }"></div>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs" :class="selectedInvoice?.id === invoice.id ? 'text-stone-300' : 'text-stone-500'">
                  <span>{{ invoice.payment_progress }}% lunas</span>
                  <span>sisa {{ invoice.outstanding_amount_label }}</span>
                </div>
              </div>

              <div class="mt-5 flex flex-wrap gap-2">
                <button type="button" @click.stop="openInvoiceDetail(invoice.id)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedInvoice?.id === invoice.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                  <FileText class="h-3.5 w-3.5" />
                  <span>Rincian</span>
                </button>
                <button type="button" @click.stop="openInvoiceModal(invoice)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedInvoice?.id === invoice.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                  <Pencil class="h-3.5 w-3.5" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click.stop="approveInvoice(invoice.id)" :disabled="Boolean(invoice.internal_approver)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-50" :class="selectedInvoice?.id === invoice.id ? 'border-emerald-300/30 text-emerald-200 hover:bg-emerald-500/10' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                  <BadgeCheck class="h-3.5 w-3.5" />
                  <span>Setujui</span>
                </button>
                <button type="button" @click.stop="sendInvoice(invoice.id)" :disabled="!invoice.can_send" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-50" :class="selectedInvoice?.id === invoice.id ? 'border-sky-300/30 text-sky-200 hover:bg-sky-500/10' : 'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100'">
                  <Send class="h-3.5 w-3.5" />
                  <span>Kirim</span>
                </button>
              </div>
            </article>

            <div v-if="invoiceItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada tagihan yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedInvoice">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Tagihan</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <h2 class="text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedInvoice.number }}</h2>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="invoiceTypeClass(selectedInvoice.type)">
                    {{ selectedInvoice.type_label }}
                  </span>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="invoiceStatusClass(selectedInvoice.effective_status)">
                    {{ selectedInvoice.effective_status_label }}
                  </span>
                </div>
                <p class="mt-2 text-sm text-stone-500">{{ selectedInvoice.client?.name || 'Belum tertaut ke klien' }}</p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openInvoiceModal(selectedInvoice)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="approveInvoice(selectedInvoice.id)" :disabled="Boolean(selectedInvoice.internal_approver)" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">
                  <ShieldCheck class="h-4 w-4" />
                  <span>{{ selectedInvoice.internal_approver ? 'Disetujui' : 'Setujui' }}</span>
                </button>
                <button type="button" @click="sendInvoice(selectedInvoice.id)" :disabled="!selectedInvoice.can_send" class="inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-50">
                  <Send class="h-4 w-4" />
                  <span>Kirim</span>
                </button>
                <button type="button" @click="openPaymentModal(selectedInvoice)" :disabled="!selectedInvoice.can_record_payment" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-50">
                  <CircleDollarSign class="h-4 w-4" />
                  <span>Konfirmasi Pembayaran</span>
                </button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="overflow-hidden rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)]">
                <div class="p-6 text-white" :style="brandAccentStyle">
                  <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                      <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-white/70">{{ brand.workspace_name }}</p>
                      <h3 class="mt-3 text-3xl font-semibold tracking-[-0.05em]">Pratinjau Tagihan Bermerek</h3>
                      <p class="mt-2 max-w-lg text-sm leading-6 text-white/80">
                        Templat ringkas untuk keuangan dan klien: nomor tagihan, jatuh tempo, rincian item, tautan pembayaran, dan catatan persetujuan.
                      </p>
                    </div>
                    <div class="rounded-[1.4rem] border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                      <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/70">Total</p>
                      <p class="mt-2 text-2xl font-semibold">{{ selectedInvoice.total_label }}</p>
                    </div>
                  </div>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-[1fr_auto]">
                  <div class="space-y-4">
                    <div class="grid gap-3 md:grid-cols-2">
                      <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Ditagihkan Ke</p>
                        <p class="mt-2 text-base font-semibold text-stone-950">{{ selectedInvoice.client?.name || 'Tanpa klien' }}</p>
                        <p class="mt-1 text-sm text-stone-500">{{ selectedInvoice.client?.pic_name || 'Tidak ada PIC tertaut' }}</p>
                      </div>
                      <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Meta Tagihan</p>
                        <p class="mt-2 text-sm text-stone-600">Jatuh Tempo {{ selectedInvoice.due_date_label || 'belum diatur' }}</p>
                        <p class="mt-1 text-sm text-stone-600">Mata Uang {{ selectedInvoice.currency }}</p>
                        <p class="mt-1 text-sm text-stone-600">Metode {{ selectedInvoice.payment_method_label }}</p>
                      </div>
                    </div>

                    <div class="rounded-[1.3rem] border border-stone-200 bg-white p-4">
                      <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Item</p>
                        <span class="rounded-full bg-stone-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ selectedInvoice.counts.items }} baris</span>
                      </div>

                      <div class="space-y-3">
                        <article v-for="item in selectedInvoice.items" :key="item.id || item.name" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                          <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                              <p class="text-sm font-semibold text-stone-950">{{ item.name }}</p>
                              <p class="mt-2 text-sm leading-6 text-stone-600">{{ item.description || 'Tidak ada deskripsi' }}</p>
                              <p class="mt-2 text-xs text-stone-500">{{ item.quantity }} x {{ item.unit_price_label }}</p>
                            </div>
                            <p class="text-sm font-semibold text-stone-950">{{ item.subtotal_label }}</p>
                          </div>
                        </article>
                      </div>
                    </div>
                  </div>

                  <div class="w-full max-w-xs rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Ringkasan</p>
                    <div class="mt-4 space-y-3">
                      <div class="flex items-center justify-between gap-3 text-sm text-stone-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-stone-950">{{ selectedInvoice.subtotal_label }}</span>
                      </div>
                      <div class="flex items-center justify-between gap-3 text-sm text-stone-600">
                        <span>Diskon</span>
                        <span class="font-semibold text-stone-950">{{ selectedInvoice.discount_amount_label }}</span>
                      </div>
                      <div class="flex items-center justify-between gap-3 text-sm text-stone-600">
                        <span>Pajak</span>
                        <span class="font-semibold text-stone-950">{{ selectedInvoice.tax_amount_label }}</span>
                      </div>
                      <div class="border-t border-stone-200 pt-3">
                        <div class="flex items-center justify-between gap-3 text-sm text-stone-600">
                          <span>Total</span>
                          <span class="text-lg font-semibold text-stone-950">{{ selectedInvoice.total_label }}</span>
                        </div>
                      </div>
                      <div class="rounded-[1.2rem] border border-white bg-white p-3">
                        <div class="flex items-center justify-between gap-3 text-sm text-stone-600">
                          <span>Lunas</span>
                          <span class="font-semibold text-emerald-700">{{ selectedInvoice.paid_amount_label }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-3 text-sm text-stone-600">
                          <span>Tertunggak</span>
                          <span class="font-semibold text-rose-700">{{ selectedInvoice.outstanding_amount_label }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </article>

              <div class="grid gap-6 2xl:grid-cols-[1fr_0.92fr]">
                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                      <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Konteks Relasi</p>
                        <p class="mt-1 text-sm text-stone-500">Jejak asal tagihan dari proyek, penawaran, atau kontrak.</p>
                      </div>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Proyek</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedInvoice.project?.name || 'Tidak tertaut' }}</p>
                      </div>
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Penawaran</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedInvoice.quotation?.number || 'Tidak tertaut' }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ selectedInvoice.quotation?.title || 'Tidak ada penawaran tertaut' }}</p>
                      </div>
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Kontrak</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedInvoice.contract?.title || 'Tidak tertaut' }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ selectedInvoice.contract?.status || 'Tidak ada kontrak tertaut' }}</p>
                      </div>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                      <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pembayaran & Penagihan</p>
                        <p class="mt-1 text-sm text-stone-500">Tautan Pakasir, transfer manual, dan histori konfirmasi pembayaran.</p>
                      </div>
                      <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="reminderStateClass(selectedInvoice.reminder_state)">
                        {{ reminderStateLabel(selectedInvoice.reminder_state) }}
                      </span>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Metode Utama</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedInvoice.payment_method_label }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ selectedInvoice.pakasir_order_id || 'Belum ada ID pesanan Pakasir' }}</p>
                      </div>
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Logika Pengingat</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ reminderCopy(selectedInvoice) }}</p>
                        <p class="mt-1 text-xs text-stone-500">Pengingat WA/email siap dipicu saat jatuh tempo mendekat.</p>
                      </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                      <button type="button" @click="openPaymentModal(selectedInvoice)" :disabled="!selectedInvoice.can_record_payment" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:cursor-not-allowed disabled:opacity-50">
                        <Banknote class="h-4 w-4" />
                        <span>Konfirmasi Transfer Manual</span>
                      </button>
                      <button v-if="!selectedInvoice.pakasir_payment_url" type="button" @click="generatePakasirLink(selectedInvoice)" class="inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                        <Zap class="h-4 w-4" />
                        <span>Buat Link Pakasir</span>
                      </button>
                      <button v-else type="button" @click="copyPaymentLink(selectedInvoice)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                        <Copy class="h-4 w-4" />
                        <span>Salin Link Bayar</span>
                      </button>
                      <a v-if="selectedInvoice.pakasir_payment_url" :href="selectedInvoice.pakasir_payment_url" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                        <Link2 class="h-4 w-4" />
                        <span>Buka Pakasir</span>
                      </a>
                    </div>

                    <div class="mt-5 space-y-3">
                      <article v-for="payment in selectedInvoice.payments" :key="payment.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                          <div>
                            <div class="flex flex-wrap items-center gap-2">
                              <p class="text-sm font-semibold text-stone-950">{{ payment.amount_label }}</p>
                              <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ payment.method_label }}</span>
                              <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">{{ payment.status_label }}</span>
                            </div>
                            <p class="mt-2 text-sm text-stone-600">{{ payment.notes || 'Tidak ada catatan' }}</p>
                          </div>
                          <div class="text-right text-xs text-stone-500">
                            <p>{{ payment.paid_at_label || '-' }}</p>
                            <p class="mt-1">Diverifikasi oleh {{ payment.verifier?.name || 'sistem' }}</p>
                          </div>
                        </div>
                      </article>

                      <div v-if="selectedInvoice.payments.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-6 text-sm text-stone-500">
                        Belum ada konfirmasi pembayaran untuk tagihan ini.
                      </div>
                    </div>
                  </article>
                </section>

                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Persetujuan & Waktu</p>
                    <div class="mt-4 space-y-3">
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                          <div>
                            <p class="text-sm font-semibold text-stone-950">Persetujuan internal</p>
                            <p class="mt-1 text-sm text-stone-500">{{ selectedInvoice.internal_approver ? `Disetujui oleh ${selectedInvoice.internal_approver.name}` : 'Belum ada persetujuan internal' }}</p>
                          </div>
                          <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="selectedInvoice.internal_approver ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                            {{ selectedInvoice.internal_approver ? 'disetujui' : 'menunggu' }}
                          </span>
                        </div>
                        <p class="mt-2 text-xs text-stone-500">{{ selectedInvoice.internal_approved_at_label || 'Belum ada waktu persetujuan' }}</p>
                      </div>

                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-950">Linimasa pengiriman</p>
                        <div class="mt-3 space-y-2 text-sm text-stone-600">
                          <p>Terkirim pada: {{ selectedInvoice.sent_at_label || 'Belum dikirim' }}</p>
                          <p>Lunas pada: {{ selectedInvoice.paid_at_label || 'Belum lunas' }}</p>
                          <p>Jatuh Tempo: {{ selectedInvoice.due_date_label || 'Belum diatur' }}</p>
                        </div>
                      </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                      <button type="button" @click="markOverdue(selectedInvoice.id)" :disabled="selectedInvoice.effective_status === 'paid'" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50">
                        <AlertTriangle class="h-4 w-4" />
                        <span>Tandai Telat Bayar</span>
                      </button>
                      <button type="button" @click="exportSelectedInvoicePdf" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                        <Printer class="h-4 w-4" />
                        <span>Ekspor PDF</span>
                      </button>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan Berulang & Tipe</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Berulang</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedInvoice.is_recurring ? selectedInvoice.recurrence_rule_label : 'Tagihan sekali' }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ selectedInvoice.is_recurring ? 'Retainer dan penagihan berkala siap dilacak.' : 'Tidak ada penagihan berkala.' }}</p>
                      </div>
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Tipe Dokumen</p>
                        <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedInvoice.type_label }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ typeDescription(selectedInvoice.type) }}</p>
                      </div>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">
                      {{ selectedInvoice.notes || 'Belum ada catatan tambahan untuk tagihan ini.' }}
                    </div>
                  </article>

                  <div class="flex flex-wrap gap-3">
                    <button type="button" @click="openInvoiceModal(selectedInvoice)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                      <span>Ubah Tagihan</span>
                    </button>
                    <button type="button" @click="deleteInvoice(selectedInvoice.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                      <Trash2 class="h-4 w-4" />
                      <span>Hapus</span>
                    </button>
                  </div>
                </section>
              </div>
            </div>
          </template>

          <div v-else class="flex min-h-[420px] items-center justify-center rounded-[1.8rem] border border-dashed border-stone-200 bg-stone-50 p-8 text-center">
            <div>
              <p class="text-lg font-semibold text-stone-950">Belum ada tagihan terpilih.</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Pilih tagihan dari daftar sebelah kiri, atau buat tagihan baru untuk mulai alur kerja keuangan menu 16.</p>
            </div>
          </div>
        </article>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showInvoiceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Formulir Tagihan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingInvoice ? 'Ubah Tagihan' : 'Buat Tagihan' }}</h3>
            </div>
            <button type="button" @click="closeInvoiceModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitInvoice">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="invoiceForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa klien</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
                <select v-model="invoiceForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa proyek</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Penawaran</span>
                <select v-model="invoiceForm.quotation_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa penawaran</option>
                  <option v-for="quotation in filterOptions.quotations" :key="quotation.id" :value="quotation.id">{{ quotation.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kontrak</span>
                <select v-model="invoiceForm.contract_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa kontrak</option>
                  <option v-for="contract in filterOptions.contracts" :key="contract.id" :value="contract.id">{{ contract.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
                <select v-model="invoiceForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Mata Uang</span>
                <select v-model="invoiceForm.currency" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="currency in filterOptions.currencies" :key="currency.value" :value="currency.value">{{ currency.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jatuh Tempo</span>
                <input v-model="invoiceForm.due_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Metode Pembayaran</span>
                <select v-model="invoiceForm.payment_method" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Belum diatur</option>
                  <option v-for="method in filterOptions.paymentMethods" :key="method.value" :value="method.value">{{ method.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Diskon</span>
                <input v-model.number="invoiceForm.discount_amount" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tarif Pajak</span>
                <input v-model.number="invoiceForm.tax_rate" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">URL Pembayaran Pakasir</span>
                <input v-model="invoiceForm.pakasir_payment_url" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p class="text-xs text-stone-500">Isi tautan pembayaran aktif jika tagihan dibayar melalui Pakasir.</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">ID Pesanan Pakasir</span>
                <input v-model="invoiceForm.pakasir_order_id" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
              <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Item Tagihan</p>
                    <p class="mt-1 text-sm text-stone-500">Rincian biaya untuk pengembangan, retainer, atau koreksi nota kredit.</p>
                  </div>
                  <button type="button" @click="addItem" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                    <Plus class="h-4 w-4" />
                    <span>Tambah Item</span>
                  </button>
                </div>

                <div class="space-y-4">
                  <article v-for="(item, index) in invoiceForm.items" :key="`item-${index}`" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="grid gap-4 md:grid-cols-2">
                      <label class="space-y-2 text-sm md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nama Item</span>
                        <input v-model="item.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                      </label>
                      <label class="space-y-2 text-sm md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Deskripsi</span>
                        <textarea v-model="item.description" rows="3" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400"></textarea>
                      </label>
                      <label class="space-y-2 text-sm">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Jumlah</span>
                        <input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                      </label>
                      <label class="space-y-2 text-sm">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Harga Satuan</span>
                        <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                      </label>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                      <p class="text-sm font-semibold text-stone-950">{{ formatCurrency(itemSubtotal(item), invoiceForm.currency) }}</p>
                      <button type="button" @click="removeItem(index)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                        <Trash2 class="h-3.5 w-3.5" />
                        <span>Hapus</span>
                      </button>
                    </div>
                  </article>
                </div>
              </section>

              <section class="space-y-4">
                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengaturan Berulang</p>
                  <div class="mt-4 space-y-4">
                    <label class="flex items-center gap-3 rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3">
                      <input v-model="invoiceForm.is_recurring" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" />
                      <span class="text-sm font-medium text-stone-700">Tagihan berulang / retainer</span>
                    </label>

                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Aturan</span>
                      <select v-model="invoiceForm.recurrence_rule" :disabled="!invoiceForm.is_recurring" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Pilih aturan</option>
                        <option v-for="rule in filterOptions.recurrenceRules" :key="rule.value" :value="rule.value">{{ rule.label }}</option>
                      </select>
                    </label>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</p>
                  <textarea v-model="invoiceForm.notes" rows="6" class="mt-4 w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-950 p-5 text-white">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-300">Ringkasan Langsung</p>
                  <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Subtotal</span>
                      <span class="font-semibold text-white">{{ formatCurrency(formSummary.subtotal, invoiceForm.currency) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Pajak</span>
                      <span class="font-semibold text-white">{{ formatCurrency(formSummary.taxAmount, invoiceForm.currency) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Total</span>
                      <span class="text-lg font-semibold text-white">{{ formatCurrency(formSummary.total, invoiceForm.currency) }}</span>
                    </div>
                  </div>
                </article>
              </section>
            </div>

            <div v-if="Object.keys(invoiceForm.errors).length" class="rounded-[1.4rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
              Lengkapi kolom wajib pada formulir tagihan sebelum disimpan.
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeInvoiceModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800" :disabled="invoiceForm.processing">
                <Sparkles class="h-4 w-4" />
                <span>{{ isEditingInvoice ? 'Simpan Tagihan' : 'Buat Tagihan' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Konfirmasi Pembayaran</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ paymentTarget?.number || 'Pembayaran Tagihan' }}</h3>
            </div>
            <button type="button" @click="closePaymentModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-4" @submit.prevent="submitPayment">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Jumlah</span>
              <input v-model.number="paymentForm.amount" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Metode</span>
                <select v-model="paymentForm.method" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="method in filterOptions.paymentMethods" :key="method.value" :value="method.value">{{ method.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tanggal Lunas</span>
                <input v-model="paymentForm.paid_at" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Catatan</span>
              <textarea v-model="paymentForm.notes" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
              Konfirmasi pembayaran akan membuat rekaman pembayaran dan transaksi pendapatan untuk tagihan ini.
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closePaymentModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800" :disabled="paymentForm.processing">
                <CircleDollarSign class="h-4 w-4" />
                <span>Simpan Pembayaran</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
    </FinanceLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  AlertTriangle,
  BadgeCheck,
  Banknote,
  CircleDollarSign,
  Copy,
  FileText,
  Filter,
  Link2,
  Pencil,
  Plus,
  Printer,
  RotateCcw,
  Send,
  ShieldCheck,
  Sparkles,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  invoices: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const invoicesBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/invoices`

const localInvoices = ref(cloneInvoices(props.invoices.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedInvoiceId = ref(props.invoices.selected_id || props.invoices.items?.[0]?.id || null)
const showInvoiceModal = ref(false)
const showPaymentModal = ref(false)
const editingInvoiceId = ref(null)
const paymentTargetId = ref(null)

const invoiceForm = useForm({
  client_id: '',
  project_id: '',
  quotation_id: '',
  contract_id: '',
  type: 'invoice',
  currency: props.invoices.brand?.currency || 'IDR',
  due_date: todayDate(14),
  discount_amount: 0,
  tax_rate: 11,
  is_recurring: false,
  recurrence_rule: '',
  payment_method: '',
  pakasir_order_id: '',
  pakasir_payment_url: '',
  notes: '',
  items: [blankItem()],
})

const paymentForm = useForm({
  amount: 0,
  method: 'manual_transfer',
  paid_at: todayDate(),
  notes: '',
})

const brand = computed(() => props.invoices.brand || {
  workspace_name: props.workspace.name,
  workspace_slug: props.workspace.slug,
  currency: 'IDR',
  primary_color: '#1c1917',
})
const invoiceSummary = computed(() => props.invoices.summary)
const invoiceItems = computed(() => localInvoices.value)
const selectedInvoice = computed(() => invoiceItems.value.find((invoice) => invoice.id === selectedInvoiceId.value) || invoiceItems.value[0] || null)
const paymentTarget = computed(() => invoiceItems.value.find((invoice) => invoice.id === paymentTargetId.value) || null)
const isEditingInvoice = computed(() => Boolean(editingInvoiceId.value))
const brandAccentStyle = computed(() => ({
  background: `linear-gradient(135deg, ${brand.value.primary_color || '#1c1917'} 0%, #1c1917 100%)`,
}))
const formSummary = computed(() => {
  const subtotal = invoiceForm.items.reduce((total, item) => total + itemSubtotal(item), 0)
  const discount = Number(invoiceForm.discount_amount || 0)
  const taxable = Math.max(0, subtotal - discount)
  const taxAmount = taxable * (Number(invoiceForm.tax_rate || 0) / 100)
  const total = taxable + taxAmount

  return {
    subtotal,
    taxAmount,
    total,
  }
})

watch(
  () => props.invoices.items,
  (items) => {
    localInvoices.value = cloneInvoices(items || [])

    if (!selectedInvoiceId.value || !localInvoices.value.some((invoice) => invoice.id === selectedInvoiceId.value)) {
      selectedInvoiceId.value = localInvoices.value[0]?.id || null
    }
  },
)

watch(
  () => props.filters,
  (filters) => {
    filterState.value = buildFilterState(filters)
  },
  { deep: true },
)

watch(
  () => props.invoices.selected_id,
  (invoiceId) => {
    if (invoiceId) {
      selectedInvoiceId.value = invoiceId
    }
  },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    client: filters.client ?? '',
    project: filters.project ?? '',
    status: filters.status ?? '',
    type: filters.type ?? '',
    currency: filters.currency ?? '',
    payment_method: filters.payment_method ?? '',
  }
}

function applyFilters() {
  router.get(invoicesBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(invoicesBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openInvoiceDetail(invoiceId) {
  selectedInvoiceId.value = invoiceId
}

function openInvoiceModal(invoice = null) {
  editingInvoiceId.value = invoice?.id || null
  invoiceForm.reset()
  invoiceForm.clearErrors()
  invoiceForm.client_id = invoice?.client_id || ''
  invoiceForm.project_id = invoice?.project_id || ''
  invoiceForm.quotation_id = invoice?.quotation_id || ''
  invoiceForm.contract_id = invoice?.contract_id || ''
  invoiceForm.type = invoice?.type || 'invoice'
  invoiceForm.currency = invoice?.currency || props.workspace.currency || 'IDR'
  invoiceForm.due_date = invoice?.due_date || todayDate(14)
  invoiceForm.discount_amount = invoice?.discount_amount ?? 0
  invoiceForm.tax_rate = invoice?.tax_rate ?? 11
  invoiceForm.is_recurring = Boolean(invoice?.is_recurring)
  invoiceForm.recurrence_rule = invoice?.recurrence_rule || ''
  invoiceForm.payment_method = invoice?.payment_method || ''
  invoiceForm.pakasir_order_id = invoice?.pakasir_order_id || ''
  invoiceForm.pakasir_payment_url = invoice?.pakasir_payment_url || ''
  invoiceForm.notes = invoice?.notes || ''
  invoiceForm.items = invoice?.items?.length
    ? invoice.items.map((item) => ({
        name: item.name,
        description: item.description || '',
        quantity: Number(item.quantity || 1),
        unit_price: Number(item.unit_price || 0),
      }))
    : [blankItem()]
  showInvoiceModal.value = true
}

onMounted(() => {
  const query = new URLSearchParams(window.location.search)
  const openModal = query.get('open_modal')
  const clientId = query.get('client_id')

  if (openModal === 'invoice') {
    openInvoiceModal()
    if (clientId) {
      invoiceForm.client_id = clientId
    }
  }
})

function closeInvoiceModal() {
  showInvoiceModal.value = false
  editingInvoiceId.value = null
  invoiceForm.reset()
  invoiceForm.clearErrors()
  invoiceForm.type = 'invoice'
  invoiceForm.currency = brand.value.currency || 'IDR'
  invoiceForm.due_date = todayDate(14)
  invoiceForm.discount_amount = 0
  invoiceForm.tax_rate = 11
  invoiceForm.is_recurring = false
  invoiceForm.recurrence_rule = ''
  invoiceForm.items = [blankItem()]
}

function submitInvoice() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeInvoiceModal(),
  }

  if (isEditingInvoice.value) {
    invoiceForm.patch(`${invoicesBaseUrl}/${encodeURIComponent(editingInvoiceId.value)}`, options)
    return
  }

  invoiceForm.post(invoicesBaseUrl, options)
}

function deleteInvoice(invoiceId) {
  if (!confirm('Hapus tagihan ini?')) {
    return
  }

  router.delete(`${invoicesBaseUrl}/${encodeURIComponent(invoiceId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedInvoiceId.value === invoiceId) {
        selectedInvoiceId.value = invoiceItems.value.find((invoice) => invoice.id !== invoiceId)?.id || null
      }
    },
  })
}

function approveInvoice(invoiceId) {
  router.post(`${invoicesBaseUrl}/${encodeURIComponent(invoiceId)}/approve`, {}, {
    preserveScroll: true,
  })
}

function sendInvoice(invoiceId) {
  router.patch(`${invoicesBaseUrl}/${encodeURIComponent(invoiceId)}/status`, {
    status: 'sent',
  }, {
    preserveScroll: true,
  })
}

function markOverdue(invoiceId) {
  router.patch(`${invoicesBaseUrl}/${encodeURIComponent(invoiceId)}/status`, {
    status: 'overdue',
  }, {
    preserveScroll: true,
  })
}

function openPaymentModal(invoice) {
  paymentTargetId.value = invoice?.id || null
  paymentForm.reset()
  paymentForm.clearErrors()
  paymentForm.amount = invoice?.outstanding_amount || 0
  paymentForm.method = invoice?.payment_method || 'manual_transfer'
  paymentForm.paid_at = todayDate()
  paymentForm.notes = ''
  showPaymentModal.value = true
}

function closePaymentModal() {
  showPaymentModal.value = false
  paymentTargetId.value = null
  paymentForm.reset()
  paymentForm.clearErrors()
  paymentForm.method = 'manual_transfer'
  paymentForm.paid_at = todayDate()
}

function submitPayment() {
  if (!paymentTargetId.value) {
    return
  }

  paymentForm.post(`${invoicesBaseUrl}/${encodeURIComponent(paymentTargetId.value)}/payments`, {
    preserveScroll: true,
    onSuccess: () => closePaymentModal(),
  })
}

async function copyPaymentLink(invoice) {
  if (!invoice?.pakasir_payment_url) {
    return
  }

  try {
    await navigator.clipboard.writeText(invoice.pakasir_payment_url)
    alert('Tautan pembayaran disalin ke clipboard!')
  } catch {
    // Ignore clipboard failure.
  }
}

function generatePakasirLink(invoice) {
  if (!confirm('Buat tautan pembayaran otomatis melalui Pakasir?')) return

  router.post(`/w/${encodeURIComponent(props.workspace.slug)}/finance/invoices/${invoice.id}/pakasir-link`, {}, {
    preserveScroll: true,
  })
}

function exportSelectedInvoicePdf() {
  if (!selectedInvoice.value) {
    return
  }

  window.print()
}

function addItem() {
  invoiceForm.items.push(blankItem())
}

function removeItem(index) {
  if (invoiceForm.items.length === 1) {
    invoiceForm.items = [blankItem()]
    return
  }

  invoiceForm.items.splice(index, 1)
}

function itemSubtotal(item) {
  return Math.max(0, Number(item.quantity || 0) * Number(item.unit_price || 0))
}

function formatCurrency(value, currency = 'IDR') {
  try {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency || 'IDR',
      maximumFractionDigits: 0,
    }).format(value || 0)
  } catch {
    return `${Number(value || 0).toLocaleString('id-ID')} ${currency || 'IDR'}`
  }
}

function invoiceStatusClass(status) {
  const map = {
    draft: 'bg-stone-100 text-stone-700',
    sent: 'bg-sky-100 text-sky-700',
    partial: 'bg-amber-100 text-amber-700',
    paid: 'bg-emerald-100 text-emerald-700',
    overdue: 'bg-rose-100 text-rose-700',
  }

  return map[status] || 'bg-stone-100 text-stone-700'
}

function invoiceTypeClass(type) {
  const map = {
    invoice: 'bg-stone-100 text-stone-700',
    proforma: 'bg-violet-100 text-violet-700',
    credit_note: 'bg-amber-100 text-amber-700',
  }

  return map[type] || 'bg-stone-100 text-stone-700'
}

function reminderStateClass(state) {
  const map = {
    completed: 'bg-emerald-100 text-emerald-700',
    scheduled: 'bg-sky-100 text-sky-700',
    ready: 'bg-amber-100 text-amber-700',
    urgent: 'bg-rose-100 text-rose-700',
    unscheduled: 'bg-stone-100 text-stone-700',
  }

  return map[state] || 'bg-stone-100 text-stone-700'
}

function reminderStateLabel(state) {
  const map = {
    completed: 'lunas',
    scheduled: 'terjadwal',
    ready: 'segera diingatkan',
    urgent: 'mendesak',
    unscheduled: 'tanpa jatuh tempo',
  }

  return map[state] || 'terjadwal'
}

function reminderCopy(invoice) {
  const days = invoice?.days_to_due

  if (invoice?.reminder_state === 'completed') {
    return 'Tagihan sudah lunas, pengingat dihentikan.'
  }

  if (invoice?.reminder_state === 'urgent') {
    return 'Tagihan sudah lewat jatuh tempo. Pengingat WA/email perlu diprioritaskan.'
  }

  if (invoice?.reminder_state === 'ready') {
    return `Jatuh tempo tinggal ${Math.max(0, Number(days || 0))} hari lagi.`
  }

  if (invoice?.reminder_state === 'unscheduled') {
    return 'Jatuh tempo belum diatur, pengingat otomatis belum bisa dijadwalkan.'
  }

  return 'Pengingat otomatis bisa disiapkan sebelum jatuh tempo.'
}

function typeDescription(type) {
  const map = {
    invoice: 'Tagihan final atau tahapan reguler untuk klien.',
    proforma: 'Tagihan DP / pra-penagihan sebelum tagihan akhir.',
    credit_note: 'Dokumen pengembalian dana atau koreksi nominal tagihan.',
  }

  return map[type] || 'Dokumen tagihan.'
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function todayDate(offsetDays = 0) {
  const date = new Date()
  date.setDate(date.getDate() + offsetDays)

  return date.toISOString().slice(0, 10)
}

function blankItem() {
  return {
    name: '',
    description: '',
    quantity: 1,
    unit_price: 0,
  }
}

function cloneInvoices(items) {
  return items.map((invoice) => ({
    ...invoice,
    client: invoice.client ? { ...invoice.client } : null,
    project: invoice.project ? { ...invoice.project } : null,
    quotation: invoice.quotation ? { ...invoice.quotation } : null,
    contract: invoice.contract ? { ...invoice.contract } : null,
    creator: invoice.creator ? { ...invoice.creator } : null,
    internal_approver: invoice.internal_approver ? { ...invoice.internal_approver } : null,
    counts: invoice.counts ? { ...invoice.counts } : {},
    items: Array.isArray(invoice.items) ? invoice.items.map((item) => ({ ...item })) : [],
    payments: Array.isArray(invoice.payments) ? invoice.payments.map((payment) => ({ ...payment, verifier: payment.verifier ? { ...payment.verifier } : null })) : [],
  }))
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.96);
}
</style>
