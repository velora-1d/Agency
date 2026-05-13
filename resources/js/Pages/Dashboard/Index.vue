<template>
  <Head :title="`Dasbor ${workspace.name}`" />

  <WorkspaceLayout
    title="Dasbor Operasional"
    :subtitle="`Pusat kendali harian untuk ${workspace.name}. Radar operasional, arus pendapatan, dan pergerakan prospek dalam satu layar.`"
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

    <div class="space-y-6 pb-12">
      <!-- SECTION 1: RADAR SIGNALS (TOP HERO) -->
      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.8fr)_minmax(340px,0.9fr)] animate-reveal">
        <article class="overflow-hidden rounded-4xl bg-[#191614] p-6 text-stone-50 shadow-[0_30px_80px_rgba(29,22,17,0.22)] transition-all duration-300 hover:shadow-[0_40px_100px_rgba(29,22,17,0.3)] hover:-translate-y-1">
          <div class="flex flex-col gap-8 lg:flex-row lg:justify-between">
            <div class="max-w-2xl space-y-4">
              <p class="text-xs uppercase tracking-[0.32em] text-amber-300/80">{{ dashboard.context.monthLabel }}</p>
              <h2 class="max-w-xl text-3xl font-semibold leading-tight tracking-tighter lg:text-4xl">
                Sinyal operasional hari ini.
              </h2>
              <p class="max-w-xl text-base leading-7 text-stone-300">
                Pantau anomali, peringatan sistem, dan peristiwa kritis yang membutuhkan respons cepat dari tim.
              </p>
            </div>

            <div v-if="topAlerts.length" class="grid min-w-[300px] gap-3 self-start">
              <div
                v-for="alert in topAlerts"
                :key="alert.key"
                class="rounded-[1.4rem] border px-5 py-5 transition-all hover:scale-[1.02]"
                :class="alertCardClass(alert.tone)"
              >
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-[0.24em]">{{ alert.label }}</p>
                    <div class="h-1.5 w-1.5 rounded-full animate-pulse bg-current"></div>
                </div>
                <p class="mt-2 text-3xl font-semibold tracking-[-0.04em]">{{ alert.value }}</p>
                <p class="mt-2 text-sm leading-relaxed opacity-80">{{ alert.description }}</p>
              </div>
            </div>

            <div v-else class="flex min-w-[300px] items-center self-start rounded-[1.4rem] border border-white/10 bg-white/5 px-6 py-8 text-center text-sm leading-7 text-stone-300">
              Belum ada notifikasi prioritas tinggi yang terdeteksi radar saat ini.
            </div>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(60,42,24,0.08)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.12)] hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Konteks Radar</p>
              <h3 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Fokus Sekarang</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.22em] text-amber-200 shadow-lg shadow-amber-900/10">
              {{ dashboard.context.timezone }}
            </div>
          </div>

          <div class="mt-6 space-y-4">
            <div class="rounded-3xl bg-stone-100 p-5">
              <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-stone-400">Workspace Aktif</p>
              <p class="mt-2 text-2xl font-semibold tracking-[-0.03em] text-stone-950">{{ workspace.name }}</p>
              <p class="mt-2 text-sm text-stone-600">
                Seluruh metrik diisolasi hanya untuk entitas ini.
              </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <div class="rounded-3xl border border-stone-200 p-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-stone-400">Panel Grafik</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ visibleCharts.length }} Aktif</p>
                <p class="mt-1 text-[11px] text-stone-500">Disesuaikan dengan peran Anda.</p>
              </div>
              <div class="rounded-3xl border border-stone-200 p-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-stone-400">Umpan Langsung</p>
                <p class="mt-2 text-lg font-semibold text-stone-950">{{ dashboard.recentActivity.length }} Item</p>
                <p class="mt-1 text-[11px] text-stone-500">Menangkap perubahan terbaru.</p>
              </div>
            </div>

            <div
              v-if="actionNotice"
              class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-900 shadow-sm"
            >
              <div class="flex gap-3">
                <Zap class="h-4 w-4 shrink-0 mt-1" />
                <p>{{ actionNotice }}</p>
              </div>
            </div>
          </div>
        </article>
      </section>

      <!-- SECTION 2: TOP KPI CARDS -->
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 animate-reveal [animation-delay:150ms]">
        <article
          v-for="metric in visibleMetrics"
          :key="metric.key"
          class="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-[0_18px_55px_rgba(77,58,35,0.06)] transition-all duration-300 hover:shadow-[0_25px_70px_rgba(77,58,35,0.12)] hover:-translate-y-1"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-stone-400">{{ metric.label }}</p>
              <p class="mt-3 text-3xl font-semibold tracking-[-0.04em] text-stone-950">{{ metric.value }}</p>
            </div>
            <div class="rounded-2xl bg-stone-950 p-2.5 text-amber-200 shadow-lg shadow-stone-900/20">
              <component :is="iconComponent(metric.icon)" class="w-5 h-5" />
            </div>
          </div>

          <div class="mt-6 flex items-center justify-between gap-3">
            <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.1em]" :class="trendClass(metric.trend.tone)">
              {{ metric.trend.value }}
            </span>
            <span class="text-right text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ trendWord(metric.trend.tone) }}</span>
          </div>

          <div class="mt-6 border-t border-stone-50 pt-4">
            <p class="text-sm leading-relaxed text-stone-600">{{ metric.helper }}</p>
            <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ metric.trend.label }}</p>
          </div>
        </article>
      </section>

      <!-- SECTION 3: MAIN CHARTS -->
      <section class="grid gap-6 xl:grid-cols-2 animate-reveal [animation-delay:300ms]">
        <!-- CHART: REVENUE -->
        <article
          v-if="isChartVisible('revenue')"
          class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1"
        >
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Arus Pendapatan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Denyut Pendapatan</h3>
              <p class="mt-2 text-sm leading-relaxed text-stone-500 italic">Visualisasi performa penagihan dan pendapatan masuk.</p>
            </div>

            <div class="flex flex-wrap gap-1.5 rounded-2xl bg-stone-100 p-1.5">
              <button
                v-for="(label, key) in dashboard.charts.revenue.filters"
                :key="key"
                type="button"
                class="rounded-xl px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] transition-all"
                :class="revenueFilter === key ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-500 hover:text-stone-900'"
                @click="revenueFilter = key"
              >
                {{ translateTimeLabel(label) }}
              </button>
            </div>
          </div>

          <div v-if="hasSeriesData(activeRevenue.values)" class="mt-10">
            <div class="relative">
                <svg viewBox="0 0 100 36" class="h-[280px] w-full overflow-visible">
                <defs>
                    <linearGradient id="revenue-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#c87b19" stop-opacity="0.35" />
                    <stop offset="100%" stop-color="#c87b19" stop-opacity="0.01" />
                    </linearGradient>
                </defs>
                <polyline
                    fill="none"
                    stroke="#c87b19"
                    stroke-width="2"
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
                    r="1.6"
                    fill="#1f1a17"
                    stroke="#fff"
                    stroke-width="0.5"
                />
                </svg>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
              <div class="rounded-3xl border border-stone-100 bg-stone-50 px-5 py-5 shadow-inner">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Total Akumulasi</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ formatCurrency(sum(activeRevenue.values)) }}</p>
              </div>
              <div class="rounded-3xl border border-stone-100 bg-stone-50 px-5 py-5 shadow-inner">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Titik Puncak</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ formatCurrency(max(activeRevenue.values)) }}</p>
              </div>
              <div class="rounded-3xl border border-stone-100 bg-stone-50 px-5 py-5 shadow-inner">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">Rata-Rata</p>
                <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ formatCurrency(average(activeRevenue.values)) }}</p>
              </div>
            </div>
          </div>

          <div v-else class="mt-10 rounded-[2rem] border-2 border-dashed border-stone-100 bg-stone-50/50 p-10 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-stone-300 shadow-sm mb-4">
                <Banknote class="h-6 w-6" />
            </div>
            <p class="text-sm font-medium text-stone-500">Belum ada data pendapatan tercatat pada periode ini.</p>
          </div>
        </article>

        <!-- CHART: LEADS CONVERSION -->
        <article
          v-if="isChartVisible('leadsConversion')"
          class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1"
        >
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Corong Penjualan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Konversi Prospek</h3>
              <p class="mt-2 text-sm leading-relaxed text-stone-500 italic">Distribusi prospek berdasarkan tahap alur kerja saat ini.</p>
            </div>

            <div class="flex flex-wrap gap-1.5 rounded-2xl bg-stone-100 p-1.5">
              <button
                v-for="(label, key) in dashboard.charts.leadsConversion.filters"
                :key="key"
                type="button"
                class="rounded-xl px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] transition-all"
                :class="leadsFilter === key ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-500 hover:text-stone-900'"
                @click="leadsFilter = key"
              >
                {{ translateTimeLabel(label) }}
              </button>
            </div>
          </div>

          <div v-if="hasComparisonData(activeLeads.labels, activeLeads.values)" class="mt-10 space-y-5">
            <div
              v-for="(label, index) in activeLeads.labels"
              :key="`${leadsFilter}-${label}`"
              class="grid grid-cols-[140px_minmax(0,1fr)_80px] items-center gap-6"
            >
              <p class="truncate text-xs font-bold uppercase tracking-[0.12em] text-stone-500">{{ label }}</p>
              <div class="h-5 overflow-hidden rounded-2xl bg-stone-50 shadow-inner">
                <div
                  class="h-full rounded-2xl transition-all duration-1000 ease-out"
                  :style="{
                    width: `${barWidth(activeLeads.values[index], activeLeads.values)}%`,
                    backgroundColor: activeLeads.colors[index] || '#b88123',
                  }"
                />
              </div>
              <p class="text-right text-lg font-bold text-stone-950">{{ activeLeads.values[index] }}</p>
            </div>
          </div>

          <div v-else class="mt-10 rounded-[2rem] border-2 border-dashed border-stone-100 bg-stone-50/50 p-10 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-stone-300 shadow-sm mb-4">
                <Filter class="h-6 w-6" />
            </div>
            <p class="text-sm font-medium text-stone-500">Distribusi prospek tidak tersedia untuk periode ini.</p>
          </div>
        </article>

        <!-- CHART: PROJECT PROGRESS -->
        <article
          v-if="isChartVisible('projectProgress')"
          class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1"
        >
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Manajemen Proyek</p>
            <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Kemajuan Proyek</h3>
            <p class="mt-2 text-sm leading-relaxed text-stone-500 italic">5 proyek teratas dengan aktivitas paling tinggi.</p>
          </div>

          <div v-if="dashboard.charts.projectProgress.labels.length" class="mt-10 space-y-5">
            <div
              v-for="(label, index) in dashboard.charts.projectProgress.labels"
              :key="label"
              class="rounded-[1.6rem] border border-stone-100 bg-stone-50/40 p-5 shadow-sm transition-all hover:bg-stone-50"
            >
              <div class="flex items-center justify-between gap-4">
                <p class="truncate text-sm font-bold text-stone-800">{{ label }}</p>
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-stone-950">{{ dashboard.charts.projectProgress.values[index] }}%</span>
                    <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: dashboard.charts.projectProgress.colors[index] }"></div>
                </div>
              </div>

              <div class="mt-4 h-3 overflow-hidden rounded-full bg-white shadow-inner">
                <div
                  class="h-full rounded-full transition-all duration-1000 ease-out"
                  :style="{
                    width: `${dashboard.charts.projectProgress.values[index]}%`,
                    backgroundColor: dashboard.charts.projectProgress.colors[index],
                  }"
                />
              </div>
            </div>
          </div>

          <div v-else class="mt-10 rounded-[2rem] border-2 border-dashed border-stone-100 bg-stone-50/50 p-10 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-stone-300 shadow-sm mb-4">
                <Briefcase class="h-6 w-6" />
            </div>
            <p class="text-sm font-medium text-stone-500">Tidak ada proyek aktif yang sedang berjalan.</p>
          </div>
        </article>

        <!-- CHART: MONTHLY GROWTH -->
        <article
          v-if="isChartVisible('monthlyGrowth')"
          class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1"
        >
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kesehatan Bisnis</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Pertumbuhan Bulanan</h3>
              <p class="mt-2 text-sm leading-relaxed text-stone-500 italic">Tren pertumbuhan selama 12 bulan terakhir.</p>
            </div>

            <div class="flex flex-wrap gap-1.5 rounded-2xl bg-stone-100 p-1.5">
              <button
                v-for="(label, key) in dashboard.charts.monthlyGrowth.filters"
                :key="key"
                type="button"
                class="rounded-xl px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] transition-all"
                :class="growthFilter === key ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-500 hover:text-stone-900'"
                @click="growthFilter = key"
              >
                {{ translateTimeLabel(label) }}
              </button>
            </div>
          </div>

          <div v-if="hasSeriesData(activeGrowthSeries)" class="mt-10">
            <div class="relative">
                <svg viewBox="0 0 100 36" class="h-[280px] w-full overflow-visible">
                <defs>
                    <linearGradient id="growth-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" :stop-color="growthPalette.stroke" stop-opacity="0.36" />
                    <stop offset="100%" :stop-color="growthPalette.stroke" stop-opacity="0.02" />
                    </linearGradient>
                </defs>
                <polyline
                    fill="none"
                    :stroke="growthPalette.stroke"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    :points="linePoints(activeGrowthSeries)"
                />
                <polygon :points="areaPoints(activeGrowthSeries)" fill="url(#growth-fill)" />
                </svg>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
              <div
                v-for="(label, index) in dashboard.charts.monthlyGrowth.labels.slice(-12)"
                :key="`growth-${index}`"
                class="flex-1 min-w-[70px] rounded-xl border border-stone-100 bg-stone-50 px-2 py-3 text-center"
              >
                <p class="text-[9px] font-bold uppercase tracking-wider text-stone-400">{{ label.substring(0, 3) }}</p>
                <p class="mt-1 text-xs font-bold text-stone-800">{{ formatMetricValue(activeGrowthSeries[activeGrowthSeries.length - 12 + index]) }}</p>
              </div>
            </div>
          </div>

          <div v-else class="mt-10 rounded-[2rem] border-2 border-dashed border-stone-100 bg-stone-50/50 p-10 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-stone-300 shadow-sm mb-4">
                <Activity class="h-6 w-6" />
            </div>
            <p class="text-sm font-medium text-stone-500">Histori pertumbuhan tidak cukup untuk ditampilkan.</p>
          </div>
        </article>
      </section>

      <!-- SECTION 4: QUICK ACTIONS & CATEGORY SUMMARY -->
      <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] animate-reveal [animation-delay:450ms]">
        <article class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Efisiensi</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Aksi Cepat</h3>
            </div>
            <div class="rounded-full bg-stone-950 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-200 shadow-lg shadow-stone-900/20">
              Pusat Kendali
            </div>
          </div>

          <div class="mt-10 grid gap-4 md:grid-cols-2">
            <Link
              v-for="action in dashboard.quickActions"
              :key="action.key"
              :href="action.href"
              class="group relative overflow-hidden rounded-[1.8rem] border border-stone-200 bg-stone-50 p-6 text-left transition-all hover:-translate-y-1 hover:border-amber-400 hover:bg-white hover:shadow-xl hover:shadow-amber-900/5"
            >
              <div class="flex items-center justify-between gap-4 relative z-10">
                <div class="rounded-2xl bg-stone-950 p-3 text-amber-200 shadow-lg shadow-stone-900/30 group-hover:scale-110 transition-transform">
                  <component :is="iconComponent(action.icon)" class="w-5 h-5" />
                </div>
                <div class="rounded-full bg-white px-3 py-1 text-[9px] font-bold uppercase tracking-[0.22em] text-stone-400 shadow-sm">
                  instan
                </div>
              </div>
              <h4 class="mt-8 text-xl font-bold tracking-[-0.03em] text-stone-950 group-hover:text-amber-800 transition-colors">{{ action.label }}</h4>
              <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ action.description }}</p>
              
              <!-- Decorative background element -->
              <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <component :is="iconComponent(action.icon)" class="w-24 h-24 text-stone-900" />
              </div>
            </Link>
          </div>
        </article>

        <article class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Modul Kerja</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Ringkasan Kategori</h3>
            </div>
            <div class="rounded-full border border-stone-200 bg-white px-4 py-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">
              Lintas Sektor
            </div>
          </div>

          <div class="mt-10 grid gap-4 sm:grid-cols-2">
            <article
              v-for="item in visibleCategories"
              :key="item.key"
              class="group rounded-[1.8rem] bg-stone-100 p-6 transition-all hover:bg-stone-950 hover:text-white"
            >
              <div class="flex items-center gap-3">
                <div class="h-1.5 w-1.5 rounded-full bg-amber-500 group-hover:bg-amber-300"></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-stone-500 group-hover:text-stone-400">{{ item.title }}</p>
              </div>
              <p class="mt-6 text-xl font-bold tracking-[-0.03em]">{{ item.lineOne }}</p>
              <p class="mt-2 text-sm leading-relaxed text-stone-600 group-hover:text-stone-300">{{ item.lineTwo }}</p>
            </article>
          </div>
        </article>
      </section>

      <!-- SECTION 5: BOTTOM GRID (MEETINGS, CALENDAR, FILES, NOTIFS) -->
      <section class="grid gap-6 xl:grid-cols-4 animate-reveal [animation-delay:600ms]">
        <!-- MINI CALENDAR -->
        <article class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] xl:col-span-2 transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Penjadwalan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Kalender Mini</h3>
            </div>
            <div class="flex items-center gap-4 text-stone-500">
                <p class="text-[11px] font-bold uppercase tracking-widest">{{ dashboard.calendar.label }}</p>
            </div>
          </div>

          <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div>
              <div class="grid grid-cols-7 gap-3 text-center text-[10px] font-bold uppercase tracking-[0.22em] text-stone-400">
                <div v-for="name in weekDays" :key="name" class="py-2">{{ name }}</div>
              </div>

              <div class="mt-4 grid grid-cols-7 gap-3">
                <button
                  v-for="day in calendarDays"
                  :key="day.date"
                  type="button"
                  class="flex aspect-square flex-col items-center justify-center rounded-[1.3rem] border text-xs font-bold transition-all hover:scale-110"
                  :class="calendarDayClass(day)"
                  @click="selectedDate = day.date"
                >
                  <span>{{ day.label }}</span>
                  <span
                    v-if="day.count > 0"
                    class="mt-2 inline-flex h-2 w-2 rounded-full"
                    :style="{ backgroundColor: day.color }"
                  />
                </button>
              </div>
            </div>

            <div class="rounded-[2rem] bg-stone-50 p-6 shadow-inner border border-stone-100">
              <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-stone-400">Acara: {{ selectedDate }}</p>
              <div class="mt-6 space-y-4">
                <article
                  v-for="event in selectedEvents"
                  :key="event.id"
                  class="rounded-2xl border border-stone-100 bg-white p-4 shadow-sm transition-transform hover:-translate-x-1"
                >
                  <div class="flex items-center gap-3">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: event.color }" />
                    <p class="text-sm font-bold text-stone-950">{{ event.title }}</p>
                  </div>
                  <div class="mt-3 flex items-center justify-between">
                    <p class="text-[11px] font-medium text-stone-500 uppercase tracking-wider">{{ event.time }}</p>
                    <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-stone-400 bg-stone-50 px-2 py-1 rounded-lg">{{ event.type }}</p>
                  </div>
                </article>

                <div v-if="selectedEvents.length === 0" class="py-10 text-center">
                  <p class="text-xs font-medium text-stone-400 italic">Tidak ada agenda untuk hari ini.</p>
                </div>
              </div>
            </div>
          </div>
        </article>

        <!-- UPCOMING MEETINGS -->
        <article class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinkronisasi</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Rapat Mendatang</h3>
            </div>
            <div class="rounded-full bg-stone-950 h-8 w-8 flex items-center justify-center text-xs font-bold text-amber-200">
              {{ dashboard.upcomingMeetings.length }}
            </div>
          </div>

          <div class="mt-10 space-y-4">
            <article
              v-for="meeting in dashboard.upcomingMeetings"
              :key="meeting.id"
              class="group rounded-[1.8rem] border border-stone-100 bg-stone-50/50 p-5 transition-all hover:bg-white hover:border-amber-200 hover:shadow-md"
            >
              <div class="flex items-start justify-between gap-4">
                <p class="text-sm font-bold text-stone-950 group-hover:text-amber-900 transition-colors">{{ meeting.title }}</p>
                <span class="rounded-lg px-2 py-1 text-[9px] font-bold uppercase tracking-[0.22em] whitespace-nowrap" :class="trendClass(meeting.badgeTone)">
                  {{ meeting.badge }}
                </span>
              </div>
              <p class="mt-4 text-xs font-bold text-stone-500 flex items-center gap-2">
                <Clock class="h-3 w-3" />
                {{ meeting.when }}
              </p>
              <p class="mt-2 text-[10px] font-medium text-stone-400 border-t border-stone-100 pt-3">{{ meeting.project }} • {{ meeting.participants }}</p>
            </article>

            <div v-if="dashboard.upcomingMeetings.length === 0" class="rounded-[1.8rem] border border-dashed border-stone-200 p-10 text-center">
              <p class="text-xs font-medium text-stone-400 italic">Antrian rapat kosong.</p>
            </div>
          </div>
        </article>

        <!-- RECENT FILES -->
        <article class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Aset Digital</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Berkas Terbaru</h3>
            </div>
            <div class="rounded-full bg-stone-100 h-8 w-8 flex items-center justify-center text-xs font-bold text-stone-500">
              {{ dashboard.recentFiles.length }}
            </div>
          </div>

          <div class="mt-10 space-y-4">
            <article
              v-for="file in dashboard.recentFiles"
              :key="file.id"
              class="group rounded-[1.8rem] border border-stone-100 bg-stone-50/50 p-5 transition-all hover:bg-white hover:border-sky-200 hover:shadow-md"
            >
              <div class="flex items-center justify-between gap-4">
                <p class="truncate text-sm font-bold text-stone-950 group-hover:text-sky-900 transition-colors">{{ file.name }}</p>
                <span class="rounded-lg px-2 py-1 text-[9px] font-bold uppercase tracking-[0.22em] bg-stone-100 text-stone-500">
                  {{ file.typeLabel }}
                </span>
              </div>
              <p class="mt-4 text-xs font-medium text-stone-500 truncate">{{ file.project }}</p>
              <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400 border-t border-stone-100 pt-3">{{ file.size }} • {{ file.when }}</p>
            </article>

            <div v-if="dashboard.recentFiles.length === 0" class="rounded-[1.8rem] border border-dashed border-stone-200 p-10 text-center">
              <p class="text-xs font-medium text-stone-400 italic">Belum ada unggahan berkas.</p>
            </div>
          </div>
        </article>
      </section>
      
      <!-- SECTION 7: RADAR NOTIFICATIONS -->
      <section class="animate-reveal [animation-delay:750ms]">
        <article class="rounded-4xl border border-stone-200 bg-white p-8 shadow-[0_20px_60px_rgba(60,42,24,0.06)] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)]">
          <div class="flex items-center justify-between gap-4 mb-10">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Sinyal</p>
              <h3 class="mt-2 text-3xl font-semibold tracking-[-0.04em] text-stone-950">Radar Notifikasi Operasional</h3>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-stone-950 flex items-center justify-center text-amber-200 shadow-xl shadow-stone-900/20">
                <Bell class="h-6 w-6" />
            </div>
          </div>

          <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <article
              v-for="alert in dashboard.alerts"
              :key="alert.key"
              class="group rounded-3xl border p-6 transition-all hover:scale-[1.02]"
              :class="notificationCardClass(alert.tone)"
            >
              <div class="flex items-center justify-between mb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] opacity-60 group-hover:opacity-100 transition-opacity">{{ alert.label }}</p>
                <div class="h-2 w-2 rounded-full bg-current opacity-40"></div>
              </div>
              <p class="text-2xl font-bold tracking-[-0.03em]">{{ alert.value }}</p>
              <p class="mt-4 text-sm leading-relaxed opacity-80 group-hover:opacity-100 transition-opacity">{{ alert.description }}</p>
            </article>

            <div v-if="dashboard.alerts.length === 0" class="col-span-full rounded-3xl border-2 border-dashed border-stone-100 bg-stone-50/50 p-12 text-center">
              <p class="text-stone-400 italic">Radar notifikasi bersih. Tidak ada anomali operasional yang perlu dilaporkan.</p>
            </div>
          </div>
        </article>
      </section>

      <!-- SECTION 8: RECENT ACTIVITY (FEED) - MOVED TO BOTTOM -->
      <section class="rounded-4xl border border-stone-200 bg-white p-6 shadow-[0_20px_60px_rgba(60,42,24,0.06)] animate-reveal [animation-delay:900ms] transition-all duration-300 hover:shadow-[0_30px_80px_rgba(60,42,24,0.1)] hover:-translate-y-1">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Jejak Digital</p>
            <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">Umpan Aktivitas Terbaru</h3>
          </div>
          <div class="flex items-center gap-2 rounded-full bg-stone-50 border border-stone-100 px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-stone-500">
            <Clock class="h-3 w-3" />
            Real-Time
          </div>
        </div>

        <div v-if="dashboard.recentActivity.length" class="mt-10 space-y-3">
          <article
            v-for="activity in dashboard.recentActivity"
            :key="activity.id"
            class="group grid gap-6 rounded-[2rem] border border-stone-100 bg-stone-50/30 p-6 transition-all hover:bg-white hover:border-amber-200 hover:shadow-lg lg:grid-cols-[80px_minmax(0,1fr)_220px]"
          >
            <div class="flex items-start">
              <div class="rounded-2xl p-4 shadow-sm group-hover:scale-110 transition-transform" :class="trendClass(activity.tone)">
                <component :is="iconComponent(activity.icon)" class="w-6 h-6" />
              </div>
            </div>

            <div class="space-y-1">
              <p class="text-xl font-bold tracking-[-0.03em] text-stone-950">{{ activity.title }}</p>
              <p class="text-sm leading-relaxed text-stone-600">{{ activity.description }}</p>
            </div>

            <div class="space-y-2 text-sm lg:text-right flex flex-col justify-center">
              <p class="font-bold text-stone-900 flex items-center gap-2 lg:justify-end">
                <span class="h-1.5 w-1.5 rounded-full bg-stone-300"></span>
                {{ activity.user }}
              </p>
              <p class="text-[11px] font-bold uppercase tracking-widest text-stone-400">{{ activity.when }}</p>
            </div>
          </article>
        </div>

        <div v-else class="mt-10 rounded-[2rem] border-2 border-dashed border-stone-100 bg-stone-50/50 p-16 text-center">
          <p class="text-sm font-medium text-stone-500">Belum ada aktivitas operasional yang tercatat hari ini.</p>
        </div>
      </section>
    </div>
  </WorkspaceLayout>
