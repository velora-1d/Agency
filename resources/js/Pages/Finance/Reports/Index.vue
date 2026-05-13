<template>
  <WorkspaceLayout
    title="Laporan Keuangan"
    subtitle="Menu 23 difokuskan untuk laba rugi, arus kas, neraca, HPP proyek, laporan pajak, laporan divisi, laporan karyawan, dan bagan akun."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <select v-model="periodState" @change="applyPeriod" class="rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 outline-none transition-all hover:border-stone-300">
          <option v-for="period in filterOptions.periods" :key="period.value" :value="period.value">{{ period.label }}</option>
        </select>
        <button type="button" @click="openChartModal()" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800">
          <Plus class="h-4 w-4" />
          <span>COA Baru</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 23 / Laporan Keuangan</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Lihat performa keuangan workspace dari sudut operasional dan akuntansi dalam satu layar.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Ringkasan profit, income, expense, dan struktur akun tetap ada, tapi header sekarang dibuat tipis supaya chart dan laporan detail lebih menonjol.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Laba</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reports.summary.profit_label }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pemasukan</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reports.summary.income_label }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pengeluaran</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reports.summary.expense_label }}</p></div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">COA</p><p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ reports.summary.active_coa_count }}</p></div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Akun</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ chartSummary.total_accounts }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aset</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ reports.balanceSheet.assets_label }}</p></div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5"><p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Liabilitas</p><p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ reports.balanceSheet.liabilities_label }}</p></div>
        </div>
      </section>

      <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]"><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Laba Rugi</p><div class="mt-4 space-y-2 text-sm text-stone-600"><p>Pendapatan: {{ reports.profitLoss.revenue_label }}</p><p>Pengeluaran: {{ reports.profitLoss.expense_label }}</p><p class="font-semibold text-stone-950">Laba Kotor: {{ reports.profitLoss.gross_profit_label }}</p></div></article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]"><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Arus Kas</p><div class="mt-4 space-y-2 text-sm text-stone-600"><p>Masuk: {{ reports.cashFlow.inflow_label }}</p><p>Keluar: {{ reports.cashFlow.outflow_label }}</p><p class="font-semibold text-stone-950">Arus Bersih: {{ reports.cashFlow.net_flow_label }}</p></div></article>
        <article class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]"><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Neraca</p><div class="mt-4 space-y-2 text-sm text-stone-600"><p>Aset: {{ reports.balanceSheet.assets_label }}</p><p>Liabilitas: {{ reports.balanceSheet.liabilities_label }}</p><p class="font-semibold text-stone-950">Ekuitas: {{ reports.balanceSheet.equity_label }}</p></div></article>
      </section>

      <section class="grid gap-4 xl:grid-cols-2">
        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="border-b border-stone-200 pb-5"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">HPP per Proyek</p><h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Gabungan biaya operasional dan fee tim per proyek.</h2></div>
          <div class="mt-5 space-y-3">
            <article v-for="project in reports.projectCosts" :key="project.id" class="rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4">
              <div class="flex items-start justify-between gap-3">
                <div><p class="text-sm font-semibold text-stone-950">{{ project.name }}</p><p class="mt-1 text-xs text-stone-500">{{ project.client_name || '-' }}</p></div>
                <p class="text-sm font-semibold text-stone-950">{{ project.hpp_label }}</p>
              </div>
              <div class="mt-3 grid gap-2 text-sm text-stone-600"><p>Pengeluaran: {{ project.expense_label }}</p><p>Fee Tim: {{ project.team_fee_label }}</p></div>
            </article>
            <div v-if="reports.projectCosts.length === 0" class="rounded-[1.3rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
              Belum ada data HPP project pada periode ini.
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="border-b border-stone-200 pb-5"><p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Laporan</p><h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Laporan per divisi dan per karyawan.</h2></div>
          <div class="mt-5 grid gap-4 xl:grid-cols-2">
            <div class="space-y-3">
              <p class="text-sm font-semibold text-stone-950">Per Divisi</p>
              <article v-for="division in reports.divisionReports" :key="division.department" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-950">{{ division.department }}</p>
                <div class="mt-3 grid gap-2 text-sm text-stone-600"><p>Terpakai: {{ division.spent_label }}</p><p>Anggaran: {{ division.budget_label }}</p><p>Selisih: {{ division.variance_label }}</p></div>
              </article>
              <div v-if="reports.divisionReports.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                Belum ada laporan divisi.
              </div>
            </div>
            <div class="space-y-3">
              <p class="text-sm font-semibold text-stone-950">Per Karyawan</p>
              <article v-for="employee in reports.employeeReports" :key="employee.user_name" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-950">{{ employee.user_name }}</p>
                <div class="mt-3 grid gap-2 text-sm text-stone-600"><p>Penghasilan: {{ employee.earning_label }}</p><p>Klaim: {{ employee.claims_label }}</p><p>Bersih: {{ employee.net_label }}</p></div>
              </article>
              <div v-if="reports.employeeReports.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
                Belum ada laporan karyawan.
              </div>
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Bagan Akun</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Kelola akun dasar untuk struktur laporan finance.</h2>
          </div>
          <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600"><span class="font-semibold text-stone-950">{{ chartSummary.total_accounts }}</span> akun</div>
        </div>
        <div class="mt-5 grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
          <article v-for="account in chartItems" :key="account.id" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-base font-semibold text-stone-950">{{ account.code }} | {{ account.name }}</p>
                <p class="mt-1 text-sm text-stone-500">{{ account.type_label }}{{ account.category ? ` | ${account.category}` : '' }}</p>
              </div>
              <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="account.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-600'">{{ account.is_active_label }}</span>
            </div>
            <p class="mt-4 text-sm leading-6 text-stone-600">{{ account.notes || 'Tidak ada catatan.' }}</p>
            <div class="mt-4 flex gap-2">
              <button type="button" @click="openChartModal(account)" class="rounded-full border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-700 transition hover:border-stone-300">Ubah</button>
              <button type="button" @click="deleteChart(account.id)" class="rounded-full border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">Hapus</button>
            </div>
          </article>
          <div v-if="chartItems.length === 0" class="xl:col-span-2 2xl:col-span-3 rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500">
            Belum ada chart of accounts yang dibuat.
          </div>
        </div>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showChartModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-3xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div><p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Bagan Akun</p><h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingChart ? 'Ubah Bagan Akun' : 'Buat Bagan Akun' }}</h3></div>
            <button type="button" @click="closeChartModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700"><X class="h-5 w-5" /></button>
          </div>
          <form class="mt-6 space-y-5" @submit.prevent="submitChart">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kode</span><input v-model="chartForm.code" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama</span><input v-model="chartForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span><select v-model="chartForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"><option v-for="type in filterOptions.accountTypes" :key="type.value" :value="type.value">{{ type.label }}</option></select></label>
              <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kategori</span><input v-model="chartForm.category" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" /></label>
            </div>
            <label class="space-y-2 text-sm"><span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</span><textarea v-model="chartForm.notes" rows="5" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea></label>
            <label class="inline-flex items-center gap-3 text-sm font-medium text-stone-700"><input v-model="chartForm.is_active" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" /><span>COA aktif</span></label>
            <div class="flex flex-wrap items-center justify-end gap-3"><button type="button" @click="closeChartModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button><button type="submit" :disabled="chartForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60"><Sparkles class="h-4 w-4" /><span>{{ isEditingChart ? 'Simpan COA' : 'Buat COA' }}</span></button></div>
          </form>
        </div>
      </div>
    </Transition>
    </FinanceLayout>
  </WorkspaceLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Plus, Sparkles, X } from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  reports: { type: Object, required: true },
  chartOfAccounts: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})
const baseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/reports`

const periodState = ref(props.filters.period || 'month')
const showChartModal = ref(false)
const editingChartId = ref(null)

const chartForm = useForm({
  code: '',
  name: '',
  type: 'asset',
  category: '',
  is_active: true,
  notes: '',
})

const reports = computed(() => props.reports)
const chartSummary = computed(() => props.chartOfAccounts.summary)
const chartItems = computed(() => props.chartOfAccounts.items || [])
const isEditingChart = computed(() => Boolean(editingChartId.value))

function applyPeriod() {
  router.get(baseUrl, { period: periodState.value }, { preserveState: true, preserveScroll: true })
}

function openChartModal(account = null) {
  editingChartId.value = account?.id || null
  chartForm.reset()
  chartForm.clearErrors()
  chartForm.code = account?.code || ''
  chartForm.name = account?.name || ''
  chartForm.type = account?.type || 'asset'
  chartForm.category = account?.category || ''
  chartForm.is_active = account?.is_active ?? true
  chartForm.notes = account?.notes || ''
  showChartModal.value = true
}

function closeChartModal() {
  showChartModal.value = false
  editingChartId.value = null
  chartForm.reset()
  chartForm.clearErrors()
  chartForm.type = 'asset'
  chartForm.is_active = true
}

function submitChart() {
  const options = { preserveScroll: true, onSuccess: () => closeChartModal() }
  if (isEditingChart.value) {
    chartForm.patch(`${baseUrl}/chart-of-accounts/${encodeURIComponent(editingChartId.value)}`, options)
    return
  }
  chartForm.post(`${baseUrl}/chart-of-accounts`, options)
}

function deleteChart(id) {
  if (!confirm('Hapus bagan akun ini?')) return
  router.delete(`${baseUrl}/chart-of-accounts/${encodeURIComponent(id)}`, { preserveScroll: true })
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; transform: scale(0.96); }
</style>
