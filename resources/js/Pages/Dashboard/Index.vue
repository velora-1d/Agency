<template>
  <Head :title="`Dashboard ${workspace.name}`" />

  <WorkspaceLayout
    title="Operational Dashboard"
    :subtitle="`Pusat kendali harian untuk ${workspace.name}. Halaman ini hanya fokus pada radar operasional, revenue pulse, lead movement, dan alert kerja yang butuh perhatian cepat.`"
    :navigation="navigation"
    :workspace-name="workspace.name"
    :workspace-slug="workspace.slug"
  >
    <template #actions>
      <div class="flex flex-wrap gap-3">
        <div class="rounded-full border border-stone-200 bg-white px-4 py-2 text-xs uppercase tracking-[0.24em] text-stone-700">
          {{ dashboard.context.roleLabel }}
        </div>
        <div class="rounded-full border border-stone-200 bg-white px-4 py-2 text-xs uppercase tracking-[0.18em] text-stone-500">
          {{ dashboard.context.generatedAt }}
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.8fr)_minmax(340px,0.9fr)] animate-reveal">
        <article class="overflow-hidden rounded-4xl bg-[#191614] p-6 text-stone-50 shadow-[0_30px_80px_rgba(29,22,17,0.22)] transition-all duration-300 hover:shadow-[0_40px_100px_rgba(29,22,17,0.3)] hover:-translate-y-1">
          <div class="flex flex-col gap-8 lg:flex-row lg:justify-between">
            <div class="max-w-2xl space-y-4">
              <p class="text-xs uppercase tracking-[0.32em] text-amber-300/80">{{ dashboard.context.monthLabel }}</p>
              <h2 class="max-w-xl text-2xl font-semibold leading-tight tracking-tighter lg:text-3xl">
                Dashboard utama untuk memegang ritme operasional workspace.
              </h2>
              <p class="max-w-2xl text-sm leading-7 text-stone-300">
                Semua section di bawah ini dibangun mengikuti brief Section A Main Dashboard: KPI atas, empat chart utama, quick actions, category summary, recent activity, lalu row bawah untuk meeting, mini calendar, file terbaru, dan alerts.
              </p>
            </div>

            <div v-if="topAlerts.length" class="grid min-w-[280px] gap-3 self-start">
              <div
                v-for="alert in topAlerts"
                :key="alert.key"
                class="rounded-[1.4rem] border px-4 py-4"
                :class="alertCardClass(alert.tone)"
              >
                <p class="text-[11px] uppercase tracking-[0.24em]">{{ alert.label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]">{{ alert.value }}</p>
                <p class="mt-2 text-sm leading-6 opacity-80">{{ alert.description }}</p>
              </div>
            </div>

            <div v-else class="flex min-w-[280px] items-center self-start rounded-[1.4rem] border border-white/10 bg-white/5 px-4 py-4 text-sm leading-6 text-stone-200">
              Belum ada notifikasi prioritas tinggi untuk workspace ini.
            </div>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Focus Strip</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Radar Hari Ini</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-xs uppercase tracking-[0.22em] text-amber-200">
              {{ dashboard.context.timezone }}
            </div>
          </div>

          <div class="mt-6 space-y-4">
            <div class="rounded-3xl bg-stone-100 p-4">
              <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Workspace</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-stone-950">{{ workspace.name }}</p>
              <p class="mt-2 text-sm text-stone-600">
                Semua angka dan activity di bawah sudah terisolasi per workspace.
              </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <div class="rounded-3xl border border-stone-200 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Chart Active</p>
              <p class="mt-2 text-lg font-semibold text-stone-950">{{ visibleCharts.length }} panel</p>
              <p class="mt-1 text-sm text-stone-600">Disesuaikan otomatis dengan role saat ini.</p>
            </div>
              <div class="rounded-3xl border border-stone-200 p-4">
                <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Live Feed</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ dashboard.recentActivity.length }} aktivitas</p>
                <p class="mt-1 text-sm text-stone-600">Menangkap update pekerjaan terbaru dari workspace.</p>
              </div>
            </div>

            <div
              v-if="actionNotice"
              class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-900"
            >
              {{ actionNotice }}
            </div>
          </div>
        </article>
      </section>

      <FilterBar 
        class="animate-reveal [animation-delay:150ms]"
        :options="dashboard.filterOptions"
        :active-filters="dashboard.activeFilters"
        @change="handleFilterChange"
        @clear="handleFilterClear"
      />

      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 animate-reveal [animation-delay:300ms]">
        <article
          v-for="metric in visibleMetrics"
          :key="metric.key"
          class="rounded-[1.8rem] border border-stone-200 bg-white p-5 shadow-[0_18px_55px_rgba(77,58,35,0.08)] transition-all duration-300 hover:shadow-[0_25px_70px_rgba(77,58,35,0.15)] hover:-translate-y-1"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs uppercase tracking-[0.22em] text-stone-500">{{ metric.label }}</p>
              <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-stone-950">{{ metric.value }}</p>
            </div>
            <div class="rounded-full bg-stone-950 p-2.5 text-amber-200">
              <component :is="iconComponent(metric.icon)" class="w-5 h-5" />
            </div>
          </div>

          <div class="mt-4 flex items-center justify-between gap-3">
            <span class="rounded-full px-3 py-1 text-xs font-medium" :class="trendClass(metric.trend.tone)">
              {{ metric.trend.value }}
            </span>
            <span class="text-right text-xs uppercase tracking-[0.18em] text-stone-400">{{ trendWord(metric.trend.tone) }}</span>
          </div>

          <p class="mt-4 text-sm leading-6 text-stone-600">{{ metric.helper }}</p>
          <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-400">{{ metric.trend.label }}</p>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-2 animate-reveal [animation-delay:450ms]">
        <article
          v-if="isChartVisible('revenue')"
          class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1"
        >
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Chart / Revenue</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Revenue Pulse</h3>
              <p class="mt-2 text-sm leading-6 text-stone-600">Line chart revenue dengan toggle 7 hari sampai 1 tahun.</p>
            </div>

            <div class="flex flex-wrap gap-2">
              <button
                v-for="(label, key) in dashboard.charts.revenue.filters"
                :key="key"
                type="button"
                class="rounded-full px-4 py-2 text-xs uppercase tracking-[0.18em] transition"
                :class="revenueFilter === key ? 'bg-stone-950 text-amber-200' : 'bg-stone-100 text-stone-500 hover:bg-stone-200'"
                @click="revenueFilter = key"
              >
                {{ label }}
              </button>
            </div>
          </div>

          <div v-if="hasSeriesData(activeRevenue.values)" class="mt-8">
            <svg viewBox="0 0 100 36" class="h-[260px] w-full overflow-visible">
              <defs>
                <linearGradient id="revenue-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#c87b19" stop-opacity="0.35" />
                  <stop offset="100%" stop-color="#c87b19" stop-opacity="0.03" />
                </linearGradient>
              </defs>
              <polyline
                fill="none"
                stroke="#c87b19"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                :points="linePoints(activeRevenue.values)"
              />
              <polygon :points="areaPoints(activeRevenue.values)" fill="url(#revenue-fill)" />
              <circle
                v-for="(value, index) in activeRevenue.values"
                :key="`${revenueFilter}-${index}`"
                :cx="pointCoordinate(index, activeRevenue.values.length).x"
                :cy="pointCoordinate(index, activeRevenue.values.length, value, activeRevenue.values).y"
                r="1.4"
                fill="#1f1a17"
              />
            </svg>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
              <div class="rounded-[1.3rem] bg-stone-100 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Total</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ formatCurrency(sum(activeRevenue.values)) }}</p>
              </div>
              <div class="rounded-[1.3rem] bg-stone-100 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Puncak</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ formatCurrency(max(activeRevenue.values)) }}</p>
              </div>
              <div class="rounded-[1.3rem] bg-stone-100 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Rata-rata</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ formatCurrency(average(activeRevenue.values)) }}</p>
              </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2 text-xs text-stone-500 sm:grid-cols-4">
              <div
                v-for="(label, index) in activeRevenue.labels.slice(-8)"
                :key="`${revenueFilter}-label-${index}`"
                class="truncate rounded-full bg-stone-100 px-3 py-2 text-center uppercase tracking-[0.14em]"
              >
                {{ label }}
              </div>
            </div>
          </div>

          <div v-else class="mt-8 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 p-6 text-sm leading-6 text-stone-500">
            Belum ada invoice paid pada rentang yang dipilih, jadi chart revenue belum menampilkan pergerakan data.
          </div>
        </article>

        <article
          v-if="isChartVisible('leadsConversion')"
          class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1"
        >
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Chart / CRM</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Lead Conversion</h3>
              <p class="mt-2 text-sm leading-6 text-stone-600">Distribusi lead per stage untuk membaca pipeline pressure.</p>
            </div>

            <div class="flex flex-wrap gap-2">
              <button
                v-for="(label, key) in dashboard.charts.leadsConversion.filters"
                :key="key"
                type="button"
                class="rounded-full px-4 py-2 text-xs uppercase tracking-[0.18em] transition"
                :class="leadsFilter === key ? 'bg-stone-950 text-amber-200' : 'bg-stone-100 text-stone-500 hover:bg-stone-200'"
                @click="leadsFilter = key"
              >
                {{ label }}
              </button>
            </div>
          </div>

          <div v-if="hasComparisonData(activeLeads.labels, activeLeads.values)" class="mt-8 grid gap-4">
            <div
              v-for="(label, index) in activeLeads.labels"
              :key="`${leadsFilter}-${label}`"
              class="grid grid-cols-[120px_minmax(0,1fr)_60px] items-center gap-3"
            >
              <p class="truncate text-sm font-medium text-stone-700">{{ label }}</p>
              <div class="h-4 overflow-hidden rounded-full bg-stone-100">
                <div
                  class="h-full rounded-full"
                  :style="{
                    width: `${barWidth(activeLeads.values[index], activeLeads.values)}%`,
                    backgroundColor: activeLeads.colors[index] || '#b88123',
                  }"
                />
              </div>
              <p class="text-right text-sm font-semibold text-stone-950">{{ activeLeads.values[index] }}</p>
            </div>
          </div>

          <div v-else class="mt-8 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 p-6 text-sm leading-6 text-stone-500">
            Belum ada distribusi lead yang cukup untuk dibaca pada periode ini.
          </div>
        </article>

        <article
          v-if="isChartVisible('projectProgress')"
          class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1"
        >
          <div>
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Chart / Project</p>
            <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Project Progress</h3>
            <p class="mt-2 text-sm leading-6 text-stone-600">Top 5 project aktif dengan visual warna berdasar progress.</p>
          </div>

          <div v-if="dashboard.charts.projectProgress.labels.length" class="mt-8 grid gap-4">
            <div
              v-for="(label, index) in dashboard.charts.projectProgress.labels"
              :key="label"
              class="rounded-3xl bg-stone-100 px-4 py-4"
            >
              <div class="flex items-center justify-between gap-3">
                <p class="truncate text-sm font-medium text-stone-700">{{ label }}</p>
                <p class="text-sm font-semibold text-stone-950">{{ dashboard.charts.projectProgress.values[index] }}%</p>
              </div>

              <div class="mt-4 h-3 overflow-hidden rounded-full bg-white">
                <div
                  class="h-full rounded-full"
                  :style="{
                    width: `${dashboard.charts.projectProgress.values[index]}%`,
                    backgroundColor: dashboard.charts.projectProgress.colors[index],
                  }"
                />
              </div>
            </div>
          </div>

          <div v-else class="mt-8 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 p-6 text-sm leading-6 text-stone-500">
            Belum ada proyek aktif yang bisa ditampilkan pada progress board.
          </div>
        </article>

        <article
          v-if="isChartVisible('monthlyGrowth')"
          class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1"
        >
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Chart / Growth</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Monthly Growth</h3>
              <p class="mt-2 text-sm leading-6 text-stone-600">Twelve-month area chart untuk revenue, lead, dan project intake.</p>
            </div>

            <div class="flex flex-wrap gap-2">
              <button
                v-for="(label, key) in dashboard.charts.monthlyGrowth.filters"
                :key="key"
                type="button"
                class="rounded-full px-4 py-2 text-xs uppercase tracking-[0.18em] transition"
                :class="growthFilter === key ? 'bg-stone-950 text-amber-200' : 'bg-stone-100 text-stone-500 hover:bg-stone-200'"
                @click="growthFilter = key"
              >
                {{ label }}
              </button>
            </div>
          </div>

          <div v-if="hasSeriesData(activeGrowthSeries)" class="mt-8">
            <svg viewBox="0 0 100 36" class="h-[260px] w-full overflow-visible">
              <defs>
                <linearGradient id="growth-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" :stop-color="growthPalette.stroke" stop-opacity="0.36" />
                  <stop offset="100%" :stop-color="growthPalette.stroke" stop-opacity="0.04" />
                </linearGradient>
              </defs>
              <polyline
                fill="none"
                :stroke="growthPalette.stroke"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                :points="linePoints(activeGrowthSeries)"
              />
              <polygon :points="areaPoints(activeGrowthSeries)" fill="url(#growth-fill)" />
            </svg>

            <div class="mt-5 grid grid-cols-2 gap-2 text-xs text-stone-500 lg:grid-cols-4">
              <div
                v-for="(label, index) in dashboard.charts.monthlyGrowth.labels.slice(-8)"
                :key="`growth-${index}`"
                class="truncate rounded-full bg-stone-100 px-3 py-2 text-center uppercase tracking-[0.14em]"
              >
                {{ label }}
              </div>
            </div>
          </div>

          <div v-else class="mt-8 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 p-6 text-sm leading-6 text-stone-500">
            Belum ada histori cukup untuk membaca pertumbuhan bulanan pada mode ini.
          </div>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] animate-reveal [animation-delay:600ms]">
        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Quick Actions</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Aksi cepat dashboard</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-xs uppercase tracking-[0.18em] text-amber-200">5 aksi</div>
          </div>

          <div class="mt-8 grid gap-4 md:grid-cols-2">
            <button
              v-for="action in dashboard.quickActions"
              :key="action.key"
              type="button"
              class="group rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 text-left transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-white"
              @click="announceAction(action)"
            >
              <div class="flex items-center justify-between gap-4">
                <div class="rounded-full bg-stone-950 p-2 text-amber-200">
                  <component :is="iconComponent(action.icon)" class="w-4 h-4" />
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-[10px] uppercase tracking-[0.22em] text-stone-500">
                  dashboard
                </span>
              </div>
              <h4 class="mt-5 text-xl font-semibold tracking-[-0.03em] text-stone-950">{{ action.label }}</h4>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ action.description }}</p>
            </button>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Category Summary</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Ringkasan kategori</h3>
            </div>
            <div class="rounded-full bg-stone-100 px-4 py-2 text-xs uppercase tracking-[0.18em] text-stone-500">{{ visibleCategories.length }} blok</div>
          </div>

          <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <article
              v-for="item in visibleCategories"
              :key="item.key"
              class="rounded-[1.6rem] bg-stone-100 p-5"
            >
              <p class="text-xs uppercase tracking-[0.22em] text-stone-500">{{ item.title }}</p>
              <p class="mt-4 text-lg font-semibold tracking-[-0.03em] text-stone-950">{{ item.lineOne }}</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ item.lineTwo }}</p>
            </article>
          </div>
        </article>
      </section>

      <section class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] animate-reveal [animation-delay:750ms] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Recent Activity</p>
            <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Feed operasional terbaru</h3>
          </div>
          <p class="text-sm text-stone-500">10 aktivitas terbaru dalam workspace aktif.</p>
        </div>

        <div v-if="dashboard.recentActivity.length" class="mt-8 grid gap-4">
          <article
            v-for="activity in dashboard.recentActivity"
            :key="activity.id"
            class="grid gap-4 rounded-[1.6rem] border border-stone-200 p-5 lg:grid-cols-[80px_minmax(0,1fr)_180px]"
          >
            <div class="flex items-start">
              <div class="rounded-full p-2.5" :class="trendClass(activity.tone)">
                <component :is="iconComponent(activity.icon)" class="w-5 h-5" />
              </div>
            </div>

            <div>
              <p class="text-lg font-semibold tracking-[-0.03em] text-stone-950">{{ activity.title }}</p>
              <p class="mt-2 text-sm leading-6 text-stone-600">{{ activity.description }}</p>
            </div>

            <div class="space-y-2 text-sm text-stone-500 lg:text-right">
              <p class="font-medium text-stone-700">{{ activity.user }}</p>
              <p>{{ activity.when }}</p>
            </div>
          </article>
        </div>

        <div v-else class="mt-8 rounded-[1.6rem] border border-dashed border-stone-200 bg-stone-50 p-6 text-sm leading-6 text-stone-500">
          Belum ada aktivitas terbaru yang tercatat pada workspace ini.
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-5 animate-reveal [animation-delay:900ms]">
        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Upcoming Meetings</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Jadwal mendatang</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-xs uppercase tracking-[0.18em] text-amber-200">
              {{ dashboard.upcomingMeetings.length }}
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <article
              v-for="meeting in dashboard.upcomingMeetings"
              :key="meeting.id"
              class="rounded-3xl bg-stone-100 p-4"
            >
              <div class="flex items-center justify-between gap-4">
                <p class="text-sm font-semibold text-stone-950">{{ meeting.title }}</p>
                <span class="rounded-full px-3 py-1 text-[10px] uppercase tracking-[0.22em]" :class="trendClass(meeting.badgeTone)">
                  {{ meeting.badge }}
                </span>
              </div>
              <p class="mt-2 text-sm text-stone-600">{{ meeting.when }}</p>
              <p class="mt-2 text-sm text-stone-500">{{ meeting.project }} / {{ meeting.participants }}</p>
            </article>

            <div v-if="dashboard.upcomingMeetings.length === 0" class="rounded-3xl border border-dashed border-stone-200 p-4 text-sm text-stone-500">
              Belum ada meeting terjadwal untuk workspace ini.
            </div>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] xl:col-span-2 transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Mini Calendar</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">{{ dashboard.calendar.label }}</h3>
            </div>
            <p class="text-sm text-stone-500">Klik tanggal untuk melihat event di hari tersebut.</p>
          </div>

          <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
            <div>
              <div class="grid grid-cols-7 gap-2 text-center text-[11px] uppercase tracking-[0.22em] text-stone-400">
                <div v-for="name in weekDays" :key="name" class="py-2">{{ name }}</div>
              </div>

              <div class="mt-3 grid grid-cols-7 gap-2">
                <button
                  v-for="day in calendarDays"
                  :key="day.date"
                  type="button"
                  class="flex aspect-square flex-col items-center justify-center rounded-[1.2rem] border text-sm transition"
                  :class="calendarDayClass(day)"
                  @click="selectedDate = day.date"
                >
                  <span>{{ day.label }}</span>
                  <span
                    v-if="day.count > 0"
                    class="mt-2 inline-flex h-2.5 w-2.5 rounded-full"
                    :style="{ backgroundColor: day.color }"
                  />
                </button>
              </div>
            </div>

            <div class="rounded-[1.7rem] bg-stone-100 p-5">
              <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Events pada {{ selectedDate }}</p>
              <div class="mt-4 space-y-3">
                <article
                  v-for="event in selectedEvents"
                  :key="event.id"
                  class="rounded-[1.2rem] bg-white p-4"
                >
                  <div class="flex items-center gap-3">
                    <span class="inline-flex h-3 w-3 rounded-full" :style="{ backgroundColor: event.color }" />
                    <p class="text-sm font-semibold text-stone-950">{{ event.title }}</p>
                  </div>
                  <p class="mt-2 text-sm text-stone-600">{{ event.time }}</p>
                  <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-400">{{ event.type }}</p>
                </article>

                <div v-if="selectedEvents.length === 0" class="rounded-[1.2rem] border border-dashed border-stone-200 p-4 text-sm text-stone-500">
                  Tidak ada event pada tanggal terpilih.
                </div>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Recent Files</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Upload terbaru</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-xs uppercase tracking-[0.18em] text-amber-200">
              {{ dashboard.recentFiles.length }}
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <article
              v-for="file in dashboard.recentFiles"
              :key="file.id"
              class="rounded-3xl bg-stone-100 p-4"
            >
              <div class="flex items-center justify-between gap-4">
                <p class="truncate text-sm font-semibold text-stone-950">{{ file.name }}</p>
                <span class="rounded-full px-3 py-1 text-[10px] uppercase tracking-[0.22em]" :class="trendClass(file.tone)">
                  {{ file.typeLabel }}
                </span>
              </div>
              <p class="mt-2 text-sm text-stone-600">{{ file.project }}</p>
              <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-400">{{ file.size }} / {{ file.when }}</p>
            </article>

            <div v-if="dashboard.recentFiles.length === 0" class="rounded-3xl border border-dashed border-stone-200 p-4 text-sm text-stone-500">
              Belum ada file yang diunggah pada workspace ini.
            </div>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Notifications</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Radar notifikasi</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-xs uppercase tracking-[0.18em] text-amber-200">
              {{ dashboard.alerts.length }}
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <article
              v-for="alert in dashboard.alerts"
              :key="alert.key"
              class="rounded-3xl border p-4"
              :class="notificationCardClass(alert.tone)"
            >
              <p class="text-[11px] uppercase tracking-[0.22em]">{{ alert.label }}</p>
              <p class="mt-2 text-lg font-semibold tracking-[-0.03em]">{{ alert.value }}</p>
              <p class="mt-2 text-sm leading-6 opacity-85">{{ alert.description }}</p>
            </article>

            <div v-if="dashboard.alerts.length === 0" class="rounded-3xl border border-dashed border-stone-200 p-4 text-sm text-stone-500">
              Tidak ada notifikasi operasional yang perlu dinaikkan saat ini.
            </div>
          </div>
        </article>
      </section>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, markRaw } from 'vue'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import FilterBar from '../../Components/domain/dashboard/FilterBar.vue'
