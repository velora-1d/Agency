<template>
  <WorkspaceLayout
    title="Penawaran"
    subtitle="Proposal terperinci yang mudah dibaca: surat pengantar, ruang lingkup, linimasa, syarat, tautan persetujuan klien, lalu ubah ke invoice dan proyek setelah disetujui."
  >
    <template #actions>
      <div class="flex flex-wrap items-center gap-3">
        <button
          type="button"
          @click="openQuotationModal()"
          class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
        >
          <Plus class="h-4 w-4" />
          <span>Proposal Baru</span>
        </button>
      </div>
    </template>

    <FinanceLayout :workspace="workspace">
      <div class="space-y-6">
      <section class="rounded-[1.6rem] border border-stone-200 bg-white p-4 shadow-[0_18px_50px_rgba(28,25,23,0.05)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="max-w-3xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-stone-400">Menu 15 / Penawaran / Proposal</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-[-0.05em] text-stone-950">Prospek masuk, proposal dikirim, klien menyetujui, lalu lanjut ke invoice dan proyek.</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">
              Proposal tetap itemized dan jelas untuk client, tapi ringkasan atasnya dibuat lebih rapat supaya daftar proposal lebih cepat kebaca.
            </p>
          </div>

          <div class="grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Nilai Tertunda</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ quotationSummary.pending_value_label }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ quotationSummary.total_quotations }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Terkirim</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ quotationSummary.sent_quotations }}</p>
            </div>
            <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Disetujui</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ quotationSummary.approved_quotations }}</p>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Draft</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ quotationSummary.draft_quotations }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Approved Value</p>
            <p class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ quotationSummary.approved_value_label }}</p>
          </div>
          <div class="rounded-[1rem] border border-stone-200 bg-stone-50 p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Flow</p>
            <p class="mt-2 text-sm leading-6 text-stone-600">Quotation approved bisa langsung di-convert jadi invoice draft dan project planning.</p>
          </div>
        </div>
      </section>

      <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
        <div class="filter-panel-head mb-5">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cari proposal berdasarkan nomor, judul, klien, prospek, atau status persetujuan.</h2>
          </div>
          <div class="filter-meta-badge">
            <span class="font-semibold text-stone-950">{{ quotationItems.length }}</span> proposal tampil
          </div>
        </div>

        <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-5">
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
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Prospek</span>
            <select v-model="filterState.lead" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Prospek</option>
              <option v-for="lead in filterOptions.leads" :key="lead.id" :value="lead.id">{{ lead.name }}</option>
            </select>
          </label>

          <label class="space-y-2 text-sm">
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
            <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
              <option value="">Semua Status</option>
              <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
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
        <aside class="space-y-4">
          <section class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Blok Proposal</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Biaya yang umum dipakai</h2>
              </div>
            </div>

            <div class="mt-5 space-y-3">
              <button
                v-for="category in filterOptions.itemCategories"
                :key="category.value"
                type="button"
                @click="appendPresetItem(category)"
                class="flex w-full items-center justify-between rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3 text-left transition hover:border-stone-300 hover:bg-white"
              >
                <div>
                  <p class="text-sm font-semibold text-stone-950">{{ category.label }}</p>
                  <p class="mt-1 text-xs text-stone-500">Tambah cepat ke form proposal</p>
                </div>
                <Plus class="h-4 w-4 text-stone-500" />
              </button>
              <div v-if="filterOptions.itemCategories.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 bg-stone-50 px-4 py-8 text-center text-sm text-stone-500">
                Belum ada preset biaya.
              </div>
            </div>
          </section>

          <section class="rounded-[2rem] border border-stone-200 bg-stone-950 p-5 text-white shadow-[0_20px_60px_rgba(28,25,23,0.14)]">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Peta Alur</p>
            <div class="mt-4 space-y-3">
              <article class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">1. Draf proposal</p>
                <p class="mt-2 text-sm text-stone-300">Tulis pengantar, ruang lingkup, linimasa, dan rincian biaya terperinci.</p>
              </article>
              <article class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">2. Kirim ke klien</p>
                <p class="mt-2 text-sm text-stone-300">Sistem membuat tautan persetujuan publik untuk ditinjau klien.</p>
              </article>
              <article class="rounded-[1.2rem] border border-white/10 bg-white/5 p-4">
                <p class="text-sm font-semibold text-white">3. Ubah setelah disetujui</p>
                <p class="mt-2 text-sm text-stone-300">Proposal yang disetujui bisa langsung diubah menjadi invoice dan proyek.</p>
              </article>
            </div>
          </section>
        </aside>

        <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
          <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pustaka Proposal</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Daftar penawaran workspace dengan status persetujuan dan konversi.</h2>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">
              {{ quotationItems.length }} proposal
            </span>
          </div>

          <div class="mt-5 space-y-4">
            <article v-for="quotation in quotationItems" :key="quotation.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-2xl">
                  <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openQuotationDetail(quotation.id)" class="text-left text-base font-semibold text-stone-950 transition hover:text-amber-700">
                      {{ quotation.title }}
                    </button>
                    <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">
                      {{ quotation.number }}
                    </span>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="quotationStatusClass(quotation.status)">
                      {{ quotation.status_label }}
                    </span>
                  </div>
                  <p class="mt-2 text-sm text-stone-500">{{ quotation.client?.name || quotation.lead?.company || 'Proposal umum' }}</p>
                  <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                    <span class="rounded-full bg-white px-3 py-1.5">{{ quotation.counts.items }} item</span>
                    <span class="rounded-full bg-white px-3 py-1.5">v{{ quotation.version }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ quotation.valid_until_label || 'Tanpa kedaluwarsa' }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5">{{ quotation.total_label }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" @click="openQuotationModal(quotation)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Pencil class="h-4 w-4" />
                  </button>
                  <button type="button" @click="sendQuotation(quotation.id)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <Send class="h-4 w-4" />
                  </button>
                  <button type="button" @click="openQuotationDetail(quotation.id)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                    <FileText class="h-4 w-4" />
                  </button>
                  <button type="button" @click="deleteQuotation(quotation.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-[1.2rem] border border-white bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Subtotal</p>
                  <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.subtotal_label }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-white bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">DP</p>
                  <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.dp_amount_label }}</p>
                </div>
                <div class="rounded-[1.2rem] border border-white bg-white p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Remaining</p>
                  <p class="mt-2 text-lg font-semibold text-stone-950">{{ quotation.remaining_amount_label }}</p>
                </div>
              </div>
            </article>

            <div v-if="quotationItems.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 px-5 py-16 text-center text-sm text-stone-500">
              Belum ada quotation yang cocok dengan filter saat ini.
            </div>
          </div>
        </article>
      </section>
    </div>

    <Transition name="modal">
      <div v-if="showQuotationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Form Proposal</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ isEditingQuotation ? 'Ubah Proposal' : 'Buat Proposal' }}</h3>
            </div>
            <button type="button" @click="closeQuotationModal" class="rounded-full p-2 text-stone-400 transition-all hover:bg-stone-100 hover:text-stone-700">
              <X class="h-5 w-5" />
            </button>
          </div>

          <form class="mt-6 space-y-6" @submit.prevent="submitQuotation">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Judul Proposal</span>
                <input v-model="quotationForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                <p v-if="quotationForm.errors.title" class="text-xs text-rose-500">{{ quotationForm.errors.title }}</p>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
                <select v-model="quotationForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa klien</option>
                  <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </select>
              </label>

              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Prospek</span>
                <select v-model="quotationForm.lead_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                  <option value="">Tanpa prospek</option>
                  <option v-for="lead in filterOptions.leads" :key="lead.id" :value="lead.id">{{ lead.name }}</option>
                </select>
              </label>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Surat Pengantar / Pengantar</span>
                <textarea v-model="quotationForm.cover_letter" rows="7" class="w-full rounded-[1.4rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Ruang Lingkup Pekerjaan</span>
                <textarea v-model="quotationForm.scope_of_work" rows="7" class="w-full rounded-[1.4rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
              <label class="space-y-2 text-sm xl:col-span-2">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Linimasa Proyek</span>
                <textarea v-model="quotationForm.timeline" rows="7" class="w-full rounded-[1.4rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>
            </div>

            <section class="rounded-[1.8rem] border border-stone-200 bg-stone-50 p-5">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Biaya Terperinci</p>
                  <p class="mt-1 text-sm text-stone-500">Semua biaya proposal dipecah per item agar klien mudah memverifikasi detail pekerjaan.</p>
                </div>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                  <Plus class="h-4 w-4" />
                  <span>Tambah Item</span>
                </button>
              </div>

              <div class="mt-5 space-y-4">
                <article v-for="(item, index) in quotationForm.items" :key="index" class="rounded-[1.4rem] border border-stone-200 bg-white p-4">
                  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                    <label class="space-y-2 text-sm xl:col-span-2">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Item</span>
                      <input v-model="item.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </label>

                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Kategori</span>
                      <select v-model="item.category" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                        <option value="">Tanpa kategori</option>
                        <option v-for="category in filterOptions.itemCategories" :key="category.value" :value="category.value">{{ category.label }}</option>
                      </select>
                    </label>

                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jumlah</span>
                      <input v-model.number="item.quantity" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </label>

                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Satuan</span>
                      <input v-model="item.unit" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </label>

                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Harga Satuan</span>
                      <input v-model.number="item.unit_price" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </label>

                    <label class="space-y-2 text-sm md:col-span-2 xl:col-span-4">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Deskripsi</span>
                      <textarea v-model="item.description" rows="3" class="w-full rounded-[1.2rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
                    </label>

                    <label class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Diskon</span>
                      <input v-model.number="item.discount_amount" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                    </label>

                    <div class="space-y-2 text-sm">
                      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Subtotal</span>
                      <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-semibold text-stone-950">
                        {{ formatCurrency(itemSubtotal(item)) }}
                      </div>
                    </div>
                  </div>

                  <div class="mt-4 flex justify-end">
                    <button type="button" @click="removeItem(index)" class="inline-flex items-center gap-2 rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-3.5 w-3.5" />
                      <span>Remove Item</span>
                    </button>
                  </div>
                </article>
              </div>
            </section>

            <div class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
              <label class="space-y-2 text-sm">
                <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Terms & Conditions</span>
                <textarea v-model="quotationForm.terms_conditions" rows="10" class="w-full rounded-[1.4rem] border border-stone-200 bg-stone-50 px-4 py-3 text-sm leading-6 text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"></textarea>
              </label>

              <section class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="grid gap-4 md:grid-cols-2">
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Global Discount</span>
                    <input v-model.number="quotationForm.discount_amount" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tax Rate</span>
                    <input v-model.number="quotationForm.tax_rate" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">DP %</span>
                    <input v-model.number="quotationForm.dp_percentage" type="number" min="0" step="0.01" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  </label>
                  <label class="space-y-2 text-sm">
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Valid Until</span>
                    <input v-model="quotationForm.valid_until" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white" />
                  </label>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Subtotal</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ formatCurrency(formSummary.subtotal) }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Tax</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ formatCurrency(formSummary.taxAmount) }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Total</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ formatCurrency(formSummary.total) }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">DP / Remaining</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ formatCurrency(formSummary.dpAmount) }}</p>
                    <p class="mt-1 text-xs text-stone-500">Sisa {{ formatCurrency(formSummary.remaining) }}</p>
                  </div>
                </div>

                <div class="mt-5 rounded-[1.2rem] border border-dashed border-stone-300 bg-stone-50 px-4 py-4 text-sm leading-6 text-stone-600">
                  Signature section bersifat digital/manual saat client menerima proposal. Setelah dikirim, link approval publik akan aktif.
                </div>
              </section>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
              <button type="button" @click="closeQuotationModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                Batal
              </button>
              <button type="submit" :disabled="quotationForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">
                {{ isEditingQuotation ? 'Simpan Proposal' : 'Buat Proposal' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showDetailModal && selectedQuotation" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-6xl overflow-y-auto rounded-[2rem] border border-white/20 bg-white p-6 shadow-2xl">
          <div class="relative overflow-hidden rounded-[1.8rem] bg-stone-950 p-6 text-white">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.2),transparent_24%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.18),transparent_35%)]"></div>
            <div class="relative">
              <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-3xl">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-amber-200/70">Proposal Detail</p>
                  <h3 class="mt-3 text-3xl font-semibold tracking-[-0.05em] text-white">{{ selectedQuotation.title }}</h3>
                  <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">{{ selectedQuotation.number }}</span>
                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="quotationStatusClass(selectedQuotation.status)">{{ selectedQuotation.status_label }}</span>
                    <span class="rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white">v{{ selectedQuotation.version }}</span>
                  </div>
                  <div class="mt-4 flex flex-wrap gap-3 text-sm text-stone-300">
                    <span>{{ selectedQuotation.client?.name || selectedQuotation.lead?.company || 'General proposal' }}</span>
                    <span>{{ selectedQuotation.valid_until_label || 'Tanpa kedaluwarsa' }}</span>
                    <span>{{ selectedQuotation.total_label }}</span>
                  </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <button type="button" @click="openQuotationModal(selectedQuotation)" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                    <Pencil class="h-4 w-4" />
                    <span>Ubah</span>
                  </button>
                  <button type="button" @click="sendQuotation(selectedQuotation.id)" :disabled="selectedQuotation.status === 'approved'" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/15 disabled:cursor-not-allowed disabled:opacity-50">
                    <Send class="h-4 w-4" />
                    <span>Send</span>
                  </button>
                  <button type="button" @click="closeDetailModal" class="rounded-full border border-white/15 bg-white/10 p-2 text-stone-200 transition hover:bg-white/15 hover:text-white">
                    <X class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_0.9fr]">
            <section class="space-y-6">
              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Proposal Narrative</p>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-stone-400">Surat Pengantar</p>
                    <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ selectedQuotation.cover_letter || 'Belum diisi.' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-stone-400">Ruang Lingkup Pekerjaan</p>
                    <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ selectedQuotation.scope_of_work || 'Belum diisi.' }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-stone-400">Timeline</p>
                    <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-stone-700">{{ selectedQuotation.timeline || 'Belum diisi.' }}</p>
                  </div>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Itemized Costs</p>
                  <span class="rounded-full bg-stone-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ selectedQuotation.counts.items }} items</span>
                </div>
                <div class="mt-4 space-y-3">
                  <article v-for="item in selectedQuotation.items" :key="item.id || item.name" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <div class="flex flex-wrap items-center gap-2">
                          <p class="text-sm font-semibold text-stone-950">{{ item.name }}</p>
                          <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ item.category || 'custom' }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ item.description || 'No description' }}</p>
                        <p class="mt-2 text-xs text-stone-500">{{ item.quantity }} {{ item.unit || 'unit' }} x {{ item.unit_price_label }}</p>
                      </div>
                      <div class="text-right">
                        <p class="text-sm font-semibold text-stone-950">{{ item.subtotal_label }}</p>
                        <p class="mt-2 text-xs text-stone-500">Discount {{ item.discount_amount_label }}</p>
                      </div>
                    </div>
                  </article>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Terms & Conditions</p>
                <div class="mt-4 rounded-[1.3rem] border border-stone-200 bg-stone-50 p-4 whitespace-pre-wrap text-sm leading-6 text-stone-700">
                  {{ selectedQuotation.terms_conditions || 'Belum ada terms & conditions.' }}
                </div>
              </article>
            </section>

            <section class="space-y-6">
              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Financial Summary</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Subtotal</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedQuotation.subtotal_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Discount</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedQuotation.discount_amount_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400">PPN</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedQuotation.tax_amount_label }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ selectedQuotation.tax_rate_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Total</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedQuotation.total_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400">DP</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedQuotation.dp_amount_label }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ selectedQuotation.dp_percentage_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.16em] text-stone-400">Remaining</p>
                    <p class="mt-2 text-lg font-semibold text-stone-950">{{ selectedQuotation.remaining_amount_label }}</p>
                  </div>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Persetujuan Klien</p>
                    <p class="mt-1 text-sm text-stone-500">Kirim link approval publik untuk client review dan decision.</p>
                  </div>
                  <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em]" :class="quotationStatusClass(selectedQuotation.status)">
                    {{ selectedQuotation.status_label }}
                  </span>
                </div>
                <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="break-all text-sm font-medium text-stone-950">{{ selectedQuotation.approval_url || 'Approval link akan aktif setelah quotation dikirim.' }}</p>
                  <p class="mt-2 text-xs text-stone-500">Terkirim {{ selectedQuotation.sent_at_label || '-' }} / Disetujui {{ selectedQuotation.approved_at_label || '-' }}</p>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                  <button type="button" @click="copyApprovalLink(selectedQuotation)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950">
                    <Copy class="h-4 w-4" />
                    <span>Copy Link</span>
                  </button>
                  <button type="button" @click="sendQuotation(selectedQuotation.id)" class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-800">
                    <Send class="h-4 w-4" />
                    <span>Kirim ke Klien</span>
                  </button>
                </div>
              </article>

              <article class="rounded-[1.6rem] border border-stone-200 bg-white p-5">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Next Step</p>
                    <p class="mt-1 text-sm text-stone-500">Setelah approved, proposal bisa turun jadi invoice dan project.</p>
                  </div>
                  <span class="rounded-full bg-stone-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-500">{{ selectedQuotation.invoice ? 'Converted' : 'Ready' }}</span>
                </div>
                <div class="mt-4 rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
                  <p>Invoice: {{ selectedQuotation.invoice?.number || 'Belum dibuat' }}</p>
                  <p class="mt-1">Proyek: {{ selectedQuotation.invoice?.project?.name || 'Belum dibuat' }}</p>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                  <button type="button" @click="approveLocally(selectedQuotation.id)" :disabled="selectedQuotation.status === 'approved'" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">
                    <BadgeCheck class="h-4 w-4" />
                    <span>Tandai Disetujui</span>
                  </button>
                  <button type="button" @click="convertQuotation(selectedQuotation.id)" :disabled="selectedQuotation.status !== 'approved' || Boolean(selectedQuotation.invoice)" class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:border-stone-300 hover:text-stone-950 disabled:cursor-not-allowed disabled:opacity-50">
                    <ArrowRightLeft class="h-4 w-4" />
                    <span>{{ selectedQuotation.invoice ? 'Sudah Dikonversi' : 'Ubah ke Invoice + Proyek' }}</span>
                  </button>
                </div>
              </article>
            </section>
          </div>
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
  ArrowRightLeft,
  BadgeCheck,
  Copy,
  FileText,
  Filter,
  Pencil,
  Plus,
  RotateCcw,
  Send,
  Trash2,
  X,
} from 'lucide-vue-next'
import WorkspaceLayout from '../../../Layouts/WorkspaceLayout.vue'
import FinanceLayout from '../../../Layouts/FinanceLayout.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
  quotations: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  filterOptions: { type: Object, required: true },
})

const quotationsBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/finance/quotations`

const localQuotations = ref(cloneQuotations(props.quotations.items || []))
const filterState = ref(buildFilterState(props.filters))
const showQuotationModal = ref(false)
const showDetailModal = ref(false)
const editingQuotationId = ref(null)
const selectedQuotationId = ref(props.quotations.selected_id || null)

const quotationForm = useForm({
  client_id: '',
  lead_id: '',
  title: '',
  cover_letter: '',
  scope_of_work: '',
  timeline: '',
  terms_conditions: '',
  discount_amount: 0,
  tax_rate: 11,
  dp_percentage: 30,
  valid_until: todayDate(14),
  items: [blankItem()],
})

const quotationSummary = computed(() => props.quotations.summary)
const quotationItems = computed(() => localQuotations.value)
const selectedQuotation = computed(() => quotationItems.value.find((quotation) => quotation.id === selectedQuotationId.value) || null)
const isEditingQuotation = computed(() => Boolean(editingQuotationId.value))
const formSummary = computed(() => {
  const subtotal = quotationForm.items.reduce((total, item) => total + itemSubtotal(item), 0)
  const discount = Number(quotationForm.discount_amount || 0)
  const taxable = Math.max(0, subtotal - discount)
  const taxAmount = taxable * (Number(quotationForm.tax_rate || 0) / 100)
  const total = taxable + taxAmount
  const dpAmount = total * (Number(quotationForm.dp_percentage || 0) / 100)

  return {
    subtotal,
    taxAmount,
    total,
    dpAmount,
    remaining: Math.max(0, total - dpAmount),
  }
})

watch(
  () => props.quotations.items,
  (items) => {
    localQuotations.value = cloneQuotations(items || [])
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
  () => props.quotations.selected_id,
  (quotationId) => {
    if (!quotationId) {
      return
    }

    selectedQuotationId.value = quotationId
    showDetailModal.value = true
  },
  { immediate: true },
)

function buildFilterState(filters = {}) {
  return {
    search: filters.search ?? '',
    client: filters.client ?? '',
    lead: filters.lead ?? '',
    status: filters.status ?? '',
  }
}

function applyFilters() {
  router.get(quotationsBaseUrl, compactFilters(filterState.value), {
    preserveState: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  filterState.value = buildFilterState()

  router.get(quotationsBaseUrl, {}, {
    preserveState: true,
    preserveScroll: true,
  })
}

function openQuotationModal(quotation = null) {
  editingQuotationId.value = quotation?.id || null
  quotationForm.reset()
  quotationForm.clearErrors()
  quotationForm.client_id = quotation?.client_id || ''
  quotationForm.lead_id = quotation?.lead_id || ''
  quotationForm.title = quotation?.title || ''
  quotationForm.cover_letter = quotation?.cover_letter || ''
  quotationForm.scope_of_work = quotation?.scope_of_work || ''
  quotationForm.timeline = quotation?.timeline || ''
  quotationForm.terms_conditions = quotation?.terms_conditions || ''
  quotationForm.discount_amount = quotation?.discount_amount ?? 0
  quotationForm.tax_rate = quotation?.tax_rate ?? 11
  quotationForm.dp_percentage = quotation?.dp_percentage ?? 30
  quotationForm.valid_until = quotation?.valid_until || todayDate(14)
  quotationForm.items = quotation?.items?.length
    ? quotation.items.map((item) => ({
        name: item.name,
        description: item.description || '',
        category: item.category || '',
        quantity: Number(item.quantity || 1),
        unit: item.unit || '',
        unit_price: Number(item.unit_price || 0),
        discount_amount: Number(item.discount_amount || 0),
      }))
    : [blankItem()]
  showQuotationModal.value = true
  showDetailModal.value = false
}

function closeQuotationModal() {
  showQuotationModal.value = false
  editingQuotationId.value = null
  quotationForm.reset()
  quotationForm.clearErrors()
  quotationForm.tax_rate = 11
  quotationForm.dp_percentage = 30
  quotationForm.valid_until = todayDate(14)
  quotationForm.items = [blankItem()]
}

function submitQuotation() {
  const options = {
    preserveScroll: true,
    onSuccess: () => closeQuotationModal(),
  }

  if (isEditingQuotation.value) {
    quotationForm.patch(`${quotationsBaseUrl}/${encodeURIComponent(editingQuotationId.value)}`, options)
    return
  }

  quotationForm.post(quotationsBaseUrl, options)
}

function deleteQuotation(quotationId) {
  if (!confirm('Hapus penawaran ini?')) {
    return
  }

  router.delete(`${quotationsBaseUrl}/${encodeURIComponent(quotationId)}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (selectedQuotationId.value === quotationId) {
        closeDetailModal()
      }
    },
  })
}

