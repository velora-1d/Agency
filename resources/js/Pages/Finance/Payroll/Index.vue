<template>
  <WorkspaceLayout
    title="Payroll & Fee Tim"
    subtitle="Menu 20 difokuskan untuk bagi hasil per proyek, komponen fee tim, bonus, komisi, potongan, pemicu pembayaran, dan laporan penghasilan per anggota."
  >
    <template #actions>
      <button
        type="button"
        @click="openSplitModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>Split Baru</span>
      </button>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 20 / Payroll & Fee Tim</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Bagi hasil per proyek yang rapi, dari potong operasional sampai distribusi fee tim.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Komponen fee, bonus, komisi, dan deduction tetap ada, tapi pembuka dibuat lebih ringkas supaya status payout lebih cepat kebaca.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pembayaran Tertunda</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.pending_payout_total_label }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Split</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.total_splits }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Item Tertunda</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.pending_payout_items }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Item Lunas</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.paid_payout_items }}</p></div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-4">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Team Fee</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.total_team_fee_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Operasional</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.total_operational_cost_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Kas Kantor</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ splitSummary.total_kas_kantor_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Ringkasan Anggota</p><p class="mt-2 text-sm leading-6 text-stone-600">{{ memberItems.length }} item pembayaran anggota</p></div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari split berdasarkan proyek, pemicu pembayaran, atau status item pembayaran.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ splitItems.length }}</span> splits tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
            <select v-model="filterState.project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Proyek</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pemicu Pembayaran</span>
            <select v-model="filterState.payment_trigger" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Pemicu</option>
              <option v-for="trigger in filterOptions.paymentTriggers" :key="trigger.value" :value="trigger.value">{{ trigger.label }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status Item</span>
            <select v-model="filterState.item_status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in filterOptions.itemStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
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
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Split</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua payroll split per proyek.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ splitItems.length }} split</span>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="split in splitItems"
              :key="split.id"
              class="rounded-[1.6rem] border p-5 transition"
              :class="selectedSplit?.id === split.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <button type="button" @click="selectSplit(split.id)" class="text-left text-base font-semibold transition" :class="selectedSplit?.id === split.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                    {{ split.project?.name || 'Split Proyek Tanpa Judul' }}
                  </button>
                  <p class="mt-2 text-sm" :class="selectedSplit?.id === split.id ? 'text-stone-300' : 'text-stone-500'">
                    {{ split.template_name || 'Tanpa nama templat' }} | {{ split.payment_trigger_label }}
                  </p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedSplit?.id === split.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ split.total_project_value_label }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedSplit?.id === split.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ split.total_team_fee_label }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedSplit?.id === split.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ split.total_kas_kantor_label }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-lg font-semibold" :class="selectedSplit?.id === split.id ? 'text-white' : 'text-stone-950'">{{ split.kas_kantor_percentage }}%</p>
                  <p class="mt-1 text-sm" :class="selectedSplit?.id === split.id ? 'text-stone-300' : 'text-stone-500'">kas kantor</p>
                </div>
              </div>
            </article>

            <div v-if="splitItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada payroll split yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedSplit">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Split</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedSplit.project?.name }}</h2>
                <p class="mt-2 text-sm text-stone-500">{{ selectedSplit.project?.client_name || 'Belum tertaut ke klien' }}</p>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openSplitModal(selectedSplit)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="deleteSplit(selectedSplit.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                  <Trash2 class="h-4 w-4" />
                  <span>Hapus</span>
                </button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)] p-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Nilai Proyek</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSplit.total_project_value_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Operasional</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSplit.total_operational_cost_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Kas Kantor</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSplit.total_kas_kantor_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Fee Tim</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSplit.total_team_fee_label }}</p>
                  </div>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Item Pembayaran</p>
                    <h3 class="mt-2 text-lg font-semibold text-stone-950">Komponen operasional dan fee tim untuk proyek ini.</h3>
                  </div>
                  <div class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ selectedSplit.payment_trigger_label }}</div>
                </div>

                <div class="mt-5 space-y-3">
                  <article v-for="item in selectedSplit.items" :key="item.id" class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <div class="flex flex-wrap items-center gap-2">
                          <p class="text-sm font-semibold text-stone-950">{{ item.label }}</p>
                          <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-600">{{ item.component_type_label }}</span>
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="item.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">{{ item.status_label }}</span>
                        </div>
                        <p class="mt-2 text-sm text-stone-600">{{ item.user?.name || 'Belum ada anggota' }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ item.calculation_type_label }} <span v-if="item.percentage !== null">| {{ item.percentage }}%</span></p>
                      </div>
                      <div class="text-right">
                        <p class="text-sm font-semibold text-stone-950">{{ item.final_amount_label }}</p>
                        <p class="mt-1 text-xs text-stone-500">{{ item.paid_at_label || 'Belum dibayar' }}</p>
                      </div>
                    </div>
                    <div v-if="item.type === 'team_fee'" class="mt-4">
                      <button type="button" @click="toggleItemStatus(item)" class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition" :class="item.status === 'paid' ? 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'">
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        <span>{{ item.status === 'paid' ? 'Tandai Tertunda' : 'Tandai Lunas' }}</span>
                      </button>
                    </div>
                  </article>
                </div>
              </article>
            </div>
          </template>

          <div v-else class="flex min-h-[420px] items-center justify-center rounded-[1.8rem] border border-dashed border-stone-200 bg-stone-50 p-8 text-center">
            <div>
              <p class="text-lg font-semibold text-stone-950">Belum ada split terpilih.</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Pilih split dari daftar kiri atau buat payroll split baru.</p>
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Per Member</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Laporan pending, paid, earning, dan komisi per anggota tim.</h2>
          </div>
          <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
            <span class="font-semibold text-stone-950">{{ memberItems.length }}</span> members
          </div>
        </div>
        <div class="mt-5 grid gap-4 xl:grid-cols-3">
          <article v-for="member in memberItems" :key="member.id" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
            <p class="text-base font-semibold text-stone-950">{{ member.user_name }}</p>
            <div class="mt-4 space-y-2 text-sm text-stone-600">
              <p>Pending: {{ member.pending_total_label }}</p>
              <p>Lunas: {{ member.paid_total_label }}</p>
              <p>Total penghasilan: {{ member.total_earning_label }}</p>
              <p>Commission: {{ member.commission_total_label }}</p>
            </div>
          </article>
          <div v-if="memberItems.length === 0" class="xl:col-span-3 rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada data earning anggota tim.
          </div>
        </div>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showSplitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Split</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingSplit ? 'Ubah Split Payroll' : 'Buat Split Payroll' }}</h3>
            </div>
            <button type="button" @click="closeSplitModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitSplit">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Templat</span>
                <input v-model="splitForm.template_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
                <select v-model="splitForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Select project</option>
                  <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
                <p v-if="splitForm.errors.project_id" class="text-xs text-rose-500">{{ splitForm.errors.project_id }}</p>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kas Kantor %</span>
                <input v-model.number="splitForm.kas_kantor_percentage" type="number" min="0" max="100" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pemicu Pembayaran</span>
                <select v-model="splitForm.payment_trigger" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Select trigger</option>
                  <option v-for="trigger in filterOptions.paymentTriggers" :key="trigger.value" :value="trigger.value">{{ trigger.label }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Custom Trigger Note</span>
                <input v-model="splitForm.payment_trigger_custom" type="text" :disabled="splitForm.payment_trigger !== 'custom'" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all disabled:cursor-not-allowed disabled:opacity-60 focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <section class="space-y-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Split Items</p>
                  <p class="mt-1 text-sm text-stone-500">Campurkan biaya operasional dan komponen fee tim di sini.</p>
                </div>
                <button type="button" @click="addItem" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Plus class="h-4 w-4" />
                  <span>Add Item</span>
                </button>
              </div>

              <div class="space-y-4">
                <article v-for="(item, index) in splitForm.items" :key="index" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
                  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
                      <select v-model="item.type" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400">
                        <option value="operational">Operational</option>
                        <option value="team_fee">Team Fee</option>
                      </select>
                    </label>
                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Component</span>
                      <select v-model="item.component_type" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400">
                        <option v-for="component in filterOptions.componentTypes" :key="component.value" :value="component.value">{{ component.label }}</option>
                      </select>
                    </label>
                    <label class="space-y-2 text-sm xl:col-span-2">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Label</span>
                      <input v-model="item.label" type="text" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                    </label>
                    <label class="space-y-2 text-sm" v-if="item.type === 'team_fee'">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Member</span>
                      <select v-model="item.user_id" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400">
                        <option value="">Select member</option>
                        <option v-for="user in filterOptions.users" :key="user.id" :value="user.id">{{ user.name }}</option>
                      </select>
                    </label>
                    <div v-else></div>
                    <button type="button" @click="removeItem(index)" class="self-end rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                      Remove
                    </button>
                  </div>

                  <div class="mt-4 grid gap-4 md:grid-cols-4">
                    <label class="space-y-2 text-sm" v-if="item.type === 'team_fee'">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jenis Hitung</span>
                      <select v-model="item.calculation_type" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400">
                        <option value="flat">Flat</option>
                        <option value="percentage">Percentage</option>
                      </select>
                    </label>
                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Percentage</span>
                      <input v-model.number="item.percentage" :disabled="item.type !== 'team_fee' || item.calculation_type !== 'percentage'" type="number" min="0" max="100" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all disabled:cursor-not-allowed disabled:opacity-60 focus:border-stone-400" />
                    </label>
                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Flat Amount</span>
                      <input v-model.number="item.flat_amount" type="number" step="1000" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400" />
                    </label>
                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                      <select v-model="item.status" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400">
                        <option v-for="status in filterOptions.itemStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                      </select>
                    </label>
                  </div>
                </article>
              </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeSplitModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="splitForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                <Sparkles class="h-4 w-4" />
                <span>{{ isEditingSplit ? 'Simpan Split' : 'Buat Split' }}</span>
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
import { BadgeCheck, Banknote, CheckCircle2, CircleDollarSign, Filter, Pencil, Plus, RotateCcw, Sparkles, Trash2, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  splits: { type: Object, required: true },
  members: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/payroll`

const localSplits = ref(cloneSplits(props.splits.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedSplitId = ref(props.splits.selected_id || props.splits.items?.[0]?.id || null)
const showSplitModal = ref(false)
const editingSplitId = ref(null)

const splitForm = useForm({
  project_id: '',
  template_name: '',
  kas_kantor_percentage: 0,
  payment_trigger: '',
  payment_trigger_custom: '',
  items: [blankItem()],
})

const splitSummary = computed(() => props.splits.summary)
const memberItems = computed(() => props.members || [])
const splitItems = computed(() => localSplits.value)
const selectedSplit = computed(() => splitItems.value.find((split) => split.id === selectedSplitId.value) || splitItems.value[0] || null)
const isEditingSplit = computed(() => Boolean(editingSplitId.value))

watch(() => props.splits.items, (items) => {
  localSplits.value = cloneSplits(items || [])
  if (!selectedSplitId.value || !localSplits.value.some((split) => split.id === selectedSplitId.value)) {
    selectedSplitId.value = localSplits.value[0]?.id || null
  }
})

watch(() => props.filters, (filters) => {
  filterState.value = buildFilterState(filters)
}, { deep: true })

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    project: filters.project ?? '',
    payment_trigger: filters.payment_trigger ?? '',
    item_status: filters.item_status ?? '',
  }
}

function applyFilters() {
  router.get(baseUrl, compactFilters(filterState.value), { preserveState: true, preserveScroll: true })
}

function resetFilters() {
  filterState.value = buildFilterState()
  router.get(baseUrl, {}, { preserveState: true, preserveScroll: true })
}

function selectSplit(splitId) {
  selectedSplitId.value = splitId
}

function openSplitModal(split = null) {
  editingSplitId.value = split?.id || null
  splitForm.reset()
  splitForm.clearErrors()
  splitForm.project_id = split?.project_id || ''
  splitForm.template_name = split?.template_name || ''
  splitForm.kas_kantor_percentage = split?.kas_kantor_percentage ?? 0
  splitForm.payment_trigger = split?.payment_trigger || ''
  splitForm.payment_trigger_custom = split?.payment_trigger_custom || ''
  splitForm.items = split?.items?.length
    ? split.items.map((item) => ({
        type: item.type,
        component_type: item.component_type || (item.type === 'operational' ? 'operational' : 'base_fee'),
        label: item.label,
        user_id: item.user_id || '',
        calculation_type: item.calculation_type || 'flat',
        percentage: item.percentage ?? null,
        flat_amount: item.flat_amount ?? null,
        status: item.status || 'pending',
      }))
    : [blankItem()]
  showSplitModal.value = true
}

function closeSplitModal() {
  showSplitModal.value = false
  editingSplitId.value = null
  splitForm.reset()
  splitForm.clearErrors()
  splitForm.kas_kantor_percentage = 0
  splitForm.items = [blankItem()]
}

function addItem() {
  splitForm.items.push(blankItem())
}

function removeItem(index) {
  if (splitForm.items.length === 1) {
    splitForm.items = [blankItem()]
    return
  }
  splitForm.items.splice(index, 1)
}

function submitSplit() {
  const options = { preserveScroll: true, onSuccess: () => closeSplitModal() }
  if (isEditingSplit.value) {
    splitForm.patch(`${baseUrl}/${encodeURIComponent(editingSplitId.value)}`, options)
    return
  }
  splitForm.post(baseUrl, options)
}

function deleteSplit(splitId) {
  if (!confirm('Hapus pembagian payroll ini?')) return
  router.delete(`${baseUrl}/${encodeURIComponent(splitId)}`, { preserveScroll: true })
}

function toggleItemStatus(item) {
  const nextStatus = item.status === 'paid' ? 'pending' : 'paid'
  router.patch(`${baseUrl}/${encodeURIComponent(selectedSplit.value.id)}/items/${encodeURIComponent(item.id)}/status`, { status: nextStatus }, { preserveScroll: true })
}

function blankItem() {
  return {
    type: 'team_fee',
    component_type: 'base_fee',
    label: '',
    user_id: '',
    calculation_type: 'flat',
    percentage: null,
    flat_amount: 0,
    status: 'pending',
  }
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneSplits(items) {
  return items.map((split) => ({
    ...split,
    project: split.project ? { ...split.project } : null,
    items: Array.isArray(split.items) ? split.items.map((item) => ({ ...item, user: item.user ? { ...item.user } : null })) : [],
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