import { 
  Banknote, 
  Building2, 
  Briefcase, 
  Clock, 
  Receipt, 
  Sparkles, 
  Activity, 
  Filter, 
  UserPlus, 
  FolderPlus, 
  FilePlus, 
  Zap, 
  Megaphone, 
  Check, 
  Users,
  HelpCircle
} from 'lucide-vue-next'

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  navigation: {
    type: Array,
    required: true,
  },
  dashboard: {
    type: Object,
    required: true,
  },
})

const revenueFilter = ref(props.dashboard.charts.revenue?.default ?? '30d')
const leadsFilter = ref(props.dashboard.charts.leadsConversion?.default ?? '30d')
const growthFilter = ref(props.dashboard.charts.monthlyGrowth?.default ?? 'revenue')
const selectedDate = ref(props.dashboard.calendar.today)
const actionNotice = ref('')

function handleFilterChange(filters) {
  router.get(route('workspace.dashboard', props.workspace.slug), filters, {
    preserveState: true,
    preserveScroll: true,
    only: ['dashboard'],
  })
}

function handleFilterClear() {
  router.get(route('workspace.dashboard', props.workspace.slug), {}, {
    preserveState: true,
    preserveScroll: true,
    only: ['dashboard'],
  })
}

const weekDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']

const topAlerts = computed(() => props.dashboard.alerts.slice(0, 3))

