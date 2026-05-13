<template>
  <WorkspaceLayout
    title="Pemasaran"
    subtitle="Menu 33-36 untuk ringkasan campaign, social planner, email campaign, dan analytics yang dibaca dari data nyata workspace."
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
                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
              />
            </label>

            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ activeMeta.statusLabel }}</span>
              <select v-model="filterState.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
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
              <span>Atur Ulang</span>
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
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ filteredCount }} items</span>
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
                      <span class="rounded-full bg-white px-3 py-1.5">Budget {{ formatMoney(campaign.budget) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Spend {{ formatMoney(campaign.spend) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Leads {{ formatNumber(campaign.leads_generated) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">ROI {{ formatPercent(campaign.roi_percentage) }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openCampaignModal(campaign)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteCampaign(campaign.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                  <div class="rounded-[1.2rem] border border-white bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Period</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ campaign.start_date_label }} - {{ campaign.end_date_label }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-white bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Traffic</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ formatNumber(campaign.traffic_sessions) }}</p>
                  </div>
                  <div class="rounded-[1.2rem] border border-white bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Updated</p>
                    <p class="mt-2 text-sm font-semibold text-stone-950">{{ campaign.updated_at_label }}</p>
                  </div>
                </div>
              </article>

              <div v-if="filteredCampaigns.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada campaign yang cocok dengan filter saat ini.
              </div>
            </div>

            <div v-else-if="activeTab === 'social'" class="mt-5 space-y-4">
              <article v-for="post in filteredPosts" :key="post.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ post.title || 'Untitled post' }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="socialStatusClass(post.status)">{{ formatOption(post.status) }}</span>
                      <span v-for="platform in post.platforms.slice(0, 3)" :key="platform" class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] bg-white text-stone-500">{{ formatOption(platform) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ post.client?.name || 'Tanpa klien' }}</p>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ post.caption || 'Tanpa keterangan' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">Reach {{ formatNumber(post.reach) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Engagement {{ formatNumber(post.engagement) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Clicks {{ formatNumber(post.clicks) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ post.scheduled_at_label }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openPostModal(post)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deletePost(post.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>

              <div v-if="filteredPosts.length === 0" class="rounded-[1.6rem] border border-dashed border-stone-300 bg-stone-50 px-6 py-14 text-center text-sm leading-6 text-stone-500">
                Belum ada social post yang cocok dengan filter saat ini.
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
                      <span class="rounded-full bg-white px-3 py-1.5">Open {{ formatPercent(newsletter.open_rate) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Click {{ formatPercent(newsletter.click_rate) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">Bounce {{ formatPercent(newsletter.bounce_rate) }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ newsletter.scheduled_at_label }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openNewsletterModal(newsletter)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteNewsletter(newsletter.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
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
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Traffic / Leads</p>
                  <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div v-for="card in analyticsCardsLeft" :key="card.label" class="rounded-[1.2rem] border border-white bg-white p-4">
                      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                      <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                    </div>
                  </div>
                </div>

                <div class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Email Health</p>
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
                  <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Source Breakdown</p>
                  <div class="mt-4 space-y-3">
                    <div v-for="source in sourceBreakdown" :key="source.source" class="rounded-[1.2rem] border border-white bg-white p-4">
                      <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-stone-950">{{ formatOption(source.source) }}</p>
                        <p class="text-xs text-stone-500">{{ formatNumber(source.leads) }} leads</p>
                      </div>
                      <p class="mt-2 text-xs text-stone-500">{{ formatNumber(source.campaigns) }} campaigns / {{ formatNumber(source.sessions) }} sessions</p>
                    </div>
                  </div>
                </article>

                <article class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                  <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Subscriber List</p>
                    <button type="button" @click="openSubscriberModal()" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-white p-2 text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">
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
                          <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="subscriber.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">{{ subscriber.is_active ? 'Active' : 'Inactive' }}</span>
                          <button type="button" @click="openSubscriberModal(subscriber)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-500 transition hover:text-stone-950">
                            <Pencil class="h-3.5 w-3.5" />
                          </button>
                          <button type="button" @click="deleteSubscriber(subscriber.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
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
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Campaign Name</span>
              <input v-model="campaignForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
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
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Budget</span>
              <input v-model="campaignForm.budget" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Spend</span>
              <input v-model="campaignForm.spend" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Start Date</span>
              <input v-model="campaignForm.start_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">End Date</span>
              <input v-model="campaignForm.end_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Primary Source</span>
              <select v-model="campaignForm.primary_source" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="item in options.campaignSources" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">External Ref</span>
              <input v-model="campaignForm.external_reference" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Leads</span>
              <input v-model="campaignForm.leads_generated" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Traffic Sessions</span>
              <input v-model="campaignForm.traffic_sessions" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">ROI %</span>
              <input v-model="campaignForm.roi_percentage" type="number" step="0.1" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeCampaignModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="campaignForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingCampaignId ? 'Simpan Perubahan' : 'Buat Item' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showPostModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitPost">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Social Media Post</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingPostId ? 'Ubah Post' : 'Post Baru' }}</h3>
            </div>
            <button type="button" @click="closePostModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
              <select v-model="postForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="client in options.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Title</span>
              <input v-model="postForm.title" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Caption</span>
              <textarea v-model="postForm.caption" rows="4" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white"></textarea>
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Hashtags</span>
              <input v-model="postForm.hashtags" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <div class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Platforms</span>
              <div class="grid gap-3 sm:grid-cols-3">
                <label v-for="platform in options.platforms" :key="platform" class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
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
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Scheduled At</span>
              <input v-model="postForm.scheduled_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Posted At</span>
              <input v-model="postForm.posted_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Reach</span>
              <input v-model="postForm.reach" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Engagement</span>
              <input v-model="postForm.engagement" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Clicks</span>
              <input v-model="postForm.clicks" type="number" min="0" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closePostModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="postForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingPostId ? 'Simpan Perubahan' : 'Buat Item' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showNewsletterModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitNewsletter">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Email Campaign</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingNewsletterId ? 'Ubah Newsletter' : 'Newsletter Baru' }}</h3>
            </div>
            <button type="button" @click="closeNewsletterModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Subject</span>
              <input v-model="newsletterForm.subject" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Content</span>
              <textarea v-model="newsletterForm.content" rows="6" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white"></textarea>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Status</span>
              <select v-model="newsletterForm.status" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option v-for="item in options.newsletterStatuses" :key="item" :value="item">{{ formatOption(item) }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Scheduled At</span>
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
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Click Rate %</span>
              <input v-model="newsletterForm.click_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Bounce Rate %</span>
              <input v-model="newsletterForm.bounce_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Unsubscribe %</span>
              <input v-model="newsletterForm.unsubscribe_rate" type="number" step="0.1" min="0" max="100" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeNewsletterModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="newsletterForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingNewsletterId ? 'Simpan Perubahan' : 'Buat Item' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showSubscriberModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitSubscriber">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Subscriber</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingSubscriberId ? 'Ubah Pelanggan' : 'Pelanggan Baru' }}</h3>
            </div>
            <button type="button" @click="closeSubscriberModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Klien</span>
              <select v-model="subscriberForm.client_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Semua</option>
                <option v-for="client in options.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Name</span>
              <input v-model="subscriberForm.name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Email</span>
              <input v-model="subscriberForm.email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
              <input v-model="subscriberForm.is_active" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Pelanggan Aktif</span>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Unsubscribed At</span>
              <input v-model="subscriberForm.unsubscribed_at" type="datetime-local" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white" />
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeSubscriberModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="subscriberForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingSubscriberId ? 'Simpan Perubahan' : 'Buat Item' }}</button>
          </div>
        </form>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Pencil, Plus, RotateCcw, Trash2 } from 'lucide-vue-next'
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
    headline: 'Campaign ringkas, spend terbaca, dan lead marketing tidak tercecer.',
    copy: 'Dashboard marketing dibaca dari campaign nyata, source breakdown, dan konten yang paling kuat di workspace.',
    filterTitle: 'Cari campaign berdasarkan nama, source, status, atau type.',
    statusLabel: 'Status',
    secondaryFilterLabel: 'Type',
    boardLabel: 'Campaign Register',
    boardTitle: 'Semua campaign aktif dan history spend workspace.',
    sideLabel: 'Overview signals',
    sideTitle: 'What moves the numbers',
  },
  social: {
    menu: 34,
    label: 'Social Media Planner',
    actionLabel: 'Post Baru',
    headline: 'Konten social disusun sebagai pipeline, bukan daftar caption yang tercecer.',
    copy: 'Idea, draft, review, scheduled, dan posted dibaca per platform supaya tim mudah lihat progres konten.',
    filterTitle: 'Cari post berdasarkan judul, client, status, atau platform.',
    statusLabel: 'Status',
    secondaryFilterLabel: 'Platform',
    boardLabel: 'Social Planner',
    boardTitle: 'Semua post dan jadwal publish workspace.',
    sideLabel: 'Planner signals',
    sideTitle: 'Scheduling and engagement',
  },
  email: {
    menu: 35,
    label: 'Email Campaigns',
    actionLabel: 'Newsletter Baru',
    headline: 'Newsletter dan audience disatukan supaya blast, schedule, dan tracking tidak terpisah.',
    copy: 'Draft, scheduled, sending, dan sent dibaca bersama subscriber list dan rate tracking yang tersimpan.',
    filterTitle: 'Cari newsletter berdasarkan subject atau isi konten.',
    statusLabel: 'Status',
    secondaryFilterLabel: 'Audience',
    boardLabel: 'Email Library',
    boardTitle: 'Newsletter dan subscriber workspace.',
    sideLabel: 'Email signals',
    sideTitle: 'Audience health',
  },
  analytics: {
    menu: 36,
    label: 'Analytics',
    actionLabel: '',
    headline: 'Analytics dibaca dari campaign, social post, dan newsletter yang memang tersimpan di workspace.',
    copy: 'Traffic sessions, ROI, engagement, open rate, dan lead source breakdown dikumpulkan dari data marketing aktif.',
    filterTitle: 'Cari source breakdown atau campaign yang relevan.',
    statusLabel: 'Type',
    secondaryFilterLabel: 'Source',
    boardLabel: 'Analitik Pemasaran',
    boardTitle: 'Ringkasan performa lintas channel.',
    sideLabel: 'Analytics signals',
    sideTitle: 'Performance lens',
  },
}

const activeMeta = computed(() => tabMeta[activeTab.value] ?? tabMeta.overview)

const activeStatCards = computed(() => {
  const cards = {
    overview: [
      { label: 'Campaigns', value: props.summary.overview.total_campaigns },
      { label: 'Active', value: props.summary.overview.active_campaigns },
      { label: 'Spend', value: formatMoney(props.summary.overview.total_spend) },
      { label: 'ROI', value: `${props.summary.overview.avg_roi}%` },
    ],
    social: [
      { label: 'Posts', value: props.summary.social.total_posts },
      { label: 'Scheduled', value: props.summary.social.scheduled_posts },
      { label: 'Reach', value: formatNumber(props.summary.social.reach) },
      { label: 'Engagement', value: formatNumber(props.summary.social.engagement) },
    ],
    email: [
      { label: 'Newsletters', value: props.summary.email.total_newsletters },
      { label: 'Sent', value: props.summary.email.sent_newsletters },
      { label: 'Open', value: `${props.summary.email.avg_open_rate}%` },
      { label: 'Click', value: `${props.summary.email.avg_click_rate}%` },
    ],
    analytics: [
      { label: 'Sessions', value: formatNumber(props.summary.analytics.traffic_sessions) },
      { label: 'Meta Spend', value: formatMoney(props.summary.analytics.meta_spend) },
      { label: 'TikTok Spend', value: formatMoney(props.summary.analytics.tiktok_spend) },
      { label: 'Leads', value: props.summary.overview.total_leads },
    ],
  }

  return cards[activeTab.value] ?? cards.overview
})

const activeSignals = computed(() => {
  const map = {
    overview: [
      { label: 'Sumber prospek', copy: `${props.summary.overview.total_leads} prospek sudah direkam dari sumber kampanye.` },
      { label: 'Spend posture', copy: `${formatMoney(props.summary.overview.total_spend)} spend terpakai dari budget ${formatMoney(props.summary.overview.total_budget)}.` },
      { label: 'Top content', copy: props.summary.overview.top_content || 'Belum ada top content yang terbaca.' },
    ],
    social: [
      { label: 'Publishing', copy: `${props.summary.social.posted_posts} post sudah publish dan ${props.summary.social.scheduled_posts} masih terjadwal.` },
      { label: 'Reach stack', copy: `${formatNumber(props.summary.social.reach)} reach terkumpul dari social planner.` },
      { label: 'Clicks', copy: `${formatNumber(props.summary.social.clicks)} clicks tercatat di analytics post.` },
    ],
    email: [
      { label: 'Audience', copy: `${props.summary.email.active_subscribers} subscriber aktif dan ${props.summary.email.inactive_subscribers} nonaktif.` },
      { label: 'Open rate', copy: `${props.summary.email.avg_open_rate}% open rate rata-rata dari newsletter.` },
      { label: 'Click rate', copy: `${props.summary.email.avg_click_rate}% click rate rata-rata dari newsletter.` },
    ],
    analytics: [
      { label: 'Sessions', copy: `${formatNumber(props.summary.analytics.traffic_sessions)} traffic sessions dibaca dari campaign.` },
      { label: 'Email health', copy: `Open ${props.summary.analytics.email_open_rate}% dan click ${props.summary.analytics.email_click_rate}% untuk email tracking.` },
      { label: 'Breakdown', copy: `${sourceBreakdown.value.length} source berbeda sudah masuk ke analisis.` },
    ],
  }

  return map[activeTab.value] ?? map.overview
})

const analyticsCardsLeft = computed(() => [
  { label: 'Traffic Sessions', value: formatNumber(props.summary.analytics.traffic_sessions) },
  { label: 'Total Leads', value: formatNumber(props.summary.overview.total_leads) },
  { label: 'Meta Spend', value: formatMoney(props.summary.analytics.meta_spend) },
  { label: 'TikTok Spend', value: formatMoney(props.summary.analytics.tiktok_spend) },
])

const analyticsCardsRight = computed(() => [
  { label: 'Email Open', value: `${props.summary.analytics.email_open_rate}%` },
  { label: 'Email Click', value: `${props.summary.analytics.email_click_rate}%` },
  { label: 'Avg ROI', value: `${props.summary.overview.avg_roi}%` },
  { label: 'Top Content', value: props.summary.overview.top_content || 'None' },
])

const activePanels = computed(() => {
  const panels = {
    overview: [
      { title: 'Komposisi sumber prospek', copy: 'Rincian sumber dipakai untuk membaca kanal mana yang paling banyak menyumbang prospek dan sesi.' },
      { title: 'Budget control', copy: 'Spend dan budget tampil berdampingan supaya keputusan scaling lebih cepat diambil.' },
      { title: 'ROI read', copy: 'ROI disimpan di campaign meta agar laporan performa bisa dihitung dari data nyata.' },
    ],
    social: [
      { title: 'Platform spread', copy: 'Distribusi platform membantu melihat konten mana yang cocok untuk Instagram, TikTok, atau LinkedIn.' },
      { title: 'Post analytics', copy: 'Reach, engagement, dan clicks ditarik langsung dari analytics tiap post.' },
      { title: 'Pipeline status', copy: 'Idea sampai posted dibaca sebagai alur kerja yang gampang dilacak.' },
    ],
    email: [
      { title: 'Audience hygiene', copy: 'Subscriber aktif dan nonaktif dipantau bersama supaya list tetap sehat.' },
      { title: 'Delivery rates', copy: 'Open, click, bounce, dan unsubscribe rate disimpan di newsletter metrics.' },
      { title: 'Scheduler', copy: 'Scheduled dan sent timestamp dipakai untuk baca lifecycle campaign email.' },
    ],
    analytics: [
      { title: 'Traffic lens', copy: 'Traffic sessions dan leads digabung untuk membaca kontribusi channel ke hasil akhir.' },
      { title: 'Channel spend', copy: 'Meta Ads dan TikTok Ads dibedakan agar spend tiap channel terlihat jelas.' },
      { title: 'Email performance', copy: 'Open rate dan click rate jadi pembanding saat campaign email dijalankan.' },
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
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
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
  if (!confirm('Delete this campaign?')) return
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
  if (!confirm('Delete this social post?')) return
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
  if (!confirm('Delete this newsletter?')) return
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
  if (!confirm('Delete this subscriber?')) return
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
