<template>
  <WorkspaceLayout
    title="Transaksi"
    subtitle="Menu 17 difokuskan untuk buku besar pemasukan dan pengeluaran yang rapi: mudah dicari, mudah dikaitkan ke proyek atau invoice, dan mudah diekspor saat finance butuh rekap cepat."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="exportExcel"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <Sheet class="h-4 w-4" />
          <span>Export Excel</span>
        </button>
        <button
          type="button"
          @click="printPdf"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <Printer class="h-4 w-4" />
          <span>Cetak / PDF</span>
        </button>
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
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 17 / Transaksi</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Buku besar harian yang cepat dibaca: pemasukan, pengeluaran, entri manual, dan transaksi terkait invoice.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Fokus finance tetap sama, tapi bagian atas dipadatkan supaya tabel transaksi dan filter jadi lebih dominan.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Net Flow</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.net_total_label }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.total_transactions }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pemasukan</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.income_total_label }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pengeluaran</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.expense_total_label }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Manual Entry</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.manual_entry_count }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tertaut Invoice</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.invoice_linked_count }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Attachments</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ transactionSummary.attachment_count }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari transaksi berdasarkan jenis, kategori, klien, proyek, invoice, akun, dan rentang tanggal.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ transactionItems.length }}</span> transaksi tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-6">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
            <select v-model="filterState.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Tipe</option>
              <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kategori</span>
            <select v-model="filterState.category" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Kategori</option>
              <option v-for="category in filterOptions.categories" :key="category.value" :value="category.value">{{ category.label }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
            <select v-model="filterState.client" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Klien</option>
              <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Invoice</span>
            <select v-model="filterState.invoice" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Invoice</option>
              <option v-for="invoice in filterOptions.invoices" :key="invoice.id" :value="invoice.id">{{ invoice.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Akun</span>
            <select v-model="filterState.account" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Akun</option>
              <option v-for="account in filterOptions.accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Dari</span>
            <input v-model="filterState.date_from" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Sampai</span>
            <input v-model="filterState.date_to" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
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
        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Lensa Kategori</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Kategori yang paling sering muncul</h2>
              </div>
            </div>

            <div class="mt-5 space-y-3">
              <article v-for="category in categoryBreakdown" :key="category.category" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-stone-950">{{ category.label }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ category.count }} transaksi</p>
                  </div>
                  <p class="text-sm font-semibold text-stone-950">{{ category.amount_label }}</p>
                </div>
              </article>

              <div v-if="categoryBreakdown.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                Belum ada kategori transaksi pada filter aktif.
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-200/75">Bacaan Cepat</p>
            <div class="mt-4 space-y-3">
              <article class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Transaksi terbaru</p>
                <p class="mt-2 text-sm text-stone-300">{{ transactionSummary.latest_transaction_label }}</p>
              </article>
              <article class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Entri manual</p>
                <p class="mt-2 text-sm text-stone-300">{{ transactionSummary.manual_entry_count }} transaksi belum terhubung invoice.</p>
              </article>
              <article class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">Alur tertaut invoice</p>
                <p class="mt-2 text-sm text-stone-300">{{ transactionSummary.invoice_linked_count }} transaksi sudah nyambung ke invoice.</p>
              </article>
            </div>
          </section>
        </aside>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedTransaction">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Buku Besar</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedTransaction.category_label }}</h2>
                <p class="mt-1 text-sm text-stone-500">{{ selectedTransaction.date_label }} | {{ selectedTransaction.entry_mode_label }}</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <button type="button" @click="openTransactionModal(selectedTransaction)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="deleteTransaction(selectedTransaction.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                  <Trash2 class="h-4 w-4" />
                  <span>Hapus</span>
                </button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)] p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cuplikan Transaksi</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="transactionTypeClass(selectedTransaction.type)">
                        {{ selectedTransaction.type_label }}
                      </span>
                      <span class="rounded-full bg-stone-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-600">
                        {{ selectedTransaction.entry_mode_label }}
                      </span>
                      <span v-if="selectedTransaction.has_attachment" class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700">
                        Lampiran
                      </span>
                    </div>
                    <p class="mt-4 text-3xl font-semibold tracking-[-0.05em] text-stone-950">{{ selectedTransaction.amount_label }}</p>
                  </div>
                  <div class="rounded-[1.4rem] border border-stone-200 bg-stone-50 px-4 py-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tanggal</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedTransaction.date_label }}</p>
                  </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Klien</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTransaction.client?.name || 'Belum tertaut ke klien' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Proyek</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTransaction.project?.name || 'Belum tertaut ke project' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Invoice</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTransaction.invoice?.number || 'Manual entry' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Account</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedTransaction.account?.name || 'No account linked' }}</p>
                  </div>
                </div>
              </article>

              <div class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">
                      {{ selectedTransaction.description || 'Belum ada deskripsi untuk transaksi ini.' }}
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Attachment</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <template v-if="selectedTransaction.attachment_path">
                        <a :href="selectedTransaction.attachment_path" target="_blank" rel="noreferrer" class="text-sm font-semibold text-sky-700 underline-offset-4 hover:underline">
                          {{ selectedTransaction.attachment_path }}
                        </a>
                      </template>
                      <p v-else class="text-sm text-stone-500">Belum ada attachment path / bukti transfer yang ditautkan.</p>
                    </div>
                  </article>
                </section>

                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Quick Meaning</p>
                    <div class="mt-4 space-y-3">
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-950">Entry mode</p>
                        <p class="mt-2 text-sm text-stone-600">{{ selectedTransaction.entry_mode_label }} memberi konteks apakah transaksi ini lahir dari invoice atau input manual.</p>
                      </div>
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-950">Pengelompokan kategori</p>
                        <p class="mt-2 text-sm text-stone-600">Kategori membantu laporan internal seperti operasional, gaji, tools, ads, dan lain-lain.</p>
                      </div>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Recent Ledger</p>
                    <div class="mt-4 space-y-3">
                      <button
                        v-for="transaction in transactionItems"
                        :key="transaction.id"
                        type="button"
                        @click="selectTransaction(transaction.id)"
                        class="flex w-full items-center justify-between rounded-[1.2rem] border px-4 py-3 text-left transition"
                        :class="selectedTransaction.id === transaction.id ? 'border-stone-900 bg-stone-950 text-white' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
                      >
                        <div>
                          <p class="text-sm font-semibold" :class="selectedTransaction.id === transaction.id ? 'text-white' : 'text-stone-950'">{{ transaction.category_label }}</p>
                          <p class="mt-1 text-xs" :class="selectedTransaction.id === transaction.id ? 'text-stone-300' : 'text-stone-500'">{{ transaction.date_label }}</p>
                        </div>
                        <p class="text-sm font-semibold" :class="selectedTransaction.id === transaction.id ? 'text-white' : 'text-stone-950'">{{ transaction.amount_label }}</p>
                      </button>
                    </div>
                  </article>
                </section>
              </div>
            </div>
          </template>

          <div v-else class="flex min-h-[420px] items-center justify-center rounded-[1.8rem] border border-dashed border-stone-200 bg-stone-50 p-8 text-center">
            <div>
              <p class="text-lg font-semibold text-stone-950">Belum ada transaksi terpilih.</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Pilih transaksi dari ledger atau buat transaksi baru untuk mulai isi menu 17.</p>
            </div>
          </div>
        </article>
      </section>
    </div>
    <Transition name="modal">
      <div v-if="showTransactionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Transaksi</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingTransaction ? 'Ubah Transaksi' : 'Buat Transaksi' }}</h3>
            </div>
            <button type="button" @click="closeTransactionModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitTransaction">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
                <select v-model="transactionForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <p v-if="transactionForm.errors.type" class="text-xs text-rose-500">{{ transactionForm.errors.type }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span>
                <input v-model.number="transactionForm.amount" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
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
                <p class="text-xs text-stone-500">Gunakan kategori internal yang konsisten untuk pelaporan.</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Account</span>
                <select v-model="transactionForm.account_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No account</option>
                  <option v-for="account in filterOptions.accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="transactionForm.client_hint" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500 outline-none transition-all focus:border-stone-400 focus:bg-white" disabled>
                  <option value="">{{ transactionClientHint }}</option>
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

            <div class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
              <section class="space-y-4">
                <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
                  <textarea v-model="transactionForm.description" rows="6" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                </label>

                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Attachment Path / URL</span>
                  <input v-model="transactionForm.attachment_path" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  <p class="text-xs text-stone-500">Isi URL publik atau path file yang disimpan di storage internal.</p>
                </label>
              </section>

              <section class="space-y-4">
                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-950 p-5 text-white">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-300">Live Meaning</p>
                  <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Tipe</span>
                      <span class="font-semibold text-white">{{ transactionForm.type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Jumlah</span>
                      <span class="font-semibold text-white">{{ formatCurrency(transactionForm.amount || 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Mode</span>
                      <span class="font-semibold text-white">{{ transactionForm.invoice_id ? 'Tertaut Invoice' : 'Entri Manual' }}</span>
                    </div>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tips</p>
                  <div class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                    <p>Gunakan kategori yang konsisten supaya rekap operasional, gaji, tools, dan ads lebih cepat dibaca.</p>
                    <p>Kalau transaksi berasal dari invoice, pilih invoice agar jejak cashflow lebih rapi.</p>
                  </div>
                </article>
              </section>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeTransactionModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Cancel
              </button>
              <button type="submit" :disabled="transactionForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                <Sparkles class="h-4 w-4" />
                <span>{{ isEditingTransaction ? 'Simpan Transaksi' : 'Buat Transaksi' }}</span>
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
  Filter,
  Pencil,
  Plus,
  Printer,
  RotateCcw,
  Sheet,
  Sparkles,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  transactions: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const transactionsBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/transactions`

const localTransactions = ref(cloneTransactions(props.transactions.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedTransactionId = ref(props.transactions.selected_id || props.transactions.items?.[0]?.id || null)
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
  attachment_path: '',
  date: todayDate(),
  client_hint: '',
})

const transactionSummary = computed(() => props.transactions.summary)
const categoryBreakdown = computed(() => props.transactions.category_breakdown || [])
const transactionItems = computed(() => localTransactions.value)
const selectedTransaction = computed(() => transactionItems.value.find((transaction) => transaction.id === selectedTransactionId.value) || transactionItems.value[0] || null)
const isEditingTransaction = computed(() => Boolean(editingTransactionId.value))
const transactionClientHint = computed(() => selectedClientHint())

watch(
  () => props.transactions.items,
  (items) => {
    localTransactions.value = cloneTransactions(items || [])

    if (!selectedTransactionId.value || !localTransactions.value.some((transaction) => transaction.id === selectedTransactionId.value)) {
      selectedTransactionId.value = localTransactions.value[0]?.id || null
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
    type: filters.type ?? '',
    category: filters.category ?? '',
    client: filters.client ?? '',
    project: filters.project ?? '',
    invoice: filters.invoice ?? '',
    account: filters.account ?? '',
    date_from: filters.date_from ?? '',
    date_to: filters.date_to ?? '',
  }
}

function applyFilters() {
  router.get(transactionsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(transactionsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function selectTransaction(transactionId) {
  selectedTransactionId.value = transactionId
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
  transactionForm.attachment_path = transaction?.attachment_path || ''
  transactionForm.date = transaction?.date || todayDate()
  transactionForm.client_hint = transaction?.client?.name || ''
  showTransactionModal.value = true
}

function closeTransactionModal() {
  showTransactionModal.value = false
  editingTransactionId.value = null
  transactionForm.reset()
  transactionForm.clearErrors()
  transactionForm.type = 'expense'
  transactionForm.date = todayDate()
  transactionForm.client_hint = ''
}

function submitTransaction() {
  const payload = {
    account_id: transactionForm.account_id,
    invoice_id: transactionForm.invoice_id,
    project_id: transactionForm.project_id,
    type: transactionForm.type,
    category: transactionForm.category,
    amount: transactionForm.amount,
    description: transactionForm.description,
    attachment_path: transactionForm.attachment_path,
    date: transactionForm.date,
  }

  const options = {
    preserveScroll: true,
    onSuccess: () => closeTransactionModal(),
  }

  if (isEditingTransaction.value) {
    transactionForm.transform(() => payload).patch(`${transactionsBaseUrl}/${encodeURIComponent(editingTransactionId.value)}`, options)
    return
  }

  transactionForm.transform(() => payload).post(transactionsBaseUrl, options)
}

function deleteTransaction(transactionId) {
  if (!confirm('Hapus transaksi ini?')) {
    return
  }

  router.delete(`${transactionsBaseUrl}/${encodeURIComponent(transactionId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedTransactionId.value === transactionId) {
        selectedTransactionId.value = transactionItems.value.find((transaction) => transaction.id !== transactionId)?.id || null
      }
    },
  })
}

function exportExcel() {
  const query = new URLSearchParams(compactFilters(filterState.value)).toString()
  const href = query ? `${transactionsBaseUrl}/export?${query}` : `${transactionsBaseUrl}/export`
  window.open(href, '_blank')
}

function printPdf() {
  window.print()
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
}

function transactionTypeClass(type) {
  return type === 'income'
    ? 'bg-emerald-100 text-emerald-700'
    : 'bg-rose-100 text-rose-700'
}

function selectedClientHint() {
  const invoice = props.filterOptions.invoices.find((item) => item.id === transactionForm.invoice_id)
  if (transactionForm.client_hint) {
    return transactionForm.client_hint
  }

  return invoice ? `Invoice linked: ${invoice.name}` : 'Client will follow project / invoice relation'
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function todayDate() {
  return new Date().toISOString().slice(0, 10)
}

function cloneTransactions(items) {
  return items.map((transaction) => ({
    ...transaction,
    account: transaction.account ? { ...transaction.account } : null,
    invoice: transaction.invoice ? { ...transaction.invoice } : null,
    project: transaction.project ? { ...transaction.project } : null,
    client: transaction.client ? { ...transaction.client } : null,
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
