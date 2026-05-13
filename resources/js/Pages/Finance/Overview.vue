<template>
  <WorkspaceLayout
    title="Ringkasan Keuangan"
    subtitle="Pusat kendali keuangan untuk pemasukan, pengeluaran, profit, invoice tertunggak, arus kas, ringkasan pajak, dan pencatatan transaksi harian."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <div class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm">
          <CalendarRange class="h-4 w-4" />
          <span>{{ summary.period_label }}</span>
        </div>
        <button
          type="button"
          @click="openTransactionModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Transaksi Baru</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="max-w-3xl">
              <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 14 / Ringkasan Keuangan</p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Pantau pemasukan, pengeluaran, profit, pajak, dan invoice tertunggak dalam satu ringkasan cepat.</h2>
              <p class="mt-2 text-sm leading-6 text-stone-500">
                Ringkasan tetap membaca invoice dan transaksi, tapi area atas sekarang lebih ringkas supaya grafik dan angka utama lebih cepat terlihat.
              </p>
            </div>

            <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Laba</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.profit_label }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pemasukan</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.income_label }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pengeluaran</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.expense_label }}</p>
              </div>
              <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tertunggak</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.outstanding_total_label }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Periode</p>
              <p class="mt-2 text-sm font-semibold text-stone-950">{{ summary.period_label }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Invoice Tertunggak</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ summary.outstanding_count }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pajak Terkumpul</p>
              <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ taxSummary.collected_tax_label }}</p>
            </div>
          </div>

          <div class="mt-3 flex flex-wrap gap-2">
            <button
              v-for="period in filterOptions.periods"
              :key="period.value"
              type="button"
              @click="applyPeriod(period.value)"
              :class="[
                'rounded-full px-4 py-2 text-[11px] font-bold uppercase tracking-[0.18em] transition',
                filters.period === period.value ? 'bg-stone-950 text-white' : 'bg-stone-100 text-stone-500 hover:bg-stone-200 hover:text-stone-950',
              ]"
            >
              {{ period.label }}
            </button>
          </div>
        </section>

        <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Grafik Arus Kas</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Enam bulan terakhir: pemasukan, pengeluaran, dan profit.</h2>
            </div>
            <div class="flex flex-wrap gap-2">
              <span class="rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Pemasukan</span>
              <span class="rounded-full bg-rose-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-rose-700">Pengeluaran</span>
              <span class="rounded-full bg-sky-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-sky-700">Laba</span>
            </div>
          </div>

          <div class="mt-8 overflow-x-auto">
            <div class="grid min-w-[900px] gap-4 lg:grid-cols-6">
              <article
                v-for="(label, index) in cashFlow.labels"
                :key="label"
                class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4"
              >
                <p class="text-center text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ label }}</p>
                <div class="mt-5 flex h-40 items-end justify-center gap-2">
                  <div class="w-4 rounded-full bg-emerald-400/90" :style="{ height: `${barHeight(cashFlow.income[index], cashFlow.income)}%` }"></div>
                  <div class="w-4 rounded-full bg-rose-400/90" :style="{ height: `${barHeight(cashFlow.expense[index], cashFlow.expense)}%` }"></div>
                  <div class="w-4 rounded-full bg-sky-500/90" :style="{ height: `${barHeight(cashFlow.profit[index], cashFlow.profit)}%` }"></div>
                </div>
                <div class="mt-4 space-y-2 text-xs text-stone-500">
                  <p>Masuk {{ formatCurrency(cashFlow.income[index]) }}</p>
                  <p>Keluar {{ formatCurrency(cashFlow.expense[index]) }}</p>
                  <p class="font-semibold text-stone-900">Bersih {{ formatCurrency(cashFlow.profit[index]) }}</p>
                </div>
              </article>
            </div>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Invoice Tertunggak</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Tagihan aktif yang masih menunggu cash-in.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
                {{ outstandingInvoices.length }} invoices
              </span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="invoice in outstandingInvoices" :key="invoice.id" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <p class="text-sm font-semibold text-stone-950">{{ invoice.number }}</p>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="invoiceStatusClass(invoice.status)">
                        {{ invoice.status_label }}
                      </span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ invoice.client?.name || 'Tanpa klien' }} / {{ invoice.project?.name || 'Tanpa project' }}</p>
                    <p class="mt-2 text-xs text-stone-500">Jatuh tempo {{ invoice.due_date_label || 'Belum ada tenggat' }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-lg font-semibold tracking-[-0.04em] text-stone-950">{{ invoice.outstanding_amount_label }}</p>
                    <p class="mt-2 text-xs text-stone-500">Pajak {{ invoice.tax_amount_label }}</p>
                  </div>
                </div>
              </article>

              <div v-if="outstandingInvoices.length === 0" class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                Tidak ada outstanding invoice pada workspace ini.
              </div>
            </div>
          </article>

          <aside class="space-y-4">
            <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Ringkasan Pajak</p>
              <div class="mt-5 grid gap-3">
                <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Dasar Pajak</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ taxSummary.taxable_amount_label }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                  <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Terkumpul</p>
                    <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ taxSummary.collected_tax_label }}</p>
                  </div>
                  <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tertunggak</p>
                    <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ taxSummary.outstanding_tax_label }}</p>
                  </div>
                </div>
                <div class="rounded-[1.3rem] border border-dashed border-stone-300 bg-white px-4 py-4 text-sm leading-6 text-stone-600">
                  Rata-rata tarif pajak invoice pada periode ini {{ taxSummary.average_rate_label }}.
                </div>
              </div>
            </section>

            <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-200/75">Akun</p>
              <div class="mt-4 space-y-3">
                <article v-for="account in accounts" :key="account.id" class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-sm font-semibold text-white">{{ account.name }}</p>
                      <p class="mt-1 text-xs uppercase tracking-[0.16em] text-stone-400">{{ account.type }}</p>
                    </div>
                    <p class="text-sm font-semibold text-white">{{ account.balance_label }}</p>
                  </div>
                </article>
                <div v-if="accounts.length === 0" class="rounded-[1.2rem] border border-dashed border-white/10 bg-white/5 px-4 py-8 text-center text-sm text-stone-300">
                  Belum ada akun bank aktif.
                </div>
              </div>
            </section>
          </aside>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pendapatan per Klien</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Klien penyumbang pendapatan pada periode aktif.</h2>
              </div>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="client in revenueByClient" :key="client.name" class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ client.name }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ client.count }} invoice lunas</p>
                  </div>
                  <p class="text-sm font-semibold text-stone-950">{{ client.amount_label }}</p>
                </div>
                <div class="mt-4 h-2 rounded-full bg-white">
                  <div class="h-2 rounded-full bg-emerald-500" :style="{ width: `${barWidth(client.amount, revenueByClient.map((item) => item.amount))}%` }"></div>
                </div>
              </article>

              <div v-if="revenueByClient.length === 0" class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                Belum ada revenue paid per client pada periode ini.
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pendapatan per Proyek</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Proyek dengan pemasukan invoice lunas terbesar.</h2>
              </div>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="project in revenueByProject" :key="project.name" class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ project.name }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ project.count }} invoice lunas</p>
                  </div>
                  <p class="text-sm font-semibold text-stone-950">{{ project.amount_label }}</p>
                </div>
                <div class="mt-4 h-2 rounded-full bg-white">
                  <div class="h-2 rounded-full bg-sky-500" :style="{ width: `${barWidth(project.amount, revenueByProject.map((item) => item.amount))}%` }"></div>
                </div>
              </article>

              <div v-if="revenueByProject.length === 0" class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                Belum ada revenue paid per project pada periode ini.
              </div>
            </div>
          </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Transaksi</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Ledger transaksi terbaru dengan CRUD langsung dari overview.</h2>
              </div>
              <button type="button" @click="openTransactionModal()" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                <Plus class="h-4 w-4" />
                <span>Add</span>
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="transaction in transactions" :key="transaction.id" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="transactionTypeClass(transaction.type)">
                        {{ transaction.type_label }}
                      </span>
                      <p class="text-sm font-semibold text-stone-950">{{ transaction.category || 'General' }}</p>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ transaction.description || 'No description' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ transaction.account?.name || 'No account' }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ transaction.project?.name || 'Tanpa proyek' }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ transaction.invoice?.number || 'Manual entry' }}</span>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-lg font-semibold tracking-[-0.04em] text-stone-950">{{ transaction.amount_label }}</p>
                    <p class="mt-2 text-xs text-stone-500">{{ transaction.date_label }}</p>
                    <div class="mt-3 flex justify-end gap-2">
                      <button type="button" @click="openTransactionModal(transaction)" class="rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                        <Pencil class="h-3.5 w-3.5" />
                      </button>
                      <button type="button" @click="deleteTransaction(transaction.id)" class="rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                        <Trash2 class="h-3.5 w-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              </article>

              <div v-if="transactions.length === 0" class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
                Belum ada transaksi yang tercatat.
              </div>
            </div>
          </article>

          <aside class="space-y-4">
            <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Finance Splits</p>
              <div class="mt-4 space-y-3">
                <article v-for="split in financeSplits" :key="split.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-sm font-semibold text-stone-950">{{ split.project?.name || 'Pembagian Proyek' }}</p>
                  <p class="mt-2 text-xs text-stone-500">{{ split.template_name || 'Custom template' }}</p>
                  <div class="mt-3 grid gap-2 text-xs text-stone-500">
                    <p>Nilai proyek {{ split.total_project_value_label }}</p>
                    <p>Operational {{ split.total_operational_cost_label }}</p>
                    <p>Team fee {{ split.total_team_fee_label }}</p>
                    <p>Kas kantor {{ split.total_kas_kantor_label }}</p>
                  </div>
                </article>
                <div v-if="financeSplits.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                  Belum ada project finance split.
                </div>
              </div>
            </section>
          </aside>
        </section>
      </div>

      <Transition name="modal">
        <div v-if="showTransactionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
          <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Transaction Form</p>
                <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingTransaction ? 'Edit Transaction' : 'Create Transaction' }}</h3>
              </div>
              <button type="button" @click="closeTransactionModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
                <X class="h-5 w-5" />
              </button>
            </div>

            <form class="mt-6 space-y-5" @submit.prevent="submitTransaction">
              <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
                  <select v-model="transactionForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option v-for="type in filterOptions.transactionTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                  </select>
                </label>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span>
                  <input v-model="transactionForm.amount" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  <p v-if="transactionForm.errors.amount" class="text-xs text-rose-500">{{ transactionForm.errors.amount }}</p>
                </label>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Date</span>
                  <input v-model="transactionForm.date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  <p v-if="transactionForm.errors.date" class="text-xs text-rose-500">{{ transactionForm.errors.date }}</p>
                </label>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kategori</span>
                  <input v-model="transactionForm.category" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  <p class="text-xs text-stone-500">Gunakan kategori yang sama dengan struktur laporan keuangan utama.</p>
                </label>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Account</span>
                  <select v-model="transactionForm.account_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option value="">No account</option>
                    <option v-for="account in filterOptions.accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                  </select>
                </label>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
                  <select v-model="transactionForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option value="">Tanpa project</option>
                    <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                  </select>
                </label>

                <label class="space-y-2 text-sm xl:col-span-2">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Invoice</span>
                  <select v-model="transactionForm.invoice_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                    <option value="">Manual entry</option>
                    <option v-for="invoice in filterOptions.invoices" :key="invoice.id" :value="invoice.id">{{ invoice.name }}</option>
                  </select>
                </label>
              </div>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
                <textarea v-model="transactionForm.description" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <div class="flex flex-wrap items-center justify-end gap-3">
                <button type="button" @click="closeTransactionModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                  Cancel
                </button>
                <button type="submit" :disabled="transactionForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                  {{ isEditingTransaction ? 'Save Transaction' : 'Create Transaction' }}
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
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
  CalendarRange,
  Pencil,
  Plus,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  finance: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const financeBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance`
const transactionsBaseUrl = `${financeBaseUrl}/transactions`

const showTransactionModal = ref(false)
const editingTransactionId = ref(null)

const transactionForm = useForm({
  account_id: '',
  invoice_id: '',
  project_id: '',
  type: 'expense',
  category: '',
  amount: '',
  description: '',
  date: todayDate(),
})

const summary = computed(() => props.finance.summary)
const cashFlow = computed(() => props.finance.cashFlow)
const outstandingInvoices = computed(() => props.finance.outstandingInvoices || [])
const revenueByClient = computed(() => props.finance.revenueByClient || [])
const revenueByProject = computed(() => props.finance.revenueByProject || [])
const taxSummary = computed(() => props.finance.taxSummary)
const accounts = computed(() => props.finance.accounts || [])
const financeSplits = computed(() => props.finance.financeSplits || [])
const transactions = computed(() => props.finance.transactions || [])
const filters = computed(() => props.filters || { period: 'month' })
const isEditingTransaction = computed(() => Boolean(editingTransactionId.value))

function applyPeriod(period) {
  router.get(financeBaseUrl, { period }, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openTransactionModal(transaction = null) {
  editingTransactionId.value = transaction?.id || null
  transactionForm.reset()
  transactionForm.clearErrors()
  transactionForm.account_id = transaction?.account_id || ''
  transactionForm.invoice_id = transaction?.invoice_id || ''
  transactionForm.project_id = transaction?.project_id || ''
  transactionForm.type = transaction?.type || 'expense'
  transactionForm.category = transaction?.category || ''
  transactionForm.amount = transaction?.amount ?? ''
  transactionForm.description = transaction?.description || ''
  transactionForm.date = transaction?.date || todayDate()
  showTransactionModal.value = true
}

function closeTransactionModal() {
  showTransactionModal.value = false
  editingTransactionId.value = null
  transactionForm.reset()
  transactionForm.clearErrors()
  transactionForm.type = 'expense'
  transactionForm.date = todayDate()
}

function submitTransaction() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeTransactionModal(),
  }

  if (isEditingTransaction.value) {
    transactionForm.patch(`${transactionsBaseUrl}/${encodeURIComponent(editingTransactionId.value)}`, options)
    return
  }

  transactionForm.post(transactionsBaseUrl, options)
}

function deleteTransaction(transactionId) {
  if (!confirm('Hapus transaksi ini?')) {
    return
  }

  router.delete(`${transactionsBaseUrl}/${encodeURIComponent(transactionId)}`, {
    preserveScroll: true,
  })
}

function formatCurrency(value) {
  const currency = 'IDR'

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency,
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function barHeight(value, values) {
  const maxValue = Math.max(...values.map((item) => Math.abs(item)), 1)

  return Math.max(10, (Math.abs(value || 0) / maxValue) * 100)
}

function barWidth(value, values) {
  const maxValue = Math.max(...values, 1)

  return (value / maxValue) * 100
}

function invoiceStatusClass(status) {
  const map = {
    draft: 'bg-stone-100 text-stone-600',
    sent: 'bg-sky-100 text-sky-700',
    partial: 'bg-amber-100 text-amber-700',
    overdue: 'bg-rose-100 text-rose-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function transactionTypeClass(type) {
  return type === 'income'
    ? 'bg-emerald-100 text-emerald-700'
    : 'bg-rose-100 text-rose-700'
}

function todayDate() {
  return new Date().toISOString().slice(0, 10)
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