function openQuotationDetail(quotationId) {
  selectedQuotationId.value = quotationId
  showDetailModal.value = true
}

function closeDetailModal() {
  showDetailModal.value = false
  selectedQuotationId.value = null
}

function addItem() {
  quotationForm.items.push(blankItem())
}

function appendPresetItem(category) {
  if (!showQuotationModal.value) {
    openQuotationModal()
  }

  quotationForm.items.push({
    name: category.label,
    description: '',
    category: category.value,
    quantity: 1,
    unit: 'paket',
    unit_price: 0,
    discount_amount: 0,
  })
}

function removeItem(index) {
  if (quotationForm.items.length === 1) {
    quotationForm.items = [blankItem()]
    return
  }

  quotationForm.items.splice(index, 1)
}

function itemSubtotal(item) {
  return Math.max(0, (Number(item.quantity || 0) * Number(item.unit_price || 0)) - Number(item.discount_amount || 0))
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function quotationStatusClass(status) {
  const map = {
    draft: 'bg-stone-100 text-stone-600',
    sent: 'bg-sky-100 text-sky-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-rose-100 text-rose-700',
    revised: 'bg-amber-100 text-amber-700',
  }

  return map[status] || 'bg-stone-100 text-stone-600'
}

function sendQuotation(quotationId) {
  router.post(`${quotationsBaseUrl}/${encodeURIComponent(quotationId)}/send`, {}, {
    preserveScroll: true,
  })
}

function approveLocally(quotationId) {
  router.patch(`${quotationsBaseUrl}/${encodeURIComponent(quotationId)}/status`, {
    status: 'approved',
  }, {
    preserveScroll: true,
  })
}

function convertQuotation(quotationId) {
  router.post(`${quotationsBaseUrl}/${encodeURIComponent(quotationId)}/convert`, {
    create_project: true,
  }, {
    preserveScroll: true,
  })
}

async function copyApprovalLink(quotation) {
  if (!quotation?.approval_url) {
    return
  }

  try {
    await navigator.clipboard.writeText(quotation.approval_url)
  } catch {
    // Ignore clipboard failures.
  }
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
    category: '',
    quantity: 1,
    unit: 'paket',
    unit_price: 0,
    discount_amount: 0,
  }
}

function cloneQuotations(items) {
  return items.map((quotation) => ({
    ...quotation,
    client: quotation.client ? { ...quotation.client } : null,
    lead: quotation.lead ? { ...quotation.lead } : null,
    creator: quotation.creator ? { ...quotation.creator } : null,
    approver: quotation.approver ? { ...quotation.approver } : null,
    invoice: quotation.invoice ? { ...quotation.invoice, project: quotation.invoice.project ? { ...quotation.invoice.project } : null } : null,
    counts: quotation.counts ? { ...quotation.counts } : {},
    items: Array.isArray(quotation.items) ? quotation.items.map((item) => ({ ...item })) : [],
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
