<template>
  <WorkspaceLayout
    title="Pemasaran"
    subtitle="Menu 33-36 untuk ringkasan kampanye, perencanaan media sosial, kampanye email, dan analitik yang dibaca dari data nyata workspace."
  >
    <template #actions>
      <button
        v-if="activeMeta.actionLabel"
        type="button"
        @click="openActiveModal()"
        class="inline-flex items-center gap-2 rounded-2xl bg-stone-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:-translate-y-0.5 hover:bg-stone-800"
      >
        <Plus class="h-4 w-4" />
        <span>{{ activeMeta.actionLabel }}</span>
      </button>
    </template>

    <MarketingLayout :workspace="workspace">
      <div class="space-y-6">
        <section class="project-hero-shell">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="project-hero-copy">
              <p class="project-hero-kicker">Menu {{ activeMeta.menu }} / {{ activeMeta.label }}</p>
              <h2 class="project-hero-title">{{ activeMeta.headline }}</h2>
              <p class="project-hero-desc">{{ activeMeta.copy }}</p>
            </div>

            <div class="compact-stat-grid min-w-full gap-3 sm:min-w-[22rem] sm:grid-cols-4 xl:w-[34rem]">
              <div v-for="card in activeStatCards" :key="card.label" class="compact-stat-card">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                <p v-if="card.note" class="mt-1 text-xs leading-5 text-stone-500">{{ card.note }}</p>
              </div>
            </div>
          </div>

          <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div v-for="signal in activeSignals" :key="signal.label" class="compact-stat-card">
              <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ signal.label }}</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ signal.copy }}</p>
            </div>
          </div>
        </section>

        <section class="project-panel-shell">
          <div class="filter-panel-head mb-5">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Filter</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ activeMeta.filterTitle }}</h2>
            </div>
            <div class="filter-meta-badge">
              <span class="font-semibold text-stone-950">{{ filteredCount }}</span> item tampil
            </div>
          </div>

          <div class="filter-toolbar grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm xl:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Cari</span>
              <input
                v-model="filterState.search"
                type="text"
                placeholder="Masukkan kata kunci pencarian..."
                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ activeMeta.statusLabel }}</span>
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua Status</option>
                <option v-for="item in statusOptions" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ activeMeta.secondaryFilterLabel }}</span>
              <select v-model="filterState.secondary" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="item in secondaryOptions" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
          </div>

          <div class="filter-actions mt-5">
            <button
              type="button"
              @click="resetFilters"
              class="inline-flex items-center gap-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition-all hover:bg-stone-100 hover:text-stone-900"
            >
              <RotateCcw class="h-4 w-4" />
              <span>Atur Ulang Filter</span>
            </button>
          </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">{{ activeMeta.boardLabel }}</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ activeMeta.boardTitle }}</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ filteredCount }} item</span>
            </div>

            <div v-if="activeTab === 'overview'" class="mt-5 space-y-4">
              <article v-for="campaign in filteredCampaigns" :key="campaign.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ campaign.name }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="campaignStatusClass(campaign.status)">{{ formatOption(campaign.status) }}</span>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="campaignTypeClass(campaign.type)">{{ formatOption(campaign.type) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">Sumber {{ formatOption(campaign.primary_source) }} / {{ campaign.external_reference || 'Tanpa referensi eksternal' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">Anggaran {{ formatMoney(campaign.budget) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Pengeluaran {{ formatMoney(campaign.spend) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Prospek {{ formatNumber(campaign.leads_generated) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">ROI {{ formatPercent(campaign.roi_percentage) }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <Link :href="`${marketingBaseUrl}/campaigns/${campaign.id}`" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Eye class="h-4 w-4" />
                      <span>Lihat Detail</span>
                    </Link>
                    <button type="button" @click="openCampaignModal(campaign)" title="Ubah" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteCampaign(campaign.id)" title="Hapus" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                  <div class="rounded-[1.2rem] border border-white bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Periode</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ campaign.start_date_label }} - {{ campaign.end_date_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-white bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Lalu Lintas</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ formatNumber(campaign.traffic_sessions) }} Sesi</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-white bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Diperbarui</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ campaign.updated_at_label }}</p>
                  </div>
                </div>
              </article>

              <div v-if="filteredCampaigns.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada kampanye yang cocok dengan filter saat ini.
              </div>
            </div>

            <div v-else-if="activeTab === 'social'" class="mt-5 space-y-4">
              <article v-for="post in filteredPosts" :key="post.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ post.title || 'Postingan tanpa judul' }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="socialStatusClass(post.status)">{{ formatOption(post.status) }}</span>
                      <span v-for="platform in post.platforms.slice(0, 3)" :key="platform" class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] bg-white text-stone-500">{{ formatOption(platform) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ post.client?.name || 'Tanpa klien' }}</p>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ post.caption || 'Tanpa keterangan' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">Jangkauan {{ formatNumber(post.reach) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Interaksi {{ formatNumber(post.engagement) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Klik {{ formatNumber(post.clicks) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ post.scheduled_at_label }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <Link :href="`${marketingBaseUrl}/social-posts/${post.id}`" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Eye class="h-4 w-4" />
                      <span>Lihat Detail</span>
                    </Link>
                    <button type="button" @click="openPostModal(post)" title="Ubah" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deletePost(post.id)" title="Hapus" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>

              <div v-if="filteredPosts.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada post sosial yang cocok dengan filter saat ini.
              </div>
            </div>

            <div v-else-if="activeTab === 'email'" class="mt-5 space-y-4">
              <article v-for="newsletter in filteredNewsletters" :key="newsletter.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ newsletter.subject }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="newsletterStatusClass(newsletter.status)">{{ formatOption(newsletter.status) }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ newsletter.excerpt }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">Buka {{ formatPercent(newsletter.open_rate) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Klik {{ formatPercent(newsletter.click_rate) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Mental {{ formatPercent(newsletter.bounce_rate) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ newsletter.scheduled_at_label }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <Link :href="`${marketingBaseUrl}/newsletters/${newsletter.id}`" class="inline-flex items-center gap-2 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Eye class="h-4 w-4" />
                      <span>Lihat Detail</span>
                    </Link>
                    <button type="button" @click="openNewsletterModal(newsletter)" title="Ubah" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteNewsletter(newsletter.id)" title="Hapus" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>

              <div v-if="filteredNewsletters.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada newsletter yang cocok dengan filter saat ini.
              </div>
            </div>

            <div v-else class="mt-5 space-y-4">
              <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Lalu Lintas / Prospek</p>
                  <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div v-for="card in analyticsCardsLeft" :key="card.label" class="rounded-[1.2rem] border border-white bg-white p-4">
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                      <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                    </div>
                  </div>
                </div>

                <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kesehatan Email</p>
                  <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div v-for="card in analyticsCardsRight" :key="card.label" class="rounded-[1.2rem] border border-white bg-white p-4">
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                      <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Rincian Sumber</p>
                  <div class="mt-4 space-y-3">
                    <div v-for="source in sourceBreakdown" :key="source.source" class="rounded-[1.2rem] border border-white bg-white p-4">
                      <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-stone-950">{{ formatOption(source.source) }}</p>
                        <p class="text-xs text-stone-500">{{ formatNumber(source.leads) }} prospek</p>
                      </div>
                      <p class="mt-2 text-xs text-stone-500">{{ formatNumber(source.campaigns) }} kampanye / {{ formatNumber(source.sessions) }} sesi</p>
                    </div>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Daftar Pelanggan</p>
                    <button type="button" @click="openSubscriberModal()" title="Tambah Pelanggan" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-white p-2 text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
                      <Plus class="h-3.5 w-3.5" />
                    </button>
                  </div>
                  <div class="mt-4 space-y-3">
                    <div v-for="subscriber in filteredSubscribers" :key="subscriber.id" class="rounded-[1.2rem] border border-white bg-white p-4">
                      <div class="flex items-start justify-between gap-3">
                        <div>
                          <p class="text-sm font-semibold text-stone-950">{{ subscriber.name || subscriber.email }}</p>
                          <p class="mt-1 text-xs text-stone-500">{{ subscriber.email }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="subscriber.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">{{ subscriber.is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                          <button type="button" @click="openSubscriberModal(subscriber)" title="Ubah" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-500 transition hover:text-stone-950">
                            <Pencil class="h-3.5 w-3.5" />
                          </button>
                          <button type="button" @click="deleteSubscriber(subscriber.id)" title="Hapus" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                            <Trash2 class="h-3.5 w-3.5" />
                          </button>
                        </div>
                      </div>
                      <p class="mt-2 text-xs text-stone-500">{{ subscriber.client?.name || 'Tanpa klien' }} / {{ subscriber.unsubscribed_at_label }}</p>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">{{ activeMeta.sideLabel }}</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ activeMeta.sideTitle }}</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ panel.title }}</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
              </div>
            </div>
          </article>
        </section>
      </div>
    </MarketingLayout>

    <Transition name="modal">
      <div v-if="showCampaignModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitCampaign">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kampanye Pemasaran</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingCampaignId ? 'Ubah Kampanye' : 'Kampanye Baru' }}</h3>
            </div>
            <button type="button" @click="closeCampaignModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama Kampanye</span>
              <input v-model="campaignForm.name" type="text" placeholder="Contoh: Promo Akhir Tahun" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tipe</span>
              <select v-model="campaignForm.type" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.campaignTypes" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="campaignForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.campaignStatuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Anggaran</span>
              <input v-model="campaignForm.budget" type="number" step="1000" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Pengeluaran</span>
              <input v-model="campaignForm.spend" type="number" step="1000" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Mulai</span>
              <input v-model="campaignForm.start_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tanggal Selesai</span>
              <input v-model="campaignForm.end_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Sumber Utama</span>
              <select v-model="campaignForm.primary_source" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Semua Sumber</option>
                <option v-for="item in options.campaignSources" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Referensi Eksternal</span>
              <input v-model="campaignForm.external_reference" type="text" placeholder="ID Iklan, dsb" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Prospek (Leads)</span>
              <input v-model="campaignForm.leads_generated" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Sesi Lalu Lintas</span>
              <input v-model="campaignForm.traffic_sessions" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">ROI %</span>
              <input v-model="campaignForm.roi_percentage" type="number" step="0.1" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeCampaignModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="campaignForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingCampaignId ? 'Simpan Perubahan' : 'Buat Kampanye' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showPostModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitPost">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Postingan Media Sosial</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingPostId ? 'Ubah Post' : 'Post Baru' }}</h3>
            </div>
            <button type="button" @click="closePostModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
              <select v-model="postForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Semua Klien</option>
                <option v-for="client in options.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Judul</span>
              <input v-model="postForm.title" type="text" placeholder="Judul konten..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Keterangan (Caption)</span>
              <textarea v-model="postForm.caption" rows="4" placeholder="Tulis keterangan di sini..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white"></textarea>
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Tagar (Hashtags)</span>
              <input v-model="postForm.hashtags" type="text" placeholder="#digitalmarketing #agency" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <div class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Platform</span>
              <div class="grid gap-3 sm:grid-cols-3">
                <label v-for="platform in options.platforms" :key="platform" class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 cursor-pointer hover:bg-white transition-all">
                  <input v-model="postForm.platforms" type="checkbox" :value="platform" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>{{ formatOption(platform) }}</span>
                </label>
              </div>
            </div>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="postForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.postStatuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dijadwalkan Pada</span>
              <input v-model="postForm.scheduled_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Diposting Pada</span>
              <input v-model="postForm.posted_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Jangkauan (Reach)</span>
              <input v-model="postForm.reach" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Interaksi (Engagement)</span>
              <input v-model="postForm.engagement" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klik</span>
              <input v-model="postForm.clicks" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closePostModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="postForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingPostId ? 'Simpan Perubahan' : 'Buat Postingan' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showNewsletterModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitNewsletter">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kampanye Email</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingNewsletterId ? 'Ubah Newsletter' : 'Newsletter Baru' }}</h3>
            </div>
            <button type="button" @click="closeNewsletterModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Subjek</span>
              <input v-model="newsletterForm.subject" type="text" placeholder="Subjek email..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Konten</span>
              <textarea v-model="newsletterForm.content" rows="6" placeholder="Isi konten email..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white"></textarea>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="newsletterForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.newsletterStatuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Dijadwalkan Pada</span>
              <input v-model="newsletterForm.scheduled_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Waktu Terkirim</span>
              <input v-model="newsletterForm.sent_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rasio Buka %</span>
              <input v-model="newsletterForm.open_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rasio Klik %</span>
              <input v-model="newsletterForm.click_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Rasio Mental %</span>
              <input v-model="newsletterForm.bounce_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Berhenti Berlangganan %</span>
              <input v-model="newsletterForm.unsubscribe_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeNewsletterModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="newsletterForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingNewsletterId ? 'Simpan Perubahan' : 'Buat Newsletter' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showSubscriberModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitSubscriber">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pelanggan / Audiens</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingSubscriberId ? 'Ubah Pelanggan' : 'Pelanggan Baru' }}</h3>
            </div>
            <button type="button" @click="closeSubscriberModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
              <select v-model="subscriberForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Semua Klien</option>
                <option v-for="client in options.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Nama</span>
              <input v-model="subscriberForm.name" type="text" placeholder="Nama pelanggan..." class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</span>
              <input v-model="subscriberForm.email" type="email" placeholder="email@contoh.com" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 cursor-pointer hover:bg-white transition-all">
              <input v-model="subscriberForm.is_active" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Pelanggan Aktif</span>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Berhenti Berlangganan Pada</span>
              <input v-model="subscriberForm.unsubscribed_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeSubscriberModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="subscriberForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingSubscriberId ? 'Simpan Perubahan' : 'Buat Pelanggan' }}</button>
          </div>
        </form>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { Eye, Pencil, Plus, RotateCcw, Trash2 } from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import MarketingLayout from '../../Layouts/MarketingLayout.vue'

const props = defineProps({
  workspace: Object,
  activeTab: String,
  campaigns: Array,
  socialPosts: Array,
  newsletters: Array,
  subscribers: Array,
  options: Object,
  summary: Object,
})

const marketingBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/marketing`
const activeTab = computed(() => props.activeTab || 'overview')
const filterState = reactive({ search: '', status: '', secondary: '' })

const tabMeta = {
  overview: {
    menu: 33,
    label: 'Ringkasan Pemasaran',
    actionLabel: 'Kampanye Baru',
    headline: 'Kampanye ringkas, pengeluaran terbaca, dan prospek pemasaran tidak tercecer.',
    copy: 'Dashboard pemasaran dibaca dari kampanye nyata, rincian sumber, dan konten yang paling kuat di workspace.',
    filterTitle: 'Cari kampanye berdasarkan nama, sumber, status, atau tipe.',
    statusLabel: 'Status',
    secondaryFilterLabel: 'Tipe',
    boardLabel: 'Daftar Kampanye',
    boardTitle: 'Semua kampanye aktif dan riwayat pengeluaran workspace.',
    sideLabel: 'Sinyal ringkasan',
    sideTitle: 'Apa yang menggerakkan angka',
  },
  social: {
    menu: 34,
    label: 'Perencanaan Media Sosial',
    actionLabel: 'Post Baru',
    headline: 'Konten media sosial disusun sebagai alur kerja, bukan daftar keterangan yang tercecer.',
    copy: 'Ide, draf, tinjauan, dijadwalkan, dan diposting dibaca per platform supaya tim mudah melihat progres konten.',
    filterTitle: 'Cari postingan berdasarkan judul, klien, status, atau platform.',
    statusLabel: 'Status',
    secondaryFilterLabel: 'Platform',
    boardLabel: 'Perencana Media Sosial',
    boardTitle: 'Semua postingan dan jadwal terbit workspace.',
    sideLabel: 'Sinyal perencanaan',
    sideTitle: 'Penjadwalan dan interaksi',
  },
  email: {
    menu: 35,
    label: 'Kampanye Email',
    actionLabel: 'Newsletter Baru',
    headline: 'Newsletter dan audiens disatukan supaya pengiriman, jadwal, dan pelacakan tidak terpisah.',
    copy: 'Draf, dijadwalkan, mengirim, dan terkirim dibaca bersama daftar pelanggan dan pelacakan rasio yang tersimpan.',
    filterTitle: 'Cari newsletter berdasarkan subjek atau isi konten.',
    statusLabel: 'Status',
    secondaryFilterLabel: 'Audiens',
    boardLabel: 'Perpustakaan Email',
    boardTitle: 'Newsletter dan pelanggan workspace.',
    sideLabel: 'Sinyal email',
    sideTitle: 'Kesehatan audiens',
  },
  analytics: {
    menu: 36,
    label: 'Analitik',
    actionLabel: '',
    headline: 'Analitik dibaca dari kampanye, post sosial, dan newsletter yang memang tersimpan di workspace.',
    copy: 'Sesi lalu lintas, ROI, interaksi, rasio buka, dan rincian sumber prospek dikumpulkan dari data pemasaran aktif.',
    filterTitle: 'Cari rincian sumber atau kampanye yang relevan.',
    statusLabel: 'Tipe',
    secondaryFilterLabel: 'Sumber',
    boardLabel: 'Analitik Pemasaran',
    boardTitle: 'Ringkasan performa lintas kanal.',
    sideLabel: 'Sinyal analitik',
    sideTitle: 'Lensa performa',
  },
}

const activeMeta = computed(() => tabMeta[activeTab.value] ?? tabMeta.overview)

const activeStatCards = computed(() => {
  const cards = {
    overview: [
      { label: 'Kampanye', value: props.summary.overview.total_campaigns },
      { label: 'Aktif', value: props.summary.overview.active_campaigns },
      { label: 'Pengeluaran', value: formatMoney(props.summary.overview.total_spend) },
      { label: 'ROI', value: `${props.summary.overview.avg_roi}%` },
    ],
    social: [
      { label: 'Postingan', value: props.summary.social.total_posts },
      { label: 'Dijadwalkan', value: props.summary.social.scheduled_posts },
      { label: 'Jangkauan', value: formatNumber(props.summary.social.reach) },
      { label: 'Interaksi', value: formatNumber(props.summary.social.engagement) },
    ],
    email: [
      { label: 'Newsletter', value: props.summary.email.total_newsletters },
      { label: 'Terkirim', value: props.summary.email.sent_newsletters },
      { label: 'Buka', value: `${props.summary.email.avg_open_rate}%` },
      { label: 'Klik', value: `${props.summary.email.avg_click_rate}%` },
    ],
    analytics: [
      { label: 'Sesi', value: formatNumber(props.summary.analytics.traffic_sessions) },
      { label: 'Meta Spend', value: formatMoney(props.summary.analytics.meta_spend) },
      { label: 'TikTok Spend', value: formatMoney(props.summary.analytics.tiktok_spend) },
      { label: 'Prospek', value: props.summary.overview.total_leads },
    ],
  }

  return cards[activeTab.value] ?? cards.overview
})

const activeSignals = computed(() => {
  const map = {
    overview: [
      { label: 'Sumber Prospek', copy: `${props.summary.overview.total_leads} prospek sudah direkam dari sumber kampanye.` },
      { label: 'Postur Pengeluaran', copy: `${formatMoney(props.summary.overview.total_spend)} pengeluaran terpakai dari anggaran ${formatMoney(props.summary.overview.total_budget)}.` },
      { label: 'Konten Terbaik', copy: props.summary.overview.top_content || 'Belum ada konten terbaik yang terbaca.' },
    ],
    social: [
      { label: 'Penerbitan', copy: `${props.summary.social.posted_posts} postingan sudah terbit dan ${props.summary.social.scheduled_posts} masih terjadwal.` },
      { label: 'Tumpukan Jangkauan', copy: `${formatNumber(props.summary.social.reach)} jangkauan terkumpul dari perencanaan media sosial.` },
      { label: 'Klik', copy: `${formatNumber(props.summary.social.clicks)} klik tercatat di analitik postingan.` },
    ],
    email: [
      { label: 'Audiens', copy: `${props.summary.email.active_subscribers} pelanggan aktif dan ${props.summary.email.inactive_subscribers} tidak aktif.` },
      { label: 'Rasio Buka', copy: `${props.summary.email.avg_open_rate}% rasio buka rata-rata dari newsletter.` },
      { label: 'Rasio Klik', copy: `${props.summary.email.avg_click_rate}% rasio klik rata-rata dari newsletter.` },
    ],
    analytics: [
      { label: 'Sesi', copy: `${formatNumber(props.summary.analytics.traffic_sessions)} sesi lalu lintas dibaca dari kampanye.` },
      { label: 'Kesehatan Email', copy: `Buka ${props.summary.analytics.email_open_rate}% dan klik ${props.summary.analytics.email_click_rate}% untuk pelacakan email.` },
      { label: 'Rincian', copy: `${sourceBreakdown.value.length} sumber berbeda sudah masuk ke analisis.` },
    ],
  }

  return map[activeTab.value] ?? map.overview
})

const analyticsCardsLeft = computed(() => [
  { label: 'Sesi Lalu Lintas', value: formatNumber(props.summary.analytics.traffic_sessions) },
  { label: 'Total Prospek', value: formatNumber(props.summary.overview.total_leads) },
  { label: 'Meta Spend', value: formatMoney(props.summary.analytics.meta_spend) },
  { label: 'TikTok Spend', value: formatMoney(props.summary.analytics.tiktok_spend) },
])

const analyticsCardsRight = computed(() => [
  { label: 'Email Buka', value: `${props.summary.analytics.email_open_rate}%` },
  { label: 'Email Klik', value: `${props.summary.analytics.email_click_rate}%` },
  { label: 'Rata-rata ROI', value: `${props.summary.overview.avg_roi}%` },
  { label: 'Konten Terbaik', value: props.summary.overview.top_content || 'Tidak Ada' },
])

const activePanels = computed(() => {
  const panels = {
    overview: [
      { title: 'Komposisi sumber prospek', copy: 'Rincian sumber dipakai untuk membaca kanal mana yang paling banyak menyumbang prospek dan sesi.' },
      { title: 'Kontrol anggaran', copy: 'Pengeluaran dan anggaran tampil berdampingan supaya keputusan penskalaan lebih cepat diambil.' },
      { title: 'Pembacaan ROI', copy: 'ROI disimpan di meta kampanye agar laporan performa bisa dihitung dari data nyata.' },
    ],
    social: [
      { title: 'Penyebaran platform', copy: 'Distribusi platform membantu melihat konten mana yang cocok untuk Instagram, TikTok, atau LinkedIn.' },
      { title: 'Analitik postingan', copy: 'Jangkauan, interaksi, dan klik ditarik langsung dari analitik tiap postingan.' },
      { title: 'Status alur kerja', copy: 'Ide sampai diposting dibaca sebagai alur kerja yang mudah dilacak.' },
    ],
    email: [
      { title: 'Kebersihan audiens', copy: 'Pelanggan aktif dan tidak aktif dipantau bersama supaya daftar tetap sehat.' },
      { title: 'Rasio pengiriman', copy: 'Buka, klik, mental, dan berhenti berlangganan disimpan di metrik newsletter.' },
      { title: 'Penjadwalan', copy: 'Tanda waktu dijadwalkan dan dikirim dipakai untuk membaca siklus hidup kampanye email.' },
    ],
    analytics: [
      { title: 'Lensa lalu lintas', copy: 'Sesi lalu lintas dan prospek digabung untuk membaca kontribusi kanal ke hasil akhir.' },
      { title: 'Pengeluaran kanal', copy: 'Meta Ads dan TikTok Ads dibedakan agar pengeluaran tiap kanal terlihat jelas.' },
      { title: 'Performa email', copy: 'Rasio buka dan rasio klik jadi pembanding saat kampanye email dijalankan.' },
    ],
  }

  return panels[activeTab.value] ?? panels.overview
})

const filteredCampaigns = computed(() => props.campaigns.filter((item) => {
  const search = filterState.search.toLowerCase()
  const matchesSearch = !search || [item.name, item.primary_source, item.external_reference, item.type].filter(Boolean).some((value) => String(value).toLowerCase().includes(search))
  const matchesStatus = !filterState.status || item.status === filterState.status
  const matchesSecondary = !filterState.secondary || item.type === filterState.secondary
  return matchesSearch && matchesStatus && matchesSecondary
}))

const filteredPosts = computed(() => props.socialPosts.filter((item) => {
  const search = filterState.search.toLowerCase()
  const matchesSearch = !search || [item.title, item.caption, item.client?.name, item.hashtags].filter(Boolean).some((value) => String(value).toLowerCase().includes(search))
  const matchesStatus = !filterState.status || item.status === filterState.status
  const matchesSecondary = !filterState.secondary || item.platforms.includes(filterState.secondary)
  return matchesSearch && matchesStatus && matchesSecondary
}))

const filteredNewsletters = computed(() => props.newsletters.filter((item) => {
  const search = filterState.search.toLowerCase()
  const matchesSearch = !search || [item.subject, item.excerpt, item.creator?.name].filter(Boolean).some((value) => String(value).toLowerCase().includes(search))
  const matchesStatus = !filterState.status || item.status === filterState.status
  return matchesSearch && matchesStatus
}))

const filteredSubscribers = computed(() => props.subscribers.filter((item) => {
  const search = filterState.search.toLowerCase()
  const matchesSearch = !search || [item.name, item.email, item.client?.name].filter(Boolean).some((value) => String(value).toLowerCase().includes(search))
  const matchesSecondary = !filterState.secondary || (filterState.secondary === 'active' ? item.is_active : !item.is_active)
  return matchesSearch && matchesSecondary
}))

const sourceBreakdown = computed(() => props.summary.analytics.source_breakdown || [])

const filteredCount = computed(() => {
  if (activeTab.value === 'overview') return filteredCampaigns.value.length
  if (activeTab.value === 'social') return filteredPosts.value.length
  if (activeTab.value === 'email') return filteredNewsletters.value.length
  return sourceBreakdown.value.length
})

const statusOptions = computed(() => {
  if (activeTab.value === 'overview') return props.options.campaignStatuses
  if (activeTab.value === 'social') return props.options.postStatuses
  if (activeTab.value === 'email') return props.options.newsletterStatuses
  return props.options.campaignTypes
})

const secondaryOptions = computed(() => {
  if (activeTab.value === 'overview') return props.options.campaignTypes
  if (activeTab.value === 'social') return props.options.platforms
  if (activeTab.value === 'email') return ['active', 'inactive']
  return props.options.campaignSources
})

function resetFilters() {
  filterState.search = ''
  filterState.status = ''
  filterState.secondary = ''
}

function formatOption(value) {
  const translations = {
    // Status Kampanye
    'planning': 'Perencanaan',
    'active': 'Aktif',
    'paused': 'Ditangguhkan',
    'completed': 'Selesai',
    
    // Status Social Post
    'idea': 'Ide',
    'draft': 'Draf',
    'review': 'Tinjauan',
    'scheduled': 'Dijadwalkan',
    'posted': 'Diposting',
    
    // Status Newsletter
    'sending': 'Mengirim',
    'sent': 'Terkirim',
    
    // Tipe / Platform
    'google_ads': 'Google Ads',
    'meta_ads': 'Meta Ads',
    'tiktok_ads': 'TikTok Ads',
    'email': 'Email',
    'whatsapp': 'WhatsApp',
    'seo': 'SEO',
    'content': 'Konten',
    'instagram': 'Instagram',
    'facebook': 'Facebook',
    'twitter': 'Twitter',
    'linkedin': 'LinkedIn',
    'youtube': 'YouTube',
    'inactive': 'Tidak Aktif'
  }
  
  return translations[value] || String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function formatNumber(value) {
  return new Intl.NumberFormat('id-ID').format(Number(value || 0))
}

function formatMoney(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0))
}

function formatPercent(value) {
  return `${Number(value || 0).toFixed(1)}%`
}

function campaignStatusClass(status) {
  return {
    planning: 'bg-stone-200 text-stone-700',
    active: 'bg-emerald-100 text-emerald-700',
    paused: 'bg-amber-100 text-amber-700',
    completed: 'bg-sky-100 text-sky-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

function campaignTypeClass(type) {
  return {
    google_ads: 'bg-sky-100 text-sky-700',
    meta_ads: 'bg-blue-100 text-blue-700',
    tiktok_ads: 'bg-fuchsia-100 text-fuchsia-700',
    email: 'bg-emerald-100 text-emerald-700',
    whatsapp: 'bg-rose-100 text-rose-700',
    seo: 'bg-amber-100 text-amber-700',
    content: 'bg-stone-200 text-stone-700',
  }[type] || 'bg-stone-100 text-stone-600'
}

function socialStatusClass(status) {
  return {
    idea: 'bg-stone-200 text-stone-700',
    draft: 'bg-amber-100 text-amber-700',
    review: 'bg-sky-100 text-sky-700',
    scheduled: 'bg-emerald-100 text-emerald-700',
    posted: 'bg-stone-950 text-white',
  }[status] || 'bg-stone-100 text-stone-600'
}

function newsletterStatusClass(status) {
  return {
    draft: 'bg-stone-200 text-stone-700',
    scheduled: 'bg-amber-100 text-amber-700',
    sending: 'bg-sky-100 text-sky-700',
    sent: 'bg-emerald-100 text-emerald-700',
  }[status] || 'bg-stone-100 text-stone-600'
}

const showCampaignModal = ref(false)
const editingCampaignId = ref(null)
const campaignForm = useForm({
  name: '',
  type: 'meta_ads',
  status: 'planning',
  budget: '',
  spend: '',
  start_date: '',
  end_date: '',
  roi_percentage: '',
  leads_generated: '',
  traffic_sessions: '',
  primary_source: '',
  external_reference: '',
})

function openCampaignModal(item = null) {
  editingCampaignId.value = item?.id || null
  campaignForm.reset()
  campaignForm.clearErrors()
  campaignForm.name = item?.name || ''
  campaignForm.type = item?.type || 'meta_ads'
  campaignForm.status = item?.status || 'planning'
  campaignForm.budget = item?.budget || ''
  campaignForm.spend = item?.spend || ''
  campaignForm.start_date = item?.start_date || ''
  campaignForm.end_date = item?.end_date || ''
  campaignForm.roi_percentage = item?.roi_percentage || ''
  campaignForm.leads_generated = item?.leads_generated || ''
  campaignForm.traffic_sessions = item?.traffic_sessions || ''
  campaignForm.primary_source = item?.primary_source || ''
  campaignForm.external_reference = item?.external_reference || ''
  showCampaignModal.value = true
}

function closeCampaignModal() {
  showCampaignModal.value = false
  editingCampaignId.value = null
}

function submitCampaign() {
  const url = editingCampaignId.value ? `${marketingBaseUrl}/campaigns/${encodeURIComponent(editingCampaignId.value)}` : `${marketingBaseUrl}/campaigns`
  const method = editingCampaignId.value ? campaignForm.patch : campaignForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeCampaignModal() })
}

function deleteCampaign(id) {
  if (!confirm('Hapus kampanye ini?')) return
  router.delete(`${marketingBaseUrl}/campaigns/${encodeURIComponent(id)}`, { preserveScroll: true })
}

const showPostModal = ref(false)
const editingPostId = ref(null)
const postForm = useForm({
  client_id: '',
  title: '',
  caption: '',
  hashtags: '',
  platforms: [],
  status: 'idea',
  scheduled_at: '',
  posted_at: '',
  reach: '',
  engagement: '',
  clicks: '',
})

function openPostModal(item = null) {
  editingPostId.value = item?.id || null
  postForm.reset()
  postForm.clearErrors()
  postForm.client_id = item?.client_id || ''
  postForm.title = item?.title || ''
  postForm.caption = item?.caption || ''
  postForm.hashtags = item?.hashtags || ''
  postForm.platforms = Array.isArray(item?.platforms) ? [...item.platforms] : []
  postForm.status = item?.status || 'idea'
  postForm.scheduled_at = item?.scheduled_at || ''
  postForm.posted_at = item?.posted_at || ''
  postForm.reach = item?.reach || ''
  postForm.engagement = item?.engagement || ''
  postForm.clicks = item?.clicks || ''
  showPostModal.value = true
}

function closePostModal() {
  showPostModal.value = false
  editingPostId.value = null
}

function submitPost() {
  const url = editingPostId.value ? `${marketingBaseUrl}/social-posts/${encodeURIComponent(editingPostId.value)}` : `${marketingBaseUrl}/social-posts`
  const method = editingPostId.value ? postForm.patch : postForm.post
  method(url, { preserveScroll: true, onSuccess: () => closePostModal() })
}

function deletePost(id) {
  if (!confirm('Hapus postingan sosial ini?')) return
  router.delete(`${marketingBaseUrl}/social-posts/${encodeURIComponent(id)}`, { preserveScroll: true })
}

const showNewsletterModal = ref(false)
const editingNewsletterId = ref(null)
const newsletterForm = useForm({
  subject: '',
  content: '',
  status: 'draft',
  scheduled_at: '',
  sent_at: '',
  open_rate: '',
  click_rate: '',
  bounce_rate: '',
  unsubscribe_rate: '',
})

function openNewsletterModal(item = null) {
  editingNewsletterId.value = item?.id || null
  newsletterForm.reset()
  newsletterForm.clearErrors()
  newsletterForm.subject = item?.subject || ''
  newsletterForm.content = item?.content || ''
  newsletterForm.status = item?.status || 'draft'
  newsletterForm.scheduled_at = item?.scheduled_at || ''
  newsletterForm.sent_at = item?.sent_at || ''
  newsletterForm.open_rate = item?.open_rate || ''
  newsletterForm.click_rate = item?.click_rate || ''
  newsletterForm.bounce_rate = item?.bounce_rate || ''
  newsletterForm.unsubscribe_rate = item?.unsubscribe_rate || ''
  showNewsletterModal.value = true
}

function closeNewsletterModal() {
  showNewsletterModal.value = false
  editingNewsletterId.value = null
}

function submitNewsletter() {
  const url = editingNewsletterId.value ? `${marketingBaseUrl}/newsletters/${encodeURIComponent(editingNewsletterId.value)}` : `${marketingBaseUrl}/newsletters`
  const method = editingNewsletterId.value ? newsletterForm.patch : newsletterForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeNewsletterModal() })
}

function deleteNewsletter(id) {
  if (!confirm('Hapus newsletter ini?')) return
  router.delete(`${marketingBaseUrl}/newsletters/${encodeURIComponent(id)}`, { preserveScroll: true })
}

const showSubscriberModal = ref(false)
const editingSubscriberId = ref(null)
const subscriberForm = useForm({
  client_id: '',
  name: '',
  email: '',
  is_active: true,
  unsubscribed_at: '',
})

function openSubscriberModal(item = null) {
  editingSubscriberId.value = item?.id || null
  subscriberForm.reset()
  subscriberForm.clearErrors()
  subscriberForm.client_id = item?.client_id || ''
  subscriberForm.name = item?.name || ''
  subscriberForm.email = item?.email || ''
  subscriberForm.is_active = item?.is_active ?? true
  subscriberForm.unsubscribed_at = item?.unsubscribed_at || ''
  showSubscriberModal.value = true
}

function closeSubscriberModal() {
  showSubscriberModal.value = false
  editingSubscriberId.value = null
}

function submitSubscriber() {
  const url = editingSubscriberId.value ? `${marketingBaseUrl}/subscribers/${encodeURIComponent(editingSubscriberId.value)}` : `${marketingBaseUrl}/subscribers`
  const method = editingSubscriberId.value ? subscriberForm.patch : subscriberForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeSubscriberModal() })
}

function deleteSubscriber(id) {
  if (!confirm('Hapus pelanggan ini?')) return
  router.delete(`${marketingBaseUrl}/subscribers/${encodeURIComponent(id)}`, { preserveScroll: true })
}

function openActiveModal() {
  if (activeTab.value === 'overview') openCampaignModal()
  else if (activeTab.value === 'social') openPostModal()
  else if (activeTab.value === 'email') openNewsletterModal()
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