const visibleMetrics = computed(() =>
  props.dashboard.metrics.filter((metric) => props.dashboard.visibility.metrics.includes(metric.key)),
)

const visibleCategories = computed(() =>
  props.dashboard.categorySummary.filter((item) => props.dashboard.visibility.categories.includes(item.key)),
)

const visibleCharts = computed(() => props.dashboard.visibility.charts)

const activeRevenue = computed(() => props.dashboard.charts.revenue?.series?.[revenueFilter.value] ?? { labels: [], values: [] })
const activeLeads = computed(() => props.dashboard.charts.leadsConversion?.series?.[leadsFilter.value] ?? { labels: [], values: [], colors: [] })
const activeGrowthSeries = computed(() => props.dashboard.charts.monthlyGrowth?.series?.[growthFilter.value] ?? [])

const growthPalette = computed(() => {
  if (growthFilter.value === 'leads') {
    return { stroke: '#295ea8' }
  }

  if (growthFilter.value === 'projects') {
    return { stroke: '#bb5c21' }
  }

  return { stroke: '#1c8c63' }
})

const selectedEvents = computed(() => props.dashboard.calendar.eventsByDate[selectedDate.value] ?? [])

const calendarDays = computed(() => {
  const days = []
  const totalSlots = 42
  const leading = props.dashboard.calendar.startWeekday - 1
  const previousMonthDays = new Date(props.dashboard.calendar.year, props.dashboard.calendar.month - 1, 0).getDate()

  for (let index = 0; index < totalSlots; index += 1) {
    const dayNumber = index - leading + 1
    const inMonth = dayNumber > 0 && dayNumber <= props.dashboard.calendar.daysInMonth

    let year = props.dashboard.calendar.year
    let month = props.dashboard.calendar.month
    let label = dayNumber

    if (!inMonth && dayNumber <= 0) {
      month -= 1
      if (month === 0) {
        month = 12
        year -= 1
      }
      label = previousMonthDays + dayNumber
    }

    if (!inMonth && dayNumber > props.dashboard.calendar.daysInMonth) {
      month += 1
      if (month === 13) {
        month = 1
        year += 1
      }
      label = dayNumber - props.dashboard.calendar.daysInMonth
    }

    const date = `${year}-${String(month).padStart(2, '0')}-${String(label).padStart(2, '0')}`
    const events = props.dashboard.calendar.eventsByDate[date] ?? []

    days.push({
      date,
      label,
      count: events.length,
      color: events[0]?.color ?? '#c6811a',
      inMonth,
      isToday: date === props.dashboard.calendar.today,
      isSelected: date === selectedDate.value,
    })
  }

  return days
})

