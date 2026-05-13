<template>
  <WorkspaceLayout
    title="Pengeluaran & Reimbursement"
    subtitle="Menu 22 difokuskan untuk submit expense tim, approve/reject, petty cash, budget per project, dan budget per divisi."
  >
    <template #actions>
      <button type="button" @click="openReimbursementModal()" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800">
        <Plus class="h-4 w-4" />
        <span>Pengeluaran Baru</span>
      </button>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 22 / Pengeluaran & Reimbursement</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Tim submit pengeluaran, admin approve/reject, lalu reimbursement paid masuk ke petty cash.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">Budget proyek dan divisi tetap dipantau, tapi header dibuat lebih pendek supaya antrian expense lebih menonjol.</p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pending</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.pending_reimbursements }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.total_reimbursements }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Disetujui</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.approved_reimbursements }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Lunas</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.paid_reimbursements }}</p></div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-4">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Budget Alerts</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.budget_alerts }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total Reimbursement</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.total_reimbursement_amount_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Petty Cash</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ reimbursementSummary.petty_cash_balance_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Lensa Pengeluaran</p><p class="mt-2 text-sm leading-6 text-stone-600">Budget divisi dipantau dari reimbursement lunas.</p></div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari pengeluaran berdasarkan judul, status, divisi, atau proyek.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ reimbursementItems.length }}</span> pengeluaran tampil
          </div>
        </div>
        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Department</span>
            <select v-model="filterState.department" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Divisi</option>
              <option v-for="department in filterOptions.departments" :key="department.value" :value="department.value">{{ department.label }}</option>
            </select>
          </label>
          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span>
            <select v-model="filterState.project" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Proyek</option>
              <option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </label>
        </div>
        <div class="filter-actions mt-5 flex flex-wrap items-center gap-3">
          <button type="button" @click="applyFilters" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-stone-800"><Filter class="h-4 w-4" /><span>Terapkan Filter</span></button>
          <button type="button" @click="resetFilters" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900"><RotateCcw class="h-4 w-4" /><span>Atur Ulang</span></button>
        </div>
      </section>

      <section class="grid gap-4 xl:grid-cols-[0.92fr_1.08fr]">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Pengeluaran</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua reimbursement yang perlu diproses.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ reimbursementItems.length }} items</span>
          </div>
          <div class="mt-5 space-y-4">
            <article v-for="reimbursement in reimbursementItems" :key="reimbursement.id" class="rounded-[1.6rem] border p-5 transition" :class="selectedReimbursement?.id === reimbursement.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <button type="button" @click="selectReimbursement(reimbursement.id)" class="text-left text-base font-semibold transition" :class="selectedReimbursement?.id === reimbursement.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">{{ reimbursement.title }}</button>
                  <p class="mt-2 text-sm" :class="selectedReimbursement?.id === reimbursement.id ? 'text-stone-300' : 'text-stone-500'">{{ reimbursement.user?.name || '-' }} | {{ reimbursement.department || 'No department' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedReimbursement?.id === reimbursement.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ reimbursement.status_label }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedReimbursement?.id === reimbursement.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ reimbursement.amount_label }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-lg font-semibold" :class="selectedReimbursement?.id === reimbursement.id ? 'text-white' : 'text-stone-950'">{{ reimbursement.budget_status }}</p>
                  <p class="mt-1 text-sm" :class="selectedReimbursement?.id === reimbursement.id ? 'text-stone-300' : 'text-stone-500'">budget state</p>
                </div>
              </div>
            </article>
            <div v-if="reimbursementItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">Belum ada pengeluaran yang cocok dengan filter saat ini.</div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedReimbursement">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Pengeluaran</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedReimbursement.title }}</h2>
                <p class="mt-2 text-sm text-stone-500">{{ selectedReimbursement.user?.name || '-' }} | {{ selectedReimbursement.project?.name || 'Belum terhubung ke proyek' }}</p>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openReimbursementModal(selectedReimbursement)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950"><Pencil class="h-4 w-4" /><span>Ubah</span></button>
                <button type="button" @click="updateStatus(selectedReimbursement.id, 'approved')" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"><CheckCircle2 class="h-4 w-4" /><span>Setujui</span></button>
                <button type="button" @click="updateStatus(selectedReimbursement.id, 'rejected')" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"><XCircle class="h-4 w-4" /><span>Tolak</span></button>
                <button type="button" @click="openPayModal(selectedReimbursement)" class="inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-100"><Wallet class="h-4 w-4" /><span>Tandai Lunas</span></button>
                <button type="button" @click="deleteReimbursement(selectedReimbursement.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"><Trash2 class="h-4 w-4" /><span>Hapus</span></button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)] p-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Jumlah</p><p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedReimbursement.amount_label }}</p></div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Department</p><p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedReimbursement.department || '-' }}</p></div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Disetujui</p><p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedReimbursement.approved_at_label || 'Belum' }}</p></div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4"><p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Lunas</p><p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedReimbursement.paid_at_label || 'Belum' }}</p></div>
                </div>
              </article>
              <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</p>
                  <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ selectedReimbursement.notes || 'Tidak ada catatan.' }}</div>
                </article>
                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Payment Flow</p>
                  <div class="mt-4 space-y-3">
                    <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                      <p class="font-semibold text-stone-950">Status</p>
                      <p class="mt-2">{{ selectedReimbursement.status_label }}</p>
                    </div>
                    <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                      <p class="font-semibold text-stone-950">Akun Pembayaran</p>
                      <p class="mt-2">{{ selectedReimbursement.paid_account?.name || 'Belum diarahkan ke akun kas.' }}</p>
                    </div>
                    <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                      <p class="font-semibold text-stone-950">Budget Signal</p>
                      <p class="mt-2">{{ selectedReimbursement.budget_status }}</p>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </template>
          <div v-else class="flex min-h-[420px] items-center justify-center rounded-[1.8rem] border border-dashed border-stone-200 bg-stone-50 p-8 text-center">
            <div><p class="text-lg font-semibold text-stone-950">Belum ada expense terpilih.</p><p class="mt-2 text-sm leading-6 text-stone-500">Pilih item dari daftar kiri atau submit expense baru.</p></div>
          </div>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Budgets</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Budget per project dan budget per divisi.</h2>
          </div>
          <button type="button" @click="openBudgetModal()" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950"><Plus class="h-4 w-4" /><span>Anggaran Baru</span></button>
        </div>
        <div class="mt-5 grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
          <article v-for="budget in budgetItems" :key="budget.id" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-base font-semibold text-stone-950">{{ budget.department }}</p>
                <p class="mt-1 text-sm text-stone-500">{{ budget.period_label }}</p>
              </div>
              <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="budget.over_budget ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">{{ budget.over_budget ? 'Over' : 'OK' }}</span>
            </div>
            <div class="mt-4 space-y-2 text-sm text-stone-600">
              <p>Limit: {{ budget.limit_amount_label }}</p>
              <p>Spent: {{ budget.spent_amount_label }}</p>
              <p>Remaining: {{ budget.remaining_amount_label }}</p>
            </div>
            <div class="mt-4 flex gap-2">
              <button type="button" @click="openBudgetModal(budget)" class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 transition hover:border-stone-300">Ubah</button>
              <button type="button" @click="deleteBudget(budget.id)" class="rounded-full border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">Hapus</button>
            </div>
          </article>
          <div v-if="budgetItems.length === 0" class="xl:col-span-2 2xl:col-span-3 rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada budget divisi yang dibuat.
          </div>
        </div>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showReimbursementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-4xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Pengeluaran</p><h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingReimbursement ? 'Ubah Pengeluaran' : 'Buat Pengeluaran' }}</h3></div>
            <button type="button" @click="closeReimbursementModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitReimbursement">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Title</span><input v-model="reimbursementForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span><input v-model.number="reimbursementForm.amount" type="number" min="0" step="1000" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">User</span><select v-model="reimbursementForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"><option value="">Select user</option><option v-for="user in filterOptions.users" :key="user.id" :value="user.id">{{ user.name }}</option></select></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proyek</span><select v-model="reimbursementForm.project_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"><option value="">Tanpa proyek</option><option v-for="project in filterOptions.projects" :key="project.id" :value="project.id">{{ project.name }}</option></select></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Department</span><input v-model="reimbursementForm.department" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span><select v-model="reimbursementForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"><option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option></select></label>
            </div>
            <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</span><textarea v-model="reimbursementForm.notes" rows="5" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea></label>
            <div class="flex flex-wrap items-center justify-end gap-3"><button type="button" @click="closeReimbursementModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button><button type="submit" :disabled="reimbursementForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><Sparkles class="h-4 w-4" /><span>{{ isEditingReimbursement ? 'Simpan Pengeluaran' : 'Buat Pengeluaran' }}</span></button></div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showBudgetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Anggaran</p><h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingBudget ? 'Ubah Anggaran' : 'Buat Anggaran' }}</h3></div>
            <button type="button" @click="closeBudgetModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitBudget">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Department</span><input v-model="budgetForm.department" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Periode</span><select v-model="budgetForm.period_label" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"><option value="monthly">Bulanan</option><option value="quarterly">Triwulanan</option><option value="yearly">Tahunan</option></select></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Limit</span><input v-model.number="budgetForm.limit_amount" type="number" min="0" step="1000" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
            </div>
            <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</span><textarea v-model="budgetForm.notes" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea></label>
            <div class="flex flex-wrap items-center justify-end gap-3"><button type="button" @click="closeBudgetModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button><button type="submit" :disabled="budgetForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><Sparkles class="h-4 w-4" /><span>{{ isEditingBudget ? 'Simpan Anggaran' : 'Buat Anggaran' }}</span></button></div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Bayar Pengeluaran</p><h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Tandai {{ payTarget?.title }} sebagai lunas</h3></div>
            <button type="button" @click="closePayModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitPay">
            <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Akun Kas</span><select v-model="payForm.paid_account_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"><option value="">Pilih akun</option><option v-for="account in filterOptions.accounts" :key="account.id" :value="account.id">{{ account.name }}</option></select></label>
            <div class="flex flex-wrap items-center justify-end gap-3"><button type="button" @click="closePayModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button><button type="submit" :disabled="payForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><Wallet class="h-4 w-4" /><span>Konfirmasi Lunas</span></button></div>
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
import { CheckCircle2, Filter, Pencil, Plus, RotateCcw, Sparkles, Trash2, X, Wallet } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  reimbursements: { type: Object, required: true },
  budgets: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/expenses`

const localReimbursements = ref(cloneReimbursements(props.reimbursements.items || []))
const localBudgets = ref(cloneBudgets(props.budgets.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedReimbursementId = ref(props.reimbursements.selected_id || props.reimbursements.items?.[0]?.id || null)
const showReimbursementModal = ref(false)
const showBudgetModal = ref(false)
const showPayModal = ref(false)
const editingReimbursementId = ref(null)
const editingBudgetId = ref(null)
const payTarget = ref(null)

const reimbursementForm = useForm({ user_id: '', project_id: '', department: '', title: '', amount: 0, status: 'pending', notes: '' })
const budgetForm = useForm({ department: '', period_label: 'monthly', limit_amount: 0, notes: '' })
const payForm = useForm({ paid_account_id: '' })

const reimbursementSummary = computed(() => props.reimbursements.summary)
const reimbursementItems = computed(() => localReimbursements.value)
const budgetItems = computed(() => localBudgets.value)
const selectedReimbursement = computed(() => reimbursementItems.value.find((item) => item.id === selectedReimbursementId.value) || reimbursementItems.value[0] || null)
const isEditingReimbursement = computed(() => Boolean(editingReimbursementId.value))
const isEditingBudget = computed(() => Boolean(editingBudgetId.value))

watch(() => props.reimbursements.items, (items) => {
  localReimbursements.value = cloneReimbursements(items || [])
  if (!selectedReimbursementId.value || !localReimbursements.value.some((item) => item.id === selectedReimbursementId.value)) {
    selectedReimbursementId.value = localReimbursements.value[0]?.id || null
  }
})

watch(() => props.budgets.items, (items) => {
  localBudgets.value = cloneBudgets(items || [])
})

watch(() => props.filters, (filters) => { filterState.value = buildFilterState(filters) }, { deep: true })

function buildFilterState(filters = {}) {
  return { search: filters.search ?? '', status: filters.status ?? '', department: filters.department ?? '', project: filters.project ?? '' }
}

function applyFilters() { router.get(baseUrl, compactFilters(filterState.value), { preserveState: true, preserveScroll: true }) }
function resetFilters() { filterState.value = buildFilterState(); router.get(baseUrl, {}, { preserveState: true, preserveScroll: true }) }
function selectReimbursement(id) { selectedReimbursementId.value = id }

function openReimbursementModal(reimbursement = null) {
  editingReimbursementId.value = reimbursement?.id || null
  reimbursementForm.reset(); reimbursementForm.clearErrors()
  reimbursementForm.user_id = reimbursement?.user_id || ''
  reimbursementForm.project_id = reimbursement?.project_id || ''
  reimbursementForm.department = reimbursement?.department || ''
  reimbursementForm.title = reimbursement?.title || ''
  reimbursementForm.amount = reimbursement?.amount ?? 0
  reimbursementForm.status = reimbursement?.status || 'pending'
  reimbursementForm.notes = reimbursement?.notes || ''
  showReimbursementModal.value = true
}
function closeReimbursementModal() { showReimbursementModal.value = false; editingReimbursementId.value = null; reimbursementForm.reset(); reimbursementForm.clearErrors(); reimbursementForm.status = 'pending'; reimbursementForm.amount = 0 }
function submitReimbursement() {
  const options = { preserveScroll: true, onSuccess: () => closeReimbursementModal() }
  if (isEditingReimbursement.value) { reimbursementForm.patch(`${baseUrl}/reimbursements/${encodeURIComponent(editingReimbursementId.value)}`, options); return }
  reimbursementForm.post(`${baseUrl}/reimbursements`, options)
}

function openBudgetModal(budget = null) {
  editingBudgetId.value = budget?.id || null
  budgetForm.reset(); budgetForm.clearErrors()
  budgetForm.department = budget?.department || ''
  budgetForm.period_label = budget?.period_label || 'monthly'
  budgetForm.limit_amount = budget?.limit_amount ?? 0
  budgetForm.notes = budget?.notes || ''
  showBudgetModal.value = true
}
function closeBudgetModal() { showBudgetModal.value = false; editingBudgetId.value = null; budgetForm.reset(); budgetForm.clearErrors(); budgetForm.period_label = 'monthly'; budgetForm.limit_amount = 0 }
function submitBudget() {
  const options = { preserveScroll: true, onSuccess: () => closeBudgetModal() }
  if (isEditingBudget.value) { budgetForm.patch(`${baseUrl}/budgets/${encodeURIComponent(editingBudgetId.value)}`, options); return }
  budgetForm.post(`${baseUrl}/budgets`, options)
}

function updateStatus(id, status) { router.patch(`${baseUrl}/reimbursements/${encodeURIComponent(id)}/status`, { status }, { preserveScroll: true }) }
function deleteBudget(id) { if (confirm('Hapus anggaran ini?')) router.delete(`${baseUrl}/budgets/${encodeURIComponent(id)}`, { preserveScroll: true }) }

function openPayModal(reimbursement) { payTarget.value = reimbursement; payForm.reset(); payForm.clearErrors(); payForm.paid_account_id = reimbursement.paid_account_id || ''; showPayModal.value = true }
function closePayModal() { showPayModal.value = false; payTarget.value = null; payForm.reset(); payForm.clearErrors() }
function submitPay() { if (!payTarget.value) return; router.patch(`${baseUrl}/reimbursements/${encodeURIComponent(payTarget.value.id)}/status`, { status: 'paid', paid_account_id: payForm.paid_account_id }, { preserveScroll: true, onSuccess: () => closePayModal() }) }
function deleteReimbursement(id) { if (confirm('Hapus pengeluaran ini?')) router.delete(`${baseUrl}/reimbursements/${encodeURIComponent(id)}`, { preserveScroll: true }) }

function compactFilters(filters) { return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined)) }
function cloneReimbursements(items) { return items.map((item) => ({ ...item, user: item.user ? { ...item.user } : null, project: item.project ? { ...item.project } : null, paid_account: item.paid_account ? { ...item.paid_account } : null })) }
function cloneBudgets(items) { return items.map((item) => ({ ...item })) }
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>
