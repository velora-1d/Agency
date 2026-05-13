<template>
  <WorkspaceLayout
    title="Kas & Bank"
    subtitle="Menu 21 difokuskan untuk multi rekening, kas tunai, mutasi per rekening, transfer antar akun, saldo real-time, dan rekonsiliasi."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button type="button" @click="showTransferModal = true" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950">
          <ArrowLeftRight class="h-4 w-4" />
          <span>Transfer Antar Akun</span>
        </button>
        <button type="button" @click="openAccountModal()" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800">
          <Plus class="h-4 w-4" />
          <span>Akun Baru</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 21 / Kas & Bank</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Kelola rekening dan kas dalam satu tempat, lengkap dengan mutasi, transfer, dan rekonsiliasi.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">Saldo real-time tetap diambil dari transaksi, tapi area atas dibuat lebih ramping supaya mutasi harian lebih dominan.</p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Gabungan</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ accountSummary.combined_balance_label }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Akun</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ accountSummary.total_accounts }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ accountSummary.active_accounts }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Transfer</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ accountSummary.transfers_this_month_label }}</p></div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Bank</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ accountSummary.bank_balance_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Tunai</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ accountSummary.cash_balance_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Rekonsiliasi</p><p class="mt-2 text-sm leading-6 text-stone-600">Rekonsiliasi tetap disimpan per akun.</p></div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari rekening berdasarkan nama, bank, tipe akun, atau status aktif.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ accountItems.length }}</span> accounts tampil
          </div>
        </div>
        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.active" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="state in filterOptions.activeStates" :key="state.value" :value="state.value">{{ state.label }}</option>
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
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Akun</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua rekening dan kas workspace.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ accountItems.length }} akun</span>
          </div>
          <div class="mt-5 space-y-4">
            <article
              v-for="account in accountItems"
              :key="account.id"
              class="rounded-[1.6rem] border p-5 transition"
              :class="selectedAccount?.id === account.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <button type="button" @click="selectAccount(account.id)" class="text-left text-base font-semibold transition" :class="selectedAccount?.id === account.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">{{ account.name }}</button>
                  <p class="mt-2 text-sm" :class="selectedAccount?.id === account.id ? 'text-stone-300' : 'text-stone-500'">{{ account.bank_name || account.type_label }} | {{ account.account_number || 'Tidak ada nomor' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedAccount?.id === account.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ account.type_label }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedAccount?.id === account.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ account.is_active_label }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-lg font-semibold" :class="selectedAccount?.id === account.id ? 'text-white' : 'text-stone-950'">{{ account.balance_label }}</p>
                  <p class="mt-1 text-sm" :class="selectedAccount?.id === account.id ? 'text-stone-300' : 'text-stone-500'">Saldo saat ini</p>
                </div>
              </div>
            </article>
            <div v-if="accountItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">Belum ada akun yang cocok dengan filter saat ini.</div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedAccount">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Akun</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedAccount.name }}</h2>
                <p class="mt-2 text-sm text-stone-500">{{ selectedAccount.bank_name || selectedAccount.type_label }}</p>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openReconcileModal(selectedAccount)" class="inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
                  <CheckCircle2 class="h-4 w-4" />
                  <span>Rekonsiliasi</span>
                </button>
                <button type="button" @click="openAccountModal(selectedAccount)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="deleteAccount(selectedAccount.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                  <Trash2 class="h-4 w-4" />
                  <span>Hapus</span>
                </button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)] p-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Tipe</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedAccount.type_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Pemilik</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedAccount.account_holder || '-' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Nomor</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedAccount.account_number || '-' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Direkonsiliasi</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedAccount.last_reconciled_at_label || 'Belum ada' }}</p>
                  </div>
                </div>
              </article>

              <div class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Mutasi</p>
                  <div class="mt-4 space-y-3">
                    <article v-for="transaction in selectedAccount.transactions" :key="transaction.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <div class="flex items-start justify-between gap-3">
                        <div>
                          <p class="text-sm font-semibold text-stone-950">{{ transaction.category || transaction.type_label }}</p>
                          <p class="mt-1 text-xs text-stone-500">{{ transaction.date_label }}</p>
                          <p class="mt-2 text-sm text-stone-600">{{ transaction.description || '-' }}</p>
                        </div>
                        <div class="text-right">
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="transaction.type === 'income' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">{{ transaction.type_label }}</span>
                          <p class="mt-2 text-sm font-semibold text-stone-950">{{ transaction.amount_label }}</p>
                        </div>
                      </div>
                    </article>
                    <div v-if="selectedAccount.transactions.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">Belum ada mutasi untuk akun ini.</div>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan Rekonsiliasi</p>
                  <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">
                    {{ selectedAccount.reconciliation_notes || 'Belum ada catatan rekonsiliasi.' }}
                  </div>
                </article>
              </div>
            </div>
          </template>

          <div v-else class="flex min-h-[420px] items-center justify-center rounded-[1.8rem] border border-dashed border-stone-200 bg-stone-50 p-8 text-center">
            <div>
              <p class="text-lg font-semibold text-stone-950">Belum ada akun terpilih.</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Pilih akun dari daftar kiri atau buat akun baru.</p>
            </div>
          </div>
        </article>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-3xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Formulir Akun</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingAccount ? 'Ubah Akun' : 'Buat Akun' }}</h3>
            </div>
            <button type="button" @click="closeAccountModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitAccount">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Akun</span>
                <input v-model="accountForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
                <select v-model="accountForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="type in filterOptions.types" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Bank</span>
                <input v-model="accountForm.bank_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nomor Rekening</span>
                <input v-model="accountForm.account_number" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Pemilik</span>
                <input v-model="accountForm.account_holder" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm" v-if="!isEditingAccount">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Saldo Awal</span>
                <input v-model.number="accountForm.opening_balance" type="number" min="0" step="1000" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>
            <label class="inline-flex items-center gap-3 text-sm font-medium text-stone-700">
              <input v-model="accountForm.is_active" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" />
              <span>Akun aktif</span>
            </label>
            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeAccountModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
              <button type="submit" :disabled="accountForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><Sparkles class="h-4 w-4" /><span>{{ isEditingAccount ? 'Simpan Akun' : 'Buat Akun' }}</span></button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showTransferModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Formulir Transfer</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Transfer Antar Akun</h3>
            </div>
            <button type="button" @click="closeTransferModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitTransfer">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dari</span>
                <select v-model="transferForm.from_account_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Pilih sumber</option>
                  <option v-for="account in filterOptions.accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Ke</span>
                <select v-model="transferForm.to_account_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Pilih tujuan</option>
                  <option v-for="account in filterOptions.accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                </select>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span>
                <input v-model.number="transferForm.amount" type="number" min="1" step="1000" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Date</span>
                <input v-model="transferForm.date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>
            <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Keterangan</span>
              <textarea v-model="transferForm.description" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>
            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeTransferModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
              <button type="submit" :disabled="transferForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><ArrowLeftRight class="h-4 w-4" /><span>Catat Transfer</span></button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showReconcileModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Formulir Rekonsiliasi</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Rekonsiliasi {{ reconcileTarget?.name }}</h3>
            </div>
            <button type="button" @click="closeReconcileModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitReconcile">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Rekonsiliasi</span>
              <input v-model="reconcileForm.last_reconciled_at" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</span>
              <textarea v-model="reconcileForm.reconciliation_notes" rows="5" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>
            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeReconcileModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
              <button type="submit" :disabled="reconcileForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><CheckCircle2 class="h-4 w-4" /><span>Simpan Rekonsiliasi</span></button>
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
import { ArrowLeftRight, CheckCircle2, Filter, Pencil, Plus, RotateCcw, Sparkles, Trash2, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  accounts: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/cash-bank`

const localAccounts = ref(cloneAccounts(props.accounts.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedAccountId = ref(props.accounts.selected_id || props.accounts.items?.[0]?.id || null)
const showAccountModal = ref(false)
const showTransferModal = ref(false)
const showReconcileModal = ref(false)
const editingAccountId = ref(null)
const reconcileTarget = ref(null)

const accountForm = useForm({
  name: '',
  bank_name: '',
  account_number: '',
  account_holder: '',
  type: 'bank',
  is_active: true,
  opening_balance: 0,
})

const transferForm = useForm({
  from_account_id: '',
  to_account_id: '',
  amount: 0,
  date: '',
  description: '',
})

const reconcileForm = useForm({
  last_reconciled_at: '',
  reconciliation_notes: '',
})

const accountSummary = computed(() => props.accounts.summary)
const accountItems = computed(() => localAccounts.value)
const selectedAccount = computed(() => accountItems.value.find((account) => account.id === selectedAccountId.value) || accountItems.value[0] || null)
const isEditingAccount = computed(() => Boolean(editingAccountId.value))

watch(() => props.accounts.items, (items) => {
  localAccounts.value = cloneAccounts(items || [])
  if (!selectedAccountId.value || !localAccounts.value.some((account) => account.id === selectedAccountId.value)) {
    selectedAccountId.value = localAccounts.value[0]?.id || null
  }
})

watch(() => props.filters, (filters) => {
  filterState.value = buildFilterState(filters)
}, { deep: true })

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    type: filters.type ?? '',
    active: filters.active ?? '',
  }
}

function applyFilters() {
  router.get(baseUrl, compactFilters(filterState.value), { preserveState: true, preserveScroll: true })
}

function resetFilters() {
  filterState.value = buildFilterState()
  router.get(baseUrl, {}, { preserveState: true, preserveScroll: true })
}

function selectAccount(accountId) {
  selectedAccountId.value = accountId
}

function openAccountModal(account = null) {
  editingAccountId.value = account?.id || null
  accountForm.reset()
  accountForm.clearErrors()
  accountForm.name = account?.name || ''
  accountForm.bank_name = account?.bank_name || ''
  accountForm.account_number = account?.account_number || ''
  accountForm.account_holder = account?.account_holder || ''
  accountForm.type = account?.type || 'bank'
  accountForm.is_active = account?.is_active ?? true
  accountForm.opening_balance = 0
  showAccountModal.value = true
}

function closeAccountModal() {
  showAccountModal.value = false
  editingAccountId.value = null
  accountForm.reset()
  accountForm.clearErrors()
  accountForm.type = 'bank'
  accountForm.is_active = true
}

function submitAccount() {
  const options = { preserveScroll: true, onSuccess: () => closeAccountModal() }
  if (isEditingAccount.value) {
    accountForm.patch(`${baseUrl}/${encodeURIComponent(editingAccountId.value)}`, options)
    return
  }
  accountForm.post(baseUrl, options)
}

function deleteAccount(accountId) {
  if (!confirm('Hapus akun ini?')) return
  router.delete(`${baseUrl}/${encodeURIComponent(accountId)}`, { preserveScroll: true })
}

function closeTransferModal() {
  showTransferModal.value = false
  transferForm.reset()
  transferForm.clearErrors()
}

function submitTransfer() {
  transferForm.post(`${baseUrl}/transfer`, { preserveScroll: true, onSuccess: () => closeTransferModal() })
}

function openReconcileModal(account) {
  reconcileTarget.value = account
  reconcileForm.reset()
  reconcileForm.clearErrors()
  reconcileForm.last_reconciled_at = account.last_reconciled_at || ''
  reconcileForm.reconciliation_notes = account.reconciliation_notes || ''
  showReconcileModal.value = true
}

function closeReconcileModal() {
  showReconcileModal.value = false
  reconcileTarget.value = null
  reconcileForm.reset()
  reconcileForm.clearErrors()
}

function submitReconcile() {
  if (!reconcileTarget.value) return
  reconcileForm.patch(`${baseUrl}/${encodeURIComponent(reconcileTarget.value.id)}/reconcile`, { preserveScroll: true, onSuccess: () => closeReconcileModal() })
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneAccounts(items) {
  return items.map((account) => ({
    ...account,
    transactions: Array.isArray(account.transactions) ? account.transactions.map((transaction) => ({ ...transaction })) : [],
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
