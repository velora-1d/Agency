<template>
  <WorkspaceLayout
    title="Langganan"
    subtitle="Menu 18 difokuskan untuk langganan tools tim, pengelolaan vendor, pengingat perpanjangan, dan keterkaitan ke transaksi pengeluaran tanpa bikin finance bingung."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openVendorModal()"
          class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-stone-300 hover:text-stone-950"
        >
          <Building2 class="h-4 w-4" />
          <span>Vendor Baru</span>
        </button>
        <button
          type="button"
          @click="openSubscriptionModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Langganan Baru</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 18 / Subscriptions</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Pantau tools tim, renewal, biaya, dan keterkaitan expense tanpa bikin finance bingung.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Vendor, renewal reminder, dan expense link tetap utuh, tapi ringkasan atasnya dibuat lebih singkat supaya daftar subscription lebih fokus.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Biaya Bulanan</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.monthly_cash_total_label }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.total_subscriptions }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Aktif</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.active_subscriptions }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Segera Jatuh Tempo</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.due_soon_subscriptions }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-4">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Expired</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.expired_subscriptions }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Pengeluaran Terkait</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.linked_expense_count }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Komitmen Tahunan</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ subscriptionSummary.annual_commitment_total_label }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Perpanjangan Berikutnya</p>
            <p class="mt-2 text-sm font-semibold text-stone-950">{{ subscriptionSummary.upcoming_renewal_label }}</p>
          </div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari langganan berdasarkan nama, vendor, status, siklus tagihan, atau status perpanjangan.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ subscriptionItems.length }}</span> subscriptions tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-5">
          <label class="space-y-2 text-sm xl:col-span-2">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
            <input v-model="filterState.search" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Vendor</span>
            <select v-model="filterState.vendor" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Vendor</option>
              <option v-for="vendor in filterOptions.vendors" :key="vendor.id" :value="vendor.id">{{ vendor.name }}</option>
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status Perpanjangan</span>
            <select v-model="filterState.renewal_state" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="state in filterOptions.renewalStates" :key="state.value" :value="state.value">{{ state.label }}</option>
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
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Papan Langganan</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Semua tools langganan yang harus terus dipantau.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ subscriptionItems.length }} tools
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article
              v-for="subscription in subscriptionItems"
              :key="subscription.id"
              class="rounded-[1.6rem] border p-5 transition"
              :class="selectedSubscription?.id === subscription.id ? 'border-stone-900 bg-stone-950 text-white shadow-[0_18px_50px_rgba(28,25,23,0.18)]' : 'border-stone-200 bg-stone-50 hover:border-stone-300 hover:bg-white'"
            >
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="selectSubscription(subscription.id)" class="text-left text-base font-semibold transition" :class="selectedSubscription?.id === subscription.id ? 'text-white' : 'text-stone-950 hover:text-sky-700'">
                      {{ subscription.name }}
                    </button>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="subscriptionStatusClass(subscription.effective_status)">
                      {{ subscription.effective_status_label }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="renewalStateClass(subscription.renewal_state)">
                      {{ subscription.renewal_state_label }}
                    </span>
                  </div>
                    <p class="mt-2 text-sm" :class="selectedSubscription?.id === subscription.id ? 'text-stone-300' : 'text-stone-500'">
                    {{ subscription.vendor?.name || 'Belum tertaut ke vendor' }}
                  </p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-3 py-1.5" :class="selectedSubscription?.id === subscription.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ subscription.billing_cycle_label }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedSubscription?.id === subscription.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ subscription.next_renewal_date_label || 'Belum ada tanggal perpanjangan' }}</span>
                    <span class="rounded-full px-3 py-1.5" :class="selectedSubscription?.id === subscription.id ? 'bg-white/10 text-stone-200' : 'bg-white text-stone-500'">{{ subscription.amount_label }}</span>
                  </div>
                </div>

                <div class="text-right">
                  <p class="text-lg font-semibold" :class="selectedSubscription?.id === subscription.id ? 'text-white' : 'text-stone-950'">{{ subscription.monthly_equivalent_label }}</p>
                  <p class="mt-1 text-sm" :class="selectedSubscription?.id === subscription.id ? 'text-stone-300' : 'text-stone-500'">setara bulanan</p>
                </div>
              </div>

              <div class="mt-5 flex flex-wrap gap-2">
                <button type="button" @click="selectSubscription(subscription.id)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedSubscription?.id === subscription.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                  <Eye class="h-3.5 w-3.5" />
                  <span>Rincian</span>
                </button>
                <button type="button" @click="openSubscriptionModal(subscription)" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedSubscription?.id === subscription.id ? 'border-white/15 text-white hover:bg-white/10' : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300 hover:text-stone-950'">
                  <Pencil class="h-3.5 w-3.5" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="markStatus(subscription.id, 'cancelled')" class="inline-flex items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="selectedSubscription?.id === subscription.id ? 'border-rose-300/30 text-rose-200 hover:bg-rose-500/10' : 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100'">
                  <Ban class="h-3.5 w-3.5" />
                  <span>Batalkan</span>
                </button>
              </div>
            </article>

            <div v-if="subscriptionItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada subscription yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <template v-if="selectedSubscription">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Langganan</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <h2 class="text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ selectedSubscription.name }}</h2>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="subscriptionStatusClass(selectedSubscription.effective_status)">
                    {{ selectedSubscription.effective_status_label }}
                  </span>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="renewalStateClass(selectedSubscription.renewal_state)">
                    {{ selectedSubscription.renewal_state_label }}
                  </span>
                </div>
                <p class="mt-2 text-sm text-stone-500">{{ selectedSubscription.vendor?.name || 'No vendor linked' }}</p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openSubscriptionModal(selectedSubscription)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-4 w-4" />
                  <span>Ubah</span>
                </button>
                <button type="button" @click="markStatus(selectedSubscription.id, 'active')" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                  <CheckCircle2 class="h-4 w-4" />
                  <span>Tandai Aktif</span>
                </button>
                <button type="button" @click="markStatus(selectedSubscription.id, 'expired')" class="inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                  <Clock3 class="h-4 w-4" />
                    <span>Tandai Kedaluwarsa</span>
                </button>
              </div>
            </div>

            <div class="mt-6 space-y-6">
              <article class="rounded-[1.8rem] border border-stone-200 bg-[linear-gradient(180deg,#fcfcfb_0%,#ffffff_100%)] p-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Siklus</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSubscription.billing_cycle_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Biaya</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSubscription.amount_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Perpanjangan Berikutnya</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSubscription.next_renewal_date_label || 'Belum ada tanggal' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-stone-400">Pengingat</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ selectedSubscription.reminder_days_before }} hari sebelumnya</p>
                  </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                  <div class="rounded-[1.3rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Monthly Equivalent</p>
                    <p class="mt-2 text-xl font-semibold text-stone-950">{{ selectedSubscription.monthly_equivalent_label }}</p>
                  </div>
                  <div class="rounded-[1.3rem] border border-stone-200 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Annualized</p>
                    <p class="mt-2 text-xl font-semibold text-stone-950">{{ selectedSubscription.annualized_amount_label }}</p>
                  </div>
                </div>
              </article>

              <div class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Vendor</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <p class="text-base font-semibold text-stone-950">{{ selectedSubscription.vendor?.name || 'No vendor linked' }}</p>
                      <div class="mt-3 space-y-2 text-sm text-stone-600">
                        <p>Contact: {{ selectedSubscription.vendor?.contact_name || '-' }}</p>
                        <p>Email: {{ selectedSubscription.vendor?.email || '-' }}</p>
                        <p>Phone: {{ selectedSubscription.vendor?.phone || '-' }}</p>
                      </div>
                      <div class="mt-4 rounded-[1rem] border border-white bg-white p-3 text-sm text-stone-600">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Contract</p>
                        <p class="mt-2 whitespace-pre-wrap">{{ selectedSubscription.vendor?.contract || 'No contract note yet.' }}</p>
                      </div>
                      <div class="mt-4 rounded-[1rem] border border-white bg-white p-3 text-sm text-stone-600">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Payment Terms</p>
                        <p class="mt-2 whitespace-pre-wrap">
                        {{ selectedSubscription.vendor?.payment_terms || 'No payment terms yet.' }}
                        </p>
                      </div>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">
                      {{ selectedSubscription.description || 'Belum ada deskripsi untuk subscription ini.' }}
                    </div>
                  </article>
                </section>

                <section class="space-y-6">
                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengeluaran Terkait</p>
                    <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                      <template v-if="selectedSubscription.transaction">
                        <p class="text-sm font-semibold text-stone-950">{{ selectedSubscription.transaction.category_label }}</p>
                        <p class="mt-2 text-sm text-stone-600">{{ selectedSubscription.transaction.amount_label }} | {{ selectedSubscription.transaction.date_label }}</p>
                        <p class="mt-2 text-xs text-stone-500">{{ selectedSubscription.transaction.account?.name || 'No account' }}</p>
                      </template>
                      <p v-else class="text-sm text-stone-500">Subscription ini belum dikaitkan ke expense transaction.</p>
                    </div>
                  </article>

                  <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Renewal Reading</p>
                    <div class="mt-4 space-y-3">
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-950">Renewal state</p>
                        <p class="mt-2 text-sm text-stone-600">{{ renewalNarrative(selectedSubscription) }}</p>
                      </div>
                      <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-semibold text-stone-950">Status</p>
                        <p class="mt-2 text-sm text-stone-600">{{ statusNarrative(selectedSubscription) }}</p>
                      </div>
                    </div>
                  </article>

                  <div class="flex flex-wrap gap-3">
                    <button type="button" @click="openSubscriptionModal(selectedSubscription)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                      <span>Ubah Langganan</span>
                    </button>
                    <button type="button" @click="deleteSubscription(selectedSubscription.id)" class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
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
              <p class="text-lg font-semibold text-stone-950">Belum ada subscription terpilih.</p>
              <p class="mt-2 text-sm leading-6 text-stone-500">Pilih item dari daftar sebelah kiri atau buat subscription baru untuk mulai isi menu 18.</p>
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Vendor Management</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Kontak vendor, payment terms, dan supplier tools tim.</h2>
          </div>
          <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
            <span class="font-semibold text-stone-950">{{ vendorSummary.total_vendors }}</span> vendors
          </div>
        </div>

        <div class="mt-5 grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
          <article v-for="vendor in vendorItems" :key="vendor.id" class="rounded-[1.5rem] border border-stone-200 bg-stone-50 p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-base font-semibold text-stone-950">{{ vendor.name }}</p>
                <p class="mt-1 text-sm text-stone-500">{{ vendor.contact_name || 'No contact yet' }}</p>
              </div>
              <div class="flex gap-2">
                <button type="button" @click="openVendorModal(vendor)" class="rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                  <Pencil class="h-3.5 w-3.5" />
                </button>
                <button type="button" @click="deleteVendor(vendor.id)" class="rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>

            <div class="mt-4 space-y-2 text-sm text-stone-600">
              <p>Email: {{ vendor.email || '-' }}</p>
              <p>Phone: {{ vendor.phone || '-' }}</p>
              <p>Subscriptions: {{ vendor.subscriptions_count }}</p>
              <p>Setara bulanan: {{ vendor.monthly_equivalent_label }}</p>
            </div>

            <div class="mt-4 rounded-[1.1rem] border border-white bg-white p-3 text-sm leading-6 text-stone-600">
              <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Contract</p>
              <p class="mt-2 whitespace-pre-wrap">{{ vendor.contract || 'No contract note.' }}</p>
            </div>

            <div class="mt-4 rounded-[1.1rem] border border-white bg-white p-3 text-sm leading-6 text-stone-600">
              <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Payment Terms</p>
              <p class="mt-2 whitespace-pre-wrap">{{ vendor.payment_terms || 'No payment terms.' }}</p>
            </div>
          </article>

          <div v-if="vendorItems.length === 0" class="rounded-[1.5rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-14 text-center text-sm text-stone-500 xl:col-span-2 2xl:col-span-3">
            Belum ada vendor yang tercatat.
          </div>
        </div>
      </section>
    </div>
    <Transition name="modal">
      <div v-if="showSubscriptionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Langganan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingSubscription ? 'Ubah Langganan' : 'Buat Langganan' }}</h3>
            </div>
            <button type="button" @click="closeSubscriptionModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitSubscription">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Subscription Name</span>
                <input v-model="subscriptionForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="subscriptionForm.errors.name" class="text-xs text-rose-500">{{ subscriptionForm.errors.name }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Vendor</span>
                <select v-model="subscriptionForm.vendor_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No vendor</option>
                  <option v-for="vendor in filterOptions.vendors" :key="vendor.id" :value="vendor.id">{{ vendor.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengeluaran Terkait</span>
                <select v-model="subscriptionForm.transaction_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">No expense link</option>
                  <option v-for="transaction in filterOptions.expenseTransactions" :key="transaction.id" :value="transaction.id">{{ transaction.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span>
                <input v-model.number="subscriptionForm.amount" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="subscriptionForm.errors.amount" class="text-xs text-rose-500">{{ subscriptionForm.errors.amount }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Siklus Penagihan</span>
                <select v-model="subscriptionForm.billing_cycle" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="cycle in filterOptions.billingCycles" :key="cycle.value" :value="cycle.value">{{ cycle.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
                <select v-model="subscriptionForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Next Renewal</span>
                <input v-model="subscriptionForm.next_renewal_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Reminder Days</span>
                <input v-model.number="subscriptionForm.reminder_days_before" type="number" min="0" step="1" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.92fr]">
              <section class="space-y-4">
                <label class="space-y-2 text-sm">
                  <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
                  <textarea v-model="subscriptionForm.description" rows="6" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                </label>
              </section>

              <section class="space-y-4">
                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-950 p-5 text-white">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-300">Ringkasan Langsung</p>
                  <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Jumlah</span>
                      <span class="font-semibold text-white">{{ formatCurrency(subscriptionForm.amount || 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Cycle</span>
                      <span class="font-semibold text-white">{{ subscriptionForm.billing_cycle === 'yearly' ? 'Yearly' : 'Monthly' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-stone-300">Status</span>
                      <span class="font-semibold text-white">{{ capitalize(subscriptionForm.status) }}</span>
                    </div>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Context</p>
                  <div class="mt-4 space-y-3 text-sm leading-6 text-stone-600">
                    <p>Gunakan link expense saat biaya subscription sudah tercatat di transaksi agar jejaknya rapi.</p>
                    <p>Atur reminder beberapa hari sebelum renewal supaya tidak telat perpanjang atau menghentikan tool.</p>
                  </div>
                </article>
              </section>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeSubscriptionModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="subscriptionForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                <Sparkles class="h-4 w-4" />
                <span>{{ isEditingSubscription ? 'Simpan Langganan' : 'Buat Langganan' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showVendorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="w-full max-w-3xl rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Vendor</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingVendor ? 'Ubah Vendor' : 'Buat Vendor' }}</h3>
            </div>
            <button type="button" @click="closeVendorModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitVendor">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Vendor Name</span>
                <input v-model="vendorForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="vendorForm.errors.name" class="text-xs text-rose-500">{{ vendorForm.errors.name }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Contact Name</span>
                <input v-model="vendorForm.contact_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</span>
                <input v-model="vendorForm.email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Phone</span>
                <input v-model="vendorForm.phone" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
              </label>
            </div>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Contract</span>
              <textarea v-model="vendorForm.contract" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Payment Terms</span>
              <textarea v-model="vendorForm.payment_terms" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Catatan</span>
              <textarea v-model="vendorForm.notes" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
            </label>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeVendorModal" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="vendorForm.processing" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                <Building2 class="h-4 w-4" />
                <span>{{ isEditingVendor ? 'Simpan Vendor' : 'Buat Vendor' }}</span>
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
  Ban,
  Building2,
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
  subscriptions: { type: Object, required: true },
  vendors: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const subscriptionsBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/subscriptions`

const localSubscriptions = ref(cloneSubscriptions(props.subscriptions.items || []))
const localVendors = ref(cloneVendors(props.vendors.items || []))
const filterState = ref(buildFilterState(props.filters))
const selectedSubscriptionId = ref(props.subscriptions.selected_id || props.subscriptions.items?.[0]?.id || null)
const showSubscriptionModal = ref(false)
const showVendorModal = ref(false)
const editingSubscriptionId = ref(null)
const editingVendorId = ref(null)

const subscriptionForm = useForm({
  vendor_id: '',
  transaction_id: '',
  name: '',
  description: '',
  amount: 0,
  billing_cycle: 'monthly',
  status: 'active',
  next_renewal_date: '',
  reminder_days_before: 7,
})

const vendorForm = useForm({
  name: '',
  contact_name: '',
  email: '',
  phone: '',
  contract: '',
  payment_terms: '',
  notes: '',
})

const subscriptionSummary = computed(() => props.subscriptions.summary)
const vendorSummary = computed(() => props.vendors.summary)
const subscriptionItems = computed(() => localSubscriptions.value)
const vendorItems = computed(() => localVendors.value)
const selectedSubscription = computed(() => subscriptionItems.value.find((subscription) => subscription.id === selectedSubscriptionId.value) || subscriptionItems.value[0] || null)
const isEditingSubscription = computed(() => Boolean(editingSubscriptionId.value))
const isEditingVendor = computed(() => Boolean(editingVendorId.value))

watch(
  () => props.subscriptions.items,
  (items) => {
    localSubscriptions.value = cloneSubscriptions(items || [])

    if (!selectedSubscriptionId.value || !localSubscriptions.value.some((subscription) => subscription.id === selectedSubscriptionId.value)) {
      selectedSubscriptionId.value = localSubscriptions.value[0]?.id || null
    }
  },
)

watch(
  () => props.vendors.items,
  (items) => {
    localVendors.value = cloneVendors(items || [])
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
    vendor: filters.vendor ?? '',
    status: filters.status ?? '',
    billing_cycle: filters.billing_cycle ?? '',
    renewal_state: filters.renewal_state ?? '',
  }
}

function applyFilters() {
  router.get(subscriptionsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(subscriptionsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function selectSubscription(subscriptionId) {
  selectedSubscriptionId.value = subscriptionId
}

function openSubscriptionModal(subscription = null) {
  editingSubscriptionId.value = subscription?.id || null
  subscriptionForm.reset()
  subscriptionForm.clearErrors()
  subscriptionForm.vendor_id = subscription?.vendor_id || ''
  subscriptionForm.transaction_id = subscription?.transaction_id || ''
  subscriptionForm.name = subscription?.name || ''
  subscriptionForm.description = subscription?.description || ''
  subscriptionForm.amount = subscription?.amount ?? 0
  subscriptionForm.billing_cycle = subscription?.billing_cycle || 'monthly'
  subscriptionForm.status = subscription?.status || 'active'
  subscriptionForm.next_renewal_date = subscription?.next_renewal_date || ''
  subscriptionForm.reminder_days_before = subscription?.reminder_days_before ?? 7
  showSubscriptionModal.value = true
}

function closeSubscriptionModal() {
  showSubscriptionModal.value = false
  editingSubscriptionId.value = null
  subscriptionForm.reset()
  subscriptionForm.clearErrors()
  subscriptionForm.billing_cycle = 'monthly'
  subscriptionForm.status = 'active'
  subscriptionForm.amount = 0
  subscriptionForm.reminder_days_before = 7
}

function submitSubscription() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeSubscriptionModal(),
  }

  if (isEditingSubscription.value) {
    subscriptionForm.patch(`${subscriptionsBaseUrl}/${encodeURIComponent(editingSubscriptionId.value)}`, options)
    return
  }

  subscriptionForm.post(subscriptionsBaseUrl, options)
}

function deleteSubscription(subscriptionId) {
  if (!confirm('Hapus langganan ini?')) {
    return
  }

  router.delete(`${subscriptionsBaseUrl}/${encodeURIComponent(subscriptionId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedSubscriptionId.value === subscriptionId) {
        selectedSubscriptionId.value = subscriptionItems.value.find((subscription) => subscription.id !== subscriptionId)?.id || null
      }
    },
  })
}

function markStatus(subscriptionId, status) {
  router.patch(`${subscriptionsBaseUrl}/${encodeURIComponent(subscriptionId)}/status`, {
    status,
  }, {
    preserveScroll: true,
  })
}

function openVendorModal(vendor = null) {
  editingVendorId.value = vendor?.id || null
  vendorForm.reset()
  vendorForm.clearErrors()
  vendorForm.name = vendor?.name || ''
  vendorForm.contact_name = vendor?.contact_name || ''
  vendorForm.email = vendor?.email || ''
  vendorForm.phone = vendor?.phone || ''
  vendorForm.contract = vendor?.contract || ''
  vendorForm.payment_terms = vendor?.payment_terms || ''
  vendorForm.notes = vendor?.notes || ''
  showVendorModal.value = true
}

function closeVendorModal() {
  showVendorModal.value = false
  editingVendorId.value = null
  vendorForm.reset()
  vendorForm.clearErrors()
}

function submitVendor() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeVendorModal(),
  }

  if (isEditingVendor.value) {
    vendorForm.patch(`${subscriptionsBaseUrl}/vendors/${encodeURIComponent(editingVendorId.value)}`, options)
    return
  }

  vendorForm.post(`${subscriptionsBaseUrl}/vendors`, options)
}

function deleteVendor(vendorId) {
  if (!confirm('Hapus vendor ini?')) {
    return
  }

  router.delete(`${subscriptionsBaseUrl}/vendors/${encodeURIComponent(vendorId)}`, {
    preserveScroll: true,
  })
}

function subscriptionStatusClass(status) {
  const map = {
    active: 'bg-emerald-100 text-emerald-700',
    expired: 'bg-amber-100 text-amber-700',
    cancelled: 'bg-rose-100 text-rose-700',
  }

  return map[status] || 'bg-stone-100 text-stone-700'
}

function renewalStateClass(state) {
  const map = {
    due_soon: 'bg-sky-100 text-sky-700',
    overdue: 'bg-rose-100 text-rose-700',
    scheduled: 'bg-stone-100 text-stone-700',
    no_date: 'bg-stone-100 text-stone-500',
    cancelled: 'bg-rose-100 text-rose-700',
  }

  return map[state] || 'bg-stone-100 text-stone-700'
}

function renewalNarrative(subscription) {
  if (subscription.renewal_state === 'overdue') {
    return 'Renewal date sudah lewat. Subscription ini perlu dievaluasi atau diperpanjang segera.'
  }

  if (subscription.renewal_state === 'due_soon') {
    return `Renewal date masuk jendela reminder ${subscription.reminder_days_before} hari. Cocok untuk follow-up sekarang.`
  }

  if (subscription.renewal_state === 'no_date') {
    return 'Tanggal renewal belum diatur, jadi pengingat belum bisa dipantau dengan baik.'
  }

  if (subscription.renewal_state === 'cancelled') {
    return 'Subscription sudah ditandai cancelled dan tidak lagi dipantau sebagai langganan aktif.'
  }

  return 'Subscription masih on track dan belum masuk jendela reminder.'
}

function statusNarrative(subscription) {
  if (subscription.effective_status === 'expired') {
    return 'Status efektif terbaca expired, baik karena memang ditandai expired atau renewal date sudah lewat.'
  }

  if (subscription.effective_status === 'cancelled') {
    return 'Status subscription dihentikan dan bisa dibersihkan dari penggunaan tim bila perlu.'
  }

  return 'Subscription aktif dan masih masuk daftar tools berjalan.'
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value || 0))
}

function capitalize(value) {
  return value ? value.charAt(0).toUpperCase() + value.slice(1) : ''
}

function compactFilters(filters) {
  return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== null && value !== undefined))
}

function cloneSubscriptions(items) {
  return items.map((subscription) => ({
    ...subscription,
    vendor: subscription.vendor ? { ...subscription.vendor } : null,
    transaction: subscription.transaction ? { ...subscription.transaction, account: subscription.transaction.account ? { ...subscription.transaction.account } : null } : null,
  }))
}

function cloneVendors(items) {
  return items.map((vendor) => ({ ...vendor }))
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
