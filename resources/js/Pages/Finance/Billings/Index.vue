<template>
  <WorkspaceLayout
    title="Penagihan"
    subtitle="Menu 19 difokuskan untuk jadwal penagihan per klien, retainer atau berbasis project, pembuatan invoice dari siklus, dan histori pembayaran tanpa muter ke menu lain."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="generateDueInvoices"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <Clock3 class="h-4 w-4" />
          <span>Buat Invoice Jatuh Tempo</span>
        </button>
        <button
          type="button"
          @click="openBillingModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Penagihan Baru</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 19 / Penagihan</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Atur penagihan per klien dengan siklus yang jelas, pembuatan invoice yang rapi, dan histori pembayaran yang mudah dibaca.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Rencana penagihan, jadwal jatuh tempo, dan pembuat invoice tetap lengkap, tapi pembuka dibuat lebih tipis supaya daftar penagihan lebih fokus.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Laju Bulanan</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.monthly_run_rate_total_label }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.total_billings }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.active_billings }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Jatuh Tempo Bulan Ini</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.due_this_month_billings }}</p></div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-4">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terlambat</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.overdue_billings }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Invoice Dibuat</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.invoices_generated }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terkumpul</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ billingSummary.collected_total_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Invoice Berikutnya</p><p class="mt-2 text-sm font-semibold text-stone-950">{{ billingSummary.upcoming_invoice_label }}</p></div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari penagihan berdasarkan nama paket, klien, project, tipe, status, siklus, atau status jadwal invoice.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ billingItems.length }}</span> paket penagihan tampil
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
            <select v-model="filterState.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Tipe</option>
              <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Siklus Penagihan</span>
            <select v-model="filterState.billing_cycle" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Siklus</option>
              <option v-for="cycle in filterOptions.billingCycles" :key="cycle.value" :value="cycle.value">{{ cycle.label }}</option>
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status Jadwal</span>
            <select v-model="filterState.schedule_state" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="state in filterOptions.scheduleStates" :key="state.value" :value="state.value">{{ state.label }}</option>
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

      <section class="grid gap-4 xl:grid-cols-[0.92fr_1.08fr]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Penagihan</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua paket penagihan klien yang jadi sumber tagihan berkala.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ billingItems.length }} paket
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="billing in billingItems"
              :key="billing.id"
              class="rounded-[1.6rem] border p-5 transition"
              :class="selectedBilling?.id === billing.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="selectBilling(billing.id)" class="text-left text-base font-semibold transition" :class="selectedBilling?.id === billing.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                      {{ billing.name }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="typeClass(billing.type)">
                      {{ billing.type_label }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(billing.status)">
                      {{ billing.status_label }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="scheduleStateClass(billing.schedule_state)">
                      {{ billing.schedule_state_label }}
                    </span>
                  </div>
                  <p class="mt-2 text-sm" :class="selectedBilling?.id === billing.id ? 'text-stone-300' : 'text-stone-500'">
                    {{ billing.client?.name || 'Belum tertaut ke klien' }}
                    <span v-if="billing.project?.name"> | {{ billing.project.name }}</span>
                  </p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedBilling?.id === billing.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ billing.billing_cycle_label }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedBilling?.id === billing.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ billing.next_invoice_date_label || 'Belum ada tanggal invoice' }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedBilling?.id === billing.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ billing.amount_label }}</span>
                  </div>
                </div>

                <div class="text-right">
                  <p class="text-lg font-semibold" :class="selectedBilling?.id === billing.id ? 'text-white' : 'text-stone-950'">{{ billing.monthly_equivalent_label }}</p>
                  <p class="mt-1 text-sm" :class="selectedBilling?.id === billing.id ? 'text-stone-300' : 'text-stone-500'">Setara bulanan</p>
                </div>
              </div>

              <div class="mt-5 flex flex-wrap gap-2">
                <button type="button" @click="selectBilling(billing.id)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedBilling?.id === billing.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                  <Eye class="h-3.5 w-3.5" />
                  <span>Detail</span>
                </button>
                <button type="button" @click="openBillingModal(billing)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedBilling?.id === billing.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                  <Pencil class="h-3.5 w-3.5" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="generateInvoice(billing.id)" :disabled="!canGenerate(billing)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-50" :class="selectedBilling?.id === billing.id ? 'border-emerald-300/30 text-emerald-200 hover:bg-emerald-500/10' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                  <Sparkles class="h-3.5 w-3.5" />
                  <span>Buat Invoice</span>
                </button>
              </div>
            </article>

            <div v-if="billingItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada billing yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedBilling">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Penagihan</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <h2 class="text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedBilling.name }}</h2>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="typeClass(selectedBilling.type)">
                    {{ selectedBilling.type_label }}
                  </span>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="statusClass(selectedBilling.status)">
                    {{ selectedBilling.status_label }}
                  </span>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="scheduleStateClass(selectedBilling.schedule_state)">
                    {{ selectedBilling.schedule_state_label }}
                  </span>
                </div>
                <p class="mt-2 text-sm text-stone-500">{{ selectedBilling.client?.name || 'Belum tertaut ke klien' }}</p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openBillingModal(selectedBilling)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="generateInvoice(selectedBilling.id)" :disabled="!canGenerate(selectedBilling)" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">
                  <CheckCircle2 class="h-4 w-4" />
                  <span>Buat Invoice</span>
                </button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)] p-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Cycle</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedBilling.billing_cycle_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Jumlah</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedBilling.amount_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Invoice Berikutnya</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedBilling.next_invoice_date_label || 'Belum ada tanggal' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Dibuat</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedBilling.invoices_generated_count }} invoice</p>
                  </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                  <div class="rounded-[1.3rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Terkumpul</p>
                    <p class="mt-2 text-xl font-semibold text-stone-950">{{ selectedBilling.collected_total_label }}</p>
                  </div>
                  <div class="rounded-[1.3rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Monthly Equivalent</p>
                    <p class="mt-2 text-xl font-semibold text-stone-950">{{ selectedBilling.monthly_equivalent_label }}</p>
                  </div>
                </div>
              </article>

              <div class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien & Project</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <p class="text-base font-semibold text-stone-950">{{ selectedBilling.client?.name || 'Belum tertaut ke klien' }}</p>
                      <div class="mt-3 space-y-2 text-sm text-stone-600">
                        <p>PIC: {{ selectedBilling.client?.pic_name || '-' }}</p>
                        <p>Email: {{ selectedBilling.client?.email || '-' }}</p>
                        <p>Telepon: {{ selectedBilling.client?.phone || '-' }}</p>
                        <p>Proyek: {{ selectedBilling.project?.name || 'Belum tertaut ke proyek' }}</p>
                        <p>Tanggal Mulai: {{ selectedBilling.start_date_label || '-' }}</p>
                      </div>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">
                      {{ selectedBilling.notes || 'Belum ada catatan billing untuk client ini.' }}
                    </div>
                  </article>
                </section>

                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Invoice Terbaru</p>
                    <div class="mt-4 space-y-3">
                      <article v-for="invoice in selectedBilling.recent_invoices" :key="invoice.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                          <div>
                            <p class="text-sm font-semibold text-stone-950">{{ invoice.number }}</p>
                            <p class="mt-1 text-xs text-stone-500">{{ invoice.due_date_label || 'Belum ada jatuh tempo' }}</p>
                          </div>
                          <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-600">
                            {{ invoice.status_label }}
                          </span>
                        </div>
                        <div class="mt-3 grid gap-2 text-sm text-stone-600">
                          <p>Total: {{ invoice.total_label }}</p>
                          <p>Terbayar: {{ invoice.paid_amount_label }}</p>
                          <p>Pembayaran: {{ invoice.payment_count }}</p>
                        </div>
                      </article>
                      <div v-if="selectedBilling.recent_invoices.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                        Belum ada invoice yang dihasilkan dari billing ini.
                      </div>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Histori Pembayaran</p>
                    <div class="mt-4 space-y-3">
                      <article v-for="payment in selectedBilling.payment_history" :key="payment.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                          <div>
                            <p class="text-sm font-semibold text-stone-950">{{ payment.invoice_number }}</p>
                            <p class="mt-1 text-xs text-stone-500">{{ payment.paid_at_label || '-' }}</p>
                          </div>
                          <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-600">
                            {{ payment.method_label }}
                          </span>
                        </div>
                        <div class="mt-3 grid gap-2 text-sm text-stone-600">
                          <p>Jumlah: {{ payment.amount_label }}</p>
                          <p>Status: {{ payment.status_label }}</p>
                          <p>Pemeriksa: {{ payment.verifier_name || '-' }}</p>
                        </div>
                      </article>
                      <div v-if="selectedBilling.payment_history.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                        Belum ada histori pembayaran untuk billing/client ini.
                      </div>
                    </div>
                  </article>
                </section>
              </div>

              <div class="flex flex-wrap gap-3">
                <button type="button" @click="openBillingModal(selectedBilling)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Edit Penagihan</span>
                </button>
                <button type="button" @click="deleteBilling(selectedBilling.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                  <Trash2 class="h-4 w-4" />
                <span>Hapus</span>
                </button>
              </div>
            </div>
          </template>

          <div v-else class="flex min-h-[420px] items-center justify-center rounded-[1.8rem] border border-dashed border-stone-200 bg-stone-50 p-8 text-center">
            <div>
              <p class="text-lg font-semibold text-stone-950">Belum ada penagihan terpilih.</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Pilih paket penagihan dari daftar sebelah kiri atau buat penagihan baru untuk mulai isi menu 19.</p>
            </div>
          </div>
        </article>
      </section>
      </div>
      <Transition name="modal">
      <div v-if="showBillingModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Formulir Penagihan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingBilling ? 'Ubah Penagihan' : 'Buat Penagihan' }}</h3>
            </div>
            <button type="button" @click="closeBillingModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitBilling">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Penagihan</span>
                <input v-model="billingForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="billingForm.errors.name" class="text-xs text-rose-500">{{ billingForm.errors.name }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="billingForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Pilih klien</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
                <p v-if="billingForm.errors.client_id" class="text-xs text-rose-500">{{ billingForm.errors.client_id }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
                <select v-model="billingForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa project</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
                <select v-model="billingForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span>
                <input v-model.number="billingForm.amount" type="number" min="0" step="1000" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="billingForm.errors.amount" class="text-xs text-rose-500">{{ billingForm.errors.amount }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Siklus Penagihan</span>
                <select v-model="billingForm.billing_cycle" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="cycle in filterOptions.billingCycles" :key="cycle.value" :value="cycle.value">{{ cycle.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="billingForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
              </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Start Date</span>
                <input v-model="billingForm.start_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="billingForm.errors.start_date" class="text-xs text-rose-500">{{ billingForm.errors.start_date }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Invoice Berikutnya</span>
                <input v-model="billingForm.next_invoice_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="billingForm.errors.next_invoice_date" class="text-xs text-rose-500">{{ billingForm.errors.next_invoice_date }}</p>
              </label>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
              <section class="space-y-4">
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</span>
                  <textarea v-model="billingForm.notes" rows="6" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                </label>
              </section>

              <section class="space-y-4">
                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-950 p-5 text-white">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-300">Ringkasan Langsung</p>
                  <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Jumlah</span>
                      <span class="font-semibold text-white">{{ formatCurrency(billingForm.amount || 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Siklus</span>
                      <span class="font-semibold text-white">{{ capitalizeWords(billingForm.billing_cycle) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Tipe</span>
                      <span class="font-semibold text-white">{{ billingForm.type === 'project_based' ? 'Berbasis Proyek' : 'Retainer' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Status</span>
                      <span class="font-semibold text-white">{{ capitalizeWords(billingForm.status) }}</span>
                    </div>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Konteks</p>
                  <div class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                    <p>Gunakan tautan project saat penagihan ini khusus untuk satu project tertentu.</p>
                    <p>Pastikan `next_invoice_date` akurat karena tombol buat jatuh tempo membaca tanggal ini untuk membuat draft invoice otomatis.</p>
                  </div>
                </article>
              </section>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeBillingModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="billingForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                <Sparkles class="h-4 w-4" />
                <span>{{ isEditingBilling ? 'Simpan Penagihan' : 'Buat Penagihan' }}</span>
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
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  CheckCircle2,
  Clock3,
  Eye,
  Filter,
  Pencil,
  Plus,
  RotateCcw,
  Sparkles,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  billings: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const billingsBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/billings`

const localBillings = ref(cloneBillings(props.billings.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedBillingId = ref(props.billings.selected_id || props.billings.items?.[0]?.id || null)
const showBillingModal = ref(false)
const editingBillingId = ref(null)

const billingForm = useForm({
  client_id: '',
  project_id: '',
  name: '',
  type: 'retainer',
  amount: 0,
  billing_cycle: 'monthly',
  start_date: '',
  next_invoice_date: '',
  status: 'active',
  notes: '',
})

const billingSummary = computed(() => props.billings.summary)
const billingItems = computed(() => localBillings.value)
const selectedBilling = computed(() => billingItems.value.find((billing) => billing.id === selectedBillingId.value) || billingItems.value[0] || null)
const isEditingBilling = computed(() => Boolean(editingBillingId.value))

watch(
  () => props.billings.items,
  (items) => {
    localBillings.value = cloneBillings(items || [])

    if (!selectedBillingId.value || !localBillings.value.some((billing) => billing.id === selectedBillingId.value)) {
      selectedBillingId.value = localBillings.value[0]?.id || null
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

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    client: filters.client ?? '',
    project: filters.project ?? '',
    type: filters.type ?? '',
    status: filters.status ?? '',
    billing_cycle: filters.billing_cycle ?? '',
    schedule_state: filters.schedule_state ?? '',
  }
}

function applyFilters() {
  router.get(billingsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(billingsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function selectBilling(billingId) {
  selectedBillingId.value = billingId
}

function openBillingModal(billing = null) {
  editingBillingId.value = billing?.id || null
  billingForm.reset()
  billingForm.clearErrors()
  billingForm.client_id = billing?.client_id || ''
  billingForm.project_id = billing?.project_id || ''
  billingForm.name = billing?.name || ''
  billingForm.type = billing?.type || 'retainer'
  billingForm.amount = billing?.amount ?? 0
  billingForm.billing_cycle = billing?.billing_cycle || 'monthly'
  billingForm.start_date = billing?.start_date || ''
  billingForm.next_invoice_date = billing?.next_invoice_date || ''
  billingForm.status = billing?.status || 'active'
  billingForm.notes = billing?.notes || ''
  showBillingModal.value = true
}

function closeBillingModal() {
  showBillingModal.value = false
  editingBillingId.value = null
  billingForm.reset()
  billingForm.clearErrors()
  billingForm.type = 'retainer'
  billingForm.amount = 0
  billingForm.billing_cycle = 'monthly'
  billingForm.status = 'active'
}

function submitBilling() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeBillingModal(),
  }

  if (isEditingBilling.value) {
    billingForm.patch(`${billingsBaseUrl}/${encodeURIComponent(editingBillingId.value)}`, options)
    return
  }

  billingForm.post(billingsBaseUrl, options)
}

function deleteBilling(billingId) {
  if (!confirm('Hapus penagihan ini?')) {
    return
  }

  router.delete(`${billingsBaseUrl}/${encodeURIComponent(billingId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedBillingId.value === billingId) {
        selectedBillingId.value = billingItems.value.find((billing) => billing.id !== billingId)?.id || null
      }
    },
  })
}

function generateInvoice(billingId) {
  router.post(`${billingsBaseUrl}/${encodeURIComponent(billingId)}/generate-invoice`, {}, {
    preserveScroll: true,
  })
}

function generateDueInvoices() {
  router.post(`${billingsBaseUrl}/generate-due`, {}, {
    preserveScroll: true,
  })
}

function canGenerate(billing) {
  return billing?.status === 'active'
}

function typeClass(type) {
  const map = {
    retainer: 'bg-sky-100 text-sky-700',
    project_based: 'bg-violet-100 text-violet-700',
  }

  return map[type] || 'bg-stone-100 text-stone-700'
}

function statusClass(status) {
  const map = {
    active: 'bg-emerald-100 text-emerald-700',
    paused: 'bg-amber-100 text-amber-700',
    completed: 'bg-stone-100 text-stone-700',
  }

  return map[status] || 'bg-stone-100 text-stone-700'
}

function scheduleStateClass(state) {
  const map = {
    due_now: 'bg-emerald-100 text-emerald-700',
    upcoming: 'bg-sky-100 text-sky-700',
    overdue: 'bg-rose-100 text-rose-700',
    paused: 'bg-amber-100 text-amber-700',
    completed: 'bg-stone-100 text-stone-700',
  }

  return map[state] || 'bg-stone-100 text-stone-700'
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
}

function capitalizeWords(value) {
  return value
    ? value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ')
    : ''
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneBillings(items) {
  return items.map((billing) => ({
    ...billing,
    client: billing.client ? { ...billing.client } : null,
    project: billing.project ? { ...billing.project } : null,
    recent_invoices: Array.isArray(billing.recent_invoices) ? billing.recent_invoices.map((invoice) => ({ ...invoice })) : [],
    payment_history: Array.isArray(billing.payment_history) ? billing.payment_history.map((payment) => ({ ...payment })) : [],
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