function isChartVisible(key) {
  return visibleCharts.value.includes(key) && Boolean(props.dashboard.charts[key])
}

function iconComponent(key) {
  const map = {
    banknotes: Banknote,
    building: Building2,
    briefcase: Briefcase,
    clock: Clock,
    receipt: Receipt,
    spark: Sparkles,
    pulse: Activity,
    funnel: Filter,
    'user-plus': UserPlus,
    'folder-plus': FolderPlus,
    'file-plus': FilePlus,
    zap: Zap,
    megaphone: Megaphone,
    check: Check,
    users: Users,
  }

  return map[key] || HelpCircle
}

function trendClass(tone) {
  return {
    positive: 'bg-emerald-100 text-emerald-800',
    critical: 'bg-rose-100 text-rose-800',
    warning: 'bg-amber-100 text-amber-900',
    info: 'bg-sky-100 text-sky-800',
    neutral: 'bg-stone-200 text-stone-700',
  }[tone] ?? 'bg-stone-200 text-stone-700'
}

function trendWord(tone) {
  return {
    positive: 'sehat',
    critical: 'perhatian',
    warning: 'pantau',
    info: 'info',
    neutral: 'stabil',
  }[tone] ?? 'stabil'
}

function alertCardClass(tone) {
  return {
    positive: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-50',
    critical: 'border-rose-300/30 bg-rose-400/10 text-rose-50',
    warning: 'border-amber-300/30 bg-amber-300/10 text-amber-50',
    info: 'border-sky-300/30 bg-sky-300/10 text-sky-50',
    neutral: 'border-white/10 bg-white/5 text-stone-100',
  }[tone] ?? 'border-white/10 bg-white/5 text-stone-100'
}