</template>

<script setup>
import { Head, router, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import { 
  Banknote, 
  Building2, 
  Briefcase, 
  Clock, 
  Receipt, 
  Sparkles, 
  Activity, 
  Filter, 
  Zap, 
  Megaphone, 
  Bell,
  Activity as RadarIcon
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
  if (growthFilter.value === 'leads') return { stroke: '#295ea8' }
  if (growthFilter.value === 'projects') return { stroke: '#bb5c21' }
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
      if (month === 0) { month = 12; year -= 1 }
      label = previousMonthDays + dayNumber
    }

    if (!inMonth && dayNumber > props.dashboard.calendar.daysInMonth) {
      month += 1
      if (month === 13) { month = 1; year += 1 }
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
    zap: Zap,
    megaphone: Megaphone,
    bell: Bell,
    radar: RadarIcon
  }
  return map[key] || RadarIcon
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
    info: 'informasi',
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

function translateTimeLabel(label) {
  const map = { '7d': '7 Hari', '30d': '30 Hari', '3m': '3 Bulan', '1y': '1 Tahun' }
  return map[label] || label
}

function translateGrowthLabel(label) {
  const map = { 'revenue': 'Pendapatan', 'leads': 'Prospek', 'projects': 'Proyek' }
  return map[label] || label
}

function announceAction(action) {
  actionNotice.value = `${action.label} segera diproses. Fungsi ini akan mengarahkan Anda ke formulir pembuatan data terkait.`
}

function max(values) { return Math.max(...values, 0) }
function sum(values) { return values.reduce((total, value) => total + value, 0) }
function average(values) { return values.length ? Math.round(sum(values) / values.length) : 0 }

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: props.dashboard.context.currency || 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function formatMetricValue(value) {
  if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M'
  if (value >= 1000) return (value / 1000).toFixed(0) + 'K'
  return value
}

function hasSeriesData(values) { return values.some((value) => value > 0) }
function hasComparisonData(labels, values) { return labels.length > 0 && values.some((value) => value > 0) }
function barWidth(value, values) { const biggest = Math.max(...values, 1); return (value / biggest) * 100 }

function pointCoordinate(index, length, value = 0, values = []) {
  const x = length <= 1 ? 50 : 4 + (index * 92) / (length - 1)
  const ceiling = Math.max(...values, 1)
  const y = 30 - (value / ceiling) * 24
  return { x, y }
}

function linePoints(values) {
  return values.map((value, index) => {
    const point = pointCoordinate(index, values.length, value, values)
    return `${point.x},${point.y}`
  }).join(' ')
}

function areaPoints(values) {
  if (!values.length) return ''
  const line = linePoints(values)
  const first = pointCoordinate(0, values.length)
  const last = pointCoordinate(values.length - 1, values.length)
  return `${first.x},30 ${line} ${last.x},30`
}

function calendarDayClass(day) {
  if (day.isSelected) return 'border-stone-950 bg-stone-950 text-amber-200 shadow-lg'
  if (day.isToday) return 'border-amber-300 bg-amber-50 text-stone-950'
  if (!day.inMonth) return 'border-transparent bg-stone-50 text-stone-300'
  if (day.count > 0) return 'border-stone-100 bg-white text-stone-900'
  return 'border-transparent bg-transparent text-stone-500 hover:bg-stone-50'
}
</script>

<style scoped>
.animate-reveal {
  animation: reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes reveal {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