function notificationCardClass(tone) {
  return {
    positive: 'border-emerald-200 bg-emerald-50 text-emerald-950',
    critical: 'border-rose-200 bg-rose-50 text-rose-950',
    warning: 'border-amber-200 bg-amber-50 text-amber-950',
    info: 'border-sky-200 bg-sky-50 text-sky-950',
    neutral: 'border-stone-200 bg-stone-50 text-stone-900',
  }[tone] ?? 'border-stone-200 bg-stone-50 text-stone-900'
}

function announceAction(action) {
  actionNotice.value = `${action.label} disiapkan dari dashboard. Implementasi detail form dan flow-nya akan dibangun saat menu terkait dikerjakan, tapi entry point-nya tetap dikunci dari dashboard ini.`
}

function max(values) {
  return Math.max(...values, 0)
}

function sum(values) {
  return values.reduce((total, value) => total + value, 0)
}

function average(values) {
  return values.length ? Math.round(sum(values) / values.length) : 0
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: props.dashboard.context.currency,
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function hasSeriesData(values) {
  return values.some((value) => value > 0)
}

function hasComparisonData(labels, values) {
  return labels.length > 0 && values.some((value) => value > 0)
}

function barWidth(value, values) {
  const biggest = Math.max(...values, 1)

  return (value / biggest) * 100
}

function pointCoordinate(index, length, value = 0, values = []) {
  const x = length <= 1 ? 50 : 4 + (index * 92) / (length - 1)
  const ceiling = Math.max(...values, 1)
  const y = 30 - (value / ceiling) * 24

  return { x, y }
}

function linePoints(values) {
  return values
    .map((value, index) => {
      const point = pointCoordinate(index, values.length, value, values)

      return `${point.x},${point.y}`
    })
    .join(' ')
}

function areaPoints(values) {
  if (!values.length) {
    return ''
  }

  const line = linePoints(values)
  const first = pointCoordinate(0, values.length)
  const last = pointCoordinate(values.length - 1, values.length)

  return `${first.x},30 ${line} ${last.x},30`
}

function calendarDayClass(day) {
  if (day.isSelected) {
    return 'border-stone-950 bg-stone-950 text-amber-200'
  }

  if (day.isToday) {
    return 'border-amber-300 bg-amber-100 text-stone-950'
  }

  if (!day.inMonth) {
    return 'border-transparent bg-stone-100 text-stone-400'
  }

  if (day.count > 0) {
    return 'border-amber-200 bg-amber-50 text-stone-950'
  }

  return 'border-stone-200 bg-white text-stone-700 hover:bg-stone-100'
}
</script>
