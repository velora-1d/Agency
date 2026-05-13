<template>
  <WorkspaceLayout
    title="Sistem"
    subtitle="Menu 39-43 untuk role, pengaturan workspace, audit log, keamanan, dan help center dalam satu panel operasional."
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

    <SystemLayout :workspace="workspace">
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

        <section v-if="activeTab === 'roles'" class="grid gap-4 xl:grid-cols-[1.02fr_0.98fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Role Builder</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Role, inheritance, dan matriks izin per workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ roles.length }} role</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="role in roles" :key="role.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5 transition hover:border-stone-300 hover:bg-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ role.name }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="role.is_default ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-700'">
                        {{ role.is_default ? 'Bawaan' : 'Kustom' }}
                      </span>
                      <span v-if="role.parent_role_name" class="rounded-full bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">
                        Induk {{ role.parent_role_name }}
                      </span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ role.slug }}</p>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ role.description || 'Belum ada deskripsi role.' }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ role.permission_count }} izin</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ role.child_roles_count }} role turunan</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ role.created_at_label }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                      <span v-for="label in role.permission_labels.slice(0, 6)" :key="label" class="rounded-full bg-white px-3 py-1.5 text-[11px] text-stone-500">{{ label }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openRoleModal(role)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteRole(role.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Anggota Workspace</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Assignment role, owner flag, dan temporary access.</h2>
              </div>
              <button type="button" @click="openMembershipModal()" class="rounded-2xl border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-100">
                Tambah Anggota
              </button>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="member in memberships" :key="member.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ member.user?.name || 'User tidak dikenal' }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="member.is_owner ? 'bg-stone-950 text-white' : 'bg-white text-stone-500'">
                        {{ member.is_owner ? 'Owner' : 'Anggota' }}
                      </span>
                      <span v-if="member.is_expired" class="rounded-full bg-rose-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-rose-700">Berakhir</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ member.user?.email }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ member.role?.name || 'Tanpa role' }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ member.joined_at_label }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ member.expires_at_label }}</span>
                    </div>
                    <p class="mt-3 text-xs text-stone-500">2FA {{ member.user?.two_factor_enabled ? 'aktif' : 'nonaktif' }} / Login terakhir {{ member.user?.last_login_at_label || 'Belum pernah login' }}</p>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openMembershipModal(member)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteMembership(member.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>
        </section>

        <section v-else-if="activeTab === 'settings'" class="grid gap-4 xl:grid-cols-[1.06fr_0.94fr]">
          <form class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]" @submit.prevent="submitSettings">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pengaturan Workspace</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Brand, domain, connectors, working hours, dan storage policy.</h2>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
              <FormInput v-model="settingsForm.name" label="Nama Workspace" />
              <FormInput v-model="settingsForm.logo" label="URL Logo" />
              <FormInput v-model="settingsForm.primary_color" label="Warna Utama" />
              <FormInput v-model="settingsForm.custom_domain" label="Domain Kustom" />
              <FormInput v-model="settingsForm.timezone" label="Zona Waktu" />
              <FormInput v-model="settingsForm.currency" label="Mata Uang" />
              <FormInput v-model="settingsForm.language" label="Bahasa" />
              <FormInput v-model="settingsForm.storage_quota_gb" label="Kuota Penyimpanan GB" type="number" />
              <FormInput v-model="settingsForm.smtp_host" label="Host SMTP" />
              <FormInput v-model="settingsForm.smtp_port" label="Port SMTP" type="number" />
              <FormInput v-model="settingsForm.smtp_username" label="Username SMTP" />
              <FormInput v-model="settingsForm.smtp_password" label="Password SMTP" type="password" />
              <FormInput v-model="settingsForm.wa_api_key" label="API Key WA" />
              <FormInput v-model="settingsForm.wa_phone_number" label="Nomor WA" />
              <FormInput v-model="settingsForm.n8n_webhook_url" label="n8n Webhook URL" />
              <FormInput v-model="settingsForm.working_hours_start" label="Jam Kerja Mulai" type="time" />
              <FormInput v-model="settingsForm.working_hours_end" label="Jam Kerja Selesai" type="time" />
              <FormTextarea v-model="settingsForm.holiday_dates_text" label="Kalender Libur" rows="5" class-name="md:col-span-2" />
              <FormTextarea v-model="settingsForm.notification_templates_text" label="Template Notifikasi" rows="5" class-name="md:col-span-2" />
              <FormTextarea v-model="settingsForm.backup_snapshots_text" label="Snapshot Backup" rows="5" class-name="md:col-span-2" />
            </div>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="settingsForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">Simpan Pengaturan</button>
            </div>
          </form>

          <div class="space-y-4">
            <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Terkonfigurasi</p>
              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div v-for="card in settingsCards" :key="card.label" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ card.label }}</p>
                  <p class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ card.value }}</p>
                </div>
              </div>
            </article>

            <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Catatan Operasional</p>
              <div class="mt-4 space-y-3">
                <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-sm font-semibold text-stone-950">{{ panel.title }}</p>
                  <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
                </div>
              </div>
            </article>
          </div>
        </section>

        <section v-else-if="activeTab === 'audit'" class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Arus Audit</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Log aksi siapa, apa, dan kapan dalam workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ auditLogs.length }} log</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="log in auditLogs" :key="log.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ log.summary }}</h3>
                      <span class="rounded-full bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ log.user?.name || 'Sistem' }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ log.created_at_label }} / {{ log.ip_address || 'Tanpa IP' }}</p>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                      <div class="rounded-[1.2rem] border border-white bg-white p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nilai Lama</p>
                        <pre class="mt-2 whitespace-pre-wrap text-xs leading-6 text-stone-600">{{ log.old_values_text || 'Kosong' }}</pre>
                      </div>
                      <div class="rounded-[1.2rem] border border-white bg-white p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-400">Nilai Baru</p>
                        <pre class="mt-2 whitespace-pre-wrap text-xs leading-6 text-stone-600">{{ log.new_values_text || 'Kosong' }}</pre>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openAuditModal(log)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteAuditLog(log.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sinyal Audit</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Baca posture perubahan dan aktivitas sensitif.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">{{ panel.title }}</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
              </div>
            </div>
          </article>
        </section>

        <section v-else-if="activeTab === 'security'" class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">API Key</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cakupan, whitelist, kedaluwarsa, dan rate limit akses API.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ apiKeys.length }} key</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="key in apiKeys" :key="key.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div>
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ key.name }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="key.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-700'">{{ key.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ key.user?.name || 'Belum ditugaskan' }} / {{ key.key_preview }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ key.rate_limit_per_hour }}/h</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ key.expires_at_label }}</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ key.last_used_at_label }}</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                      <span v-for="scope in key.scopes.slice(0, 6)" :key="scope" class="rounded-full bg-white px-3 py-1.5 text-[11px] text-stone-500">{{ scope }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openApiKeyModal(key)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteApiKey(key.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <div class="space-y-4">
            <form class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]" @submit.prevent="submitSecurity">
              <div class="border-b border-stone-200 pb-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Kebijakan Keamanan</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">2FA, SSO, idle timeout, whitelist, dan password policy.</h2>
              </div>

              <div class="mt-5 space-y-4">
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="securityForm.require_two_factor" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>Require Two Factor</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="securityForm.allow_google_sso" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>Allow Google SSO</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                  <input v-model="securityForm.brute_force_protection" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                  <span>Brute Force Protection</span>
                </label>
                <FormInput v-model="securityForm.session_idle_minutes" label="Session Idle Minutes" type="number" />
                <FormTextarea v-model="securityForm.allowed_ips_text" label="Allowed IPs" rows="5" />
                <FormTextarea v-model="securityForm.password_policy" label="Password Policy" rows="4" />
              </div>

              <div class="mt-6 flex justify-end">
                <button type="submit" :disabled="securityForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">Simpan Keamanan</button>
              </div>
            </form>

            <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Sessions</p>
              <div class="mt-4 space-y-3">
                <div v-for="session in sessions" :key="session.id" class="rounded-[1.2rem] border border-stone-200 bg-stone-50 p-4">
                  <p class="text-sm font-semibold text-stone-950">{{ session.user_name || 'User tidak dikenal' }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ session.user_email }}</p>
                  <p class="mt-2 text-xs text-stone-500">{{ session.ip_address || 'Tanpa IP' }} / {{ session.last_activity_label }}</p>
                  <p class="mt-2 text-xs text-stone-500">{{ session.user_agent }}</p>
                </div>
              </div>
            </article>
          </div>
        </section>

        <section v-else class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="flex items-center justify-between gap-3 border-b border-stone-200 pb-5">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Bantuan</p>
                <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Dokumentasi internal, FAQ, tutorial, dan changelog workspace.</h2>
              </div>
              <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">{{ helpArticles.length }} articles</span>
            </div>

            <div class="mt-5 space-y-4">
              <article v-for="article in helpArticles" :key="article.id" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                  <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="text-base font-semibold text-stone-950">{{ article.title }}</h3>
                      <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]" :class="article.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-700'">
                        {{ article.is_published ? 'Terbit' : 'Draft' }}
                      </span>
                    </div>
                    <p class="mt-2 text-sm text-stone-500">{{ article.category || 'General' }} / {{ article.slug }}</p>
                    <p class="mt-3 text-sm leading-6 text-stone-600">{{ article.excerpt }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-stone-500">
                      <span class="rounded-full bg-white px-3 py-1.5">{{ article.view_count }} views</span>
                      <span class="rounded-full bg-white px-3 py-1.5">{{ article.updated_at_label }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-2">
                    <button type="button" @click="openHelpModal(article)" class="inline-flex items-center justify-center rounded-full border border-stone-200 p-2 text-stone-600 transition hover:border-stone-300 hover:text-stone-950">
                      <Pencil class="h-4 w-4" />
                    </button>
                    <button type="button" @click="deleteHelpArticle(article.id)" class="inline-flex items-center justify-center rounded-full border border-rose-200 p-2 text-rose-700 transition hover:bg-rose-50">
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </article>

          <article class="rounded-[2rem] border border-stone-200 bg-white p-5 shadow-[0_20px_60px_rgba(28,25,23,0.06)]">
            <div class="border-b border-stone-200 pb-5">
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Knowledge Signals</p>
              <h2 class="mt-2 text-xl font-semibold tracking-[-0.04em] text-stone-950">Cara baca publish state dan knowledge coverage.</h2>
            </div>

            <div class="mt-5 space-y-4">
              <div v-for="panel in activePanels" :key="panel.title" class="rounded-[1.6rem] border border-stone-200 bg-stone-50 p-5">
                <p class="text-sm font-semibold text-stone-950">{{ panel.title }}</p>
                <p class="mt-2 text-sm leading-6 text-stone-600">{{ panel.copy }}</p>
              </div>
            </div>
          </article>
        </section>
      </div>
    </SystemLayout>

    <Transition name="modal">
      <div v-if="showRoleModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitRole">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Role Builder</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingRoleId ? 'Ubah Role' : 'Role Baru' }}</h3>
            </div>
            <button type="button" @click="closeRoleModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <FormInput v-model="roleForm.name" label="Nama Role" />
            <FormInput v-model="roleForm.slug" label="Slug Role" />
            <FormInput v-model="roleForm.description" label="Deskripsi" class-name="md:col-span-2" />
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Role Induk</span>
              <select v-model="roleForm.parent_role_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa induk</option>
                <option v-for="item in options.roleOptions" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
              <input v-model="roleForm.is_default" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Role bawaan</span>
            </label>
          </div>

          <div class="mt-6 space-y-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Permission Matrix</p>
            <div class="grid gap-4 md:grid-cols-2">
              <div v-for="module in permissionModules" :key="module.name" class="rounded-[1.4rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-950">{{ formatOption(module.name) }}</p>
                <div class="mt-3 grid gap-3">
                  <label v-for="permission in module.items" :key="permission.id" class="flex items-center gap-3 rounded-2xl border border-white bg-white px-4 py-3 text-sm text-stone-700">
                    <input v-model="roleForm.permission_ids" type="checkbox" :value="permission.id" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
                    <span>{{ permission.module }} / {{ permission.action }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeRoleModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="roleForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingRoleId ? 'Simpan Perubahan' : 'Buat Role' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showMembershipModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitMembership">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Anggota Workspace</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingMembershipId ? 'Ubah Anggota' : 'Tambah Anggota' }}</h3>
            </div>
            <button type="button" @click="closeMembershipModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm md:col-span-2">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">User</span>
              <select v-model="membershipForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Pilih user</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }} / {{ user.email }}</option>
              </select>
            </label>
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Role</span>
              <select v-model="membershipForm.role_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Tanpa role</option>
                <option v-for="item in options.roleOptions" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </label>
            <FormInput v-model="membershipForm.joined_at" label="Bergabung Pada" type="datetime-local" />
            <FormInput v-model="membershipForm.expires_at" label="Berakhir Pada" type="datetime-local" />
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
              <input v-model="membershipForm.is_owner" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Akses Owner</span>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeMembershipModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="membershipForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingMembershipId ? 'Simpan Perubahan' : 'Tambah Anggota' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showAuditModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitAuditLog">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Audit Log</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingAuditId ? 'Ubah Log' : 'Log Baru' }}</h3>
            </div>
            <button type="button" @click="closeAuditModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">User</span>
              <select v-model="auditForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Sistem</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
            </label>
            <FormInput v-model="auditForm.module" label="Modul" />
            <FormInput v-model="auditForm.action" label="Aksi" />
            <FormInput v-model="auditForm.model_type" label="Tipe Model" />
            <FormInput v-model="auditForm.model_id" label="ID Model" />
            <FormInput v-model="auditForm.ip_address" label="Alamat IP" />
            <FormInput v-model="auditForm.user_agent" label="User Agent" class-name="md:col-span-2" />
            <FormTextarea v-model="auditForm.old_values_text" label="JSON Nilai Lama" rows="6" />
            <FormTextarea v-model="auditForm.new_values_text" label="JSON Nilai Baru" rows="6" />
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeAuditModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="auditForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingAuditId ? 'Simpan Perubahan' : 'Buat Log' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showApiKeyModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitApiKey">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">API Key</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingApiKeyId ? 'Ubah Key' : 'API Key Baru' }}</h3>
            </div>
            <button type="button" @click="closeApiKeyModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <FormInput v-model="apiKeyForm.name" label="Nama Key" />
            <label class="space-y-2 text-sm">
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">Penugas</span>
              <select v-model="apiKeyForm.user_id" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none focus:border-stone-400 focus:bg-white">
                <option value="">Belum ditugaskan</option>
                <option v-for="user in options.users" :key="user.id" :value="user.id">{{ user.name }}</option>
              </select>
            </label>
            <FormInput v-model="apiKeyForm.key_value" label="Nilai Key Asli" type="password" class-name="md:col-span-2" />
            <FormInput v-model="apiKeyForm.rate_limit_per_hour" label="Batas / Jam" type="number" />
            <FormInput v-model="apiKeyForm.expires_at" label="Berakhir Pada" type="datetime-local" />
            <FormTextarea v-model="apiKeyForm.scopes_text" label="Cakupan" rows="5" />
            <FormTextarea v-model="apiKeyForm.ip_whitelist_text" label="IP Whitelist" rows="5" />
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 md:col-span-2">
              <input v-model="apiKeyForm.is_active" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Key Aktif</span>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeApiKeyModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="apiKeyForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingApiKeyId ? 'Simpan Perubahan' : 'Buat API Key' }}</button>
          </div>
        </form>
      </div>
    </Transition>

    <Transition name="modal">
      <div v-if="showHelpModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-stone-950/40 p-4 backdrop-blur-sm">
        <form class="w-full max-w-4xl rounded-[2rem] bg-white p-6 shadow-2xl" @submit.prevent="submitHelpArticle">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-stone-400">Pusat Bantuan</p>
              <h3 class="mt-2 text-2xl font-semibold tracking-[-0.04em] text-stone-950">{{ editingHelpId ? 'Ubah Artikel' : 'Artikel Baru' }}</h3>
            </div>
            <button type="button" @click="closeHelpModal" class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700">x</button>
          </div>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <FormInput v-model="helpForm.title" label="Judul" class-name="md:col-span-2" />
            <FormInput v-model="helpForm.slug" label="Slug" />
            <FormInput v-model="helpForm.category" label="Kategori" />
            <FormTextarea v-model="helpForm.content" label="Konten" rows="10" class-name="md:col-span-2" />
            <FormInput v-model="helpForm.view_count" label="Jumlah Lihat" type="number" />
            <label class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
              <input v-model="helpForm.is_published" type="checkbox" class="h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" />
              <span>Terbit</span>
            </label>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="closeHelpModal" class="rounded-2xl border border-stone-200 bg-white px-5 py-3 text-sm font-semibold text-stone-600 transition hover:bg-stone-100 hover:text-stone-900">Batal</button>
            <button type="submit" :disabled="helpForm.processing" class="rounded-2xl bg-stone-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-stone-800 disabled:opacity-60">{{ editingHelpId ? 'Simpan Perubahan' : 'Buat Artikel' }}</button>
          </div>
        </form>
      </div>
    </Transition>
  </WorkspaceLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Pencil, Plus, Trash2 } from 'lucide-vue-next'
import WorkspaceLayout from '../../Layouts/WorkspaceLayout.vue'
import SystemLayout from '../../Layouts/SystemLayout.vue'

const props = defineProps({
  workspace: Object,
  workspaceSettings: Object,
  activeTab: String,
  roles: Array,
  permissions: Array,
  memberships: Array,
  auditLogs: Array,
  apiKeys: Array,
  helpArticles: Array,
  sessions: Array,
  options: Object,
  summary: Object,
})

const systemBaseUrl = `/w/${encodeURIComponent(props.workspace.slug)}/system`
const activeTab = computed(() => props.activeTab || 'roles')

const tabMeta = {
  roles: {
    menu: 39,
    label: 'Role & Permissions',
    actionLabel: 'Role Baru',
    headline: 'Role workspace dibaca sebagai matrix izin dan assignment nyata, bukan daftar statis.',
    copy: 'Owner, admin, finance, marketing, dan role custom bisa dibangun per workspace dengan izin yang terbaca jelas.',
  },
  settings: {
    menu: 40,
    label: 'Pengaturan Workspace',
    actionLabel: '',
    headline: 'Brand, domain, SMTP, WA, n8n, dan jam kerja ditaruh dalam satu panel operasional.',
    copy: 'Konfigurasi workspace tidak lagi tercecer karena semua setting inti disimpan dan dibaca dari workspace yang aktif.',
  },
  audit: {
    menu: 41,
    label: 'Audit Log',
    actionLabel: 'Log Audit Baru',
    headline: 'Audit log memberi jejak siapa mengubah apa, kapan, dan dari mana perubahan itu datang.',
    copy: 'Log create, update, delete, sampai manual note dipakai untuk baca aktivitas sensitif dan investigasi internal.',
  },
  security: {
    menu: 42,
    label: 'Keamanan',
    actionLabel: 'API Key Baru',
    headline: 'Security posture dibaca dari policy, API key, session aktif, dan kebiasaan login tim.',
    copy: '2FA, SSO, idle timeout, whitelist, dan key scopes digabung supaya kontrol akses tidak tersebar.',
  },
  help: {
    menu: 43,
    label: 'Pusat Bantuan',
    actionLabel: 'Artikel Baru',
    headline: 'Pusat bantuan jadi knowledge base internal yang bisa diterbitkan, direvisi, dan dilacak.',
    copy: 'FAQ, tutorial, changelog, dan artikel onboarding dibaca langsung dari data workspace, bukan catatan luar sistem.',
  },
}

const activeMeta = computed(() => tabMeta[activeTab.value] ?? tabMeta.roles)

const activeStatCards = computed(() => {
  const cards = {
    roles: [
      { label: 'Role', value: props.summary.roles.total },
      { label: 'Bawaan', value: props.summary.roles.default },
      { label: 'Kustom', value: props.summary.roles.custom },
      { label: 'Akses Sementara', value: props.summary.roles.temporary_members },
    ],
    settings: [
      { label: 'Integrasi', value: props.summary.settings.configured_integrations },
      { label: 'Libur', value: props.summary.settings.holiday_count },
      { label: 'Template', value: props.summary.settings.template_count },
      { label: 'Backup', value: props.summary.settings.backup_count },
    ],
    audit: [
      { label: 'Log', value: props.summary.audit.total },
      { label: 'Hari Ini', value: props.summary.audit.today },
      { label: 'Hapus', value: props.summary.audit.delete_actions },
      { label: 'Aktor', value: props.summary.audit.users },
    ],
    security: [
      { label: 'API Key', value: props.summary.security.api_keys },
      { label: 'Key Aktif', value: props.summary.security.active_keys },
      { label: 'Sesi', value: props.summary.security.active_sessions },
      { label: 'User 2FA', value: props.summary.security.two_factor_users },
    ],
    help: [
      { label: 'Artikel', value: props.summary.help.articles },
      { label: 'Terbit', value: props.summary.help.published },
      { label: 'Draft', value: props.summary.help.drafts },
      { label: 'Kategori', value: props.summary.help.categories },
    ],
  }

  return cards[activeTab.value] ?? cards.roles
})

const activeSignals = computed(() => {
  const signals = {
    roles: [
      { label: 'Role kustom', copy: `${props.summary.roles.custom} role kustom aktif di workspace ini.` },
      { label: 'Akses sementara', copy: `${props.summary.roles.temporary_members} membership memakai expiry date.` },
      { label: 'Sebaran izin', copy: `${props.permissions.length} izin tersedia untuk dipetakan ke role.` },
    ],
    settings: [
      { label: 'Layanan terhubung', copy: `${props.summary.settings.configured_integrations} konektor inti sudah dikonfigurasi.` },
      { label: 'Posisi backup', copy: `${props.summary.settings.backup_count} snapshot backup tercatat di settings.` },
      { label: 'Kebijakan kalender', copy: `${props.summary.settings.holiday_count} hari libur tersimpan untuk workspace.` },
    ],
    audit: [
      { label: 'Volume log', copy: `${props.summary.audit.total} audit log terbaru sedang terbaca di panel ini.` },
      { label: 'Aksi sensitif', copy: `${props.summary.audit.delete_actions} aksi delete muncul di stream audit.` },
      { label: 'Aktor aktif', copy: `${props.summary.audit.users} user berbeda muncul di jejak audit terbaru.` },
    ],
    security: [
      { label: 'Key posture', copy: `${props.summary.security.active_keys} API key aktif masih bisa dipakai.` },
      { label: 'Session load', copy: `${props.summary.security.active_sessions} session user masih aktif.` },
      { label: '2FA posture', copy: `${props.summary.security.two_factor_users} membership memakai akun dengan 2FA aktif.` },
    ],
    help: [
      { label: 'Cakupan materi', copy: `${props.summary.help.articles} artikel sudah tersimpan di pusat bantuan.` },
      { label: 'Status terbit', copy: `${props.summary.help.published} artikel sudah terbit dan ${props.summary.help.drafts} masih draft.` },
      { label: 'Taksonomi', copy: `${props.summary.help.categories} kategori aktif dipakai untuk dokumentasi.` },
    ],
  }

  return signals[activeTab.value] ?? signals.roles
})

const activePanels = computed(() => {
  const panels = {
    settings: [
      { title: 'Workspace identity', copy: 'Nama, brand color, logo, dan custom domain dipakai sebagai identitas utama tenant ini.' },
      { title: 'Operational connectors', copy: 'SMTP, WA, dan n8n webhook dirapikan dalam satu panel supaya tidak tercecer di file config terpisah.' },
      { title: 'Storage and backup', copy: 'Quota storage, holiday calendar, template notifikasi, dan snapshot backup disimpan di workspace settings.' },
    ],
    audit: [
      { title: 'Manual entry', copy: 'Selain log otomatis dari model auditable, admin juga bisa menambah log manual untuk catatan insiden.' },
      { title: 'Change visibility', copy: 'Old values dan new values diringkas untuk mempermudah investigasi perubahan data.' },
      { title: 'Actor trace', copy: 'IP address, user agent, dan timestamp membantu memastikan jejak aksi tetap bisa ditelusuri.' },
    ],
    help: [
      { title: 'Kontrol terbit', copy: 'Setiap artikel bisa disimpan sebagai draft dulu atau langsung diterbitkan ke pusat bantuan internal.' },
      { title: 'Kebutuhan onboarding', copy: 'Kategori dan slug membuat artikel lebih mudah dipakai untuk onboarding maupun changelog.' },
      { title: 'Sinyal penggunaan', copy: 'View count memberi indikasi artikel mana yang paling sering dipakai tim.' },
    ],
  }

  return panels[activeTab.value] ?? []
})

const settingsCards = computed(() => [
  { label: 'Storage', value: `${props.workspaceSettings.storage_quota_gb} GB` },
  { label: 'Timezone', value: props.workspaceSettings.timezone },
  { label: 'Currency', value: props.workspaceSettings.currency },
  { label: 'Hours', value: `${props.workspaceSettings.working_hours_start} - ${props.workspaceSettings.working_hours_end}` },
])

const permissionModules = computed(() =>
  Object.entries(props.options.permissionModules || {}).map(([name, items]) => ({ name, items })),
)

function formatOption(value) {
  return String(value || '').replaceAll('-', ' ').replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function submitDelete(url, message) {
  if (!confirm(message)) return
  router.delete(url, { preserveScroll: true })
}

const settingsForm = useForm({
  name: props.workspaceSettings.name || '',
  logo: props.workspaceSettings.logo || '',
  primary_color: props.workspaceSettings.primary_color || '',
  timezone: props.workspaceSettings.timezone || 'Asia/Jakarta',
  currency: props.workspaceSettings.currency || 'IDR',
  language: props.workspaceSettings.language || 'id',
  custom_domain: props.workspaceSettings.custom_domain || '',
  smtp_host: props.workspaceSettings.smtp_host || '',
  smtp_port: props.workspaceSettings.smtp_port || '',
  smtp_username: props.workspaceSettings.smtp_username || '',
  smtp_password: '',
  wa_api_key: props.workspaceSettings.wa_api_key || '',
  wa_phone_number: props.workspaceSettings.wa_phone_number || '',
  n8n_webhook_url: props.workspaceSettings.n8n_webhook_url || '',
  working_hours_start: props.workspaceSettings.working_hours_start || '08:00',
  working_hours_end: props.workspaceSettings.working_hours_end || '17:00',
  storage_quota_gb: props.workspaceSettings.storage_quota_gb || 50,
  holiday_dates_text: props.workspaceSettings.holiday_dates_text || '',
  notification_templates_text: props.workspaceSettings.notification_templates_text || '',
  backup_snapshots_text: props.workspaceSettings.backup_snapshots_text || '',
})

function submitSettings() {
  settingsForm.patch(`${systemBaseUrl}/settings`, { preserveScroll: true })
}

const securityForm = useForm({
  require_two_factor: props.workspaceSettings.require_two_factor ?? false,
  allow_google_sso: props.workspaceSettings.allow_google_sso ?? true,
  brute_force_protection: props.workspaceSettings.brute_force_protection ?? true,
  session_idle_minutes: props.workspaceSettings.session_idle_minutes || 30,
  allowed_ips_text: props.workspaceSettings.allowed_ips_text || '',
  password_policy: props.workspaceSettings.password_policy || '',
})

function submitSecurity() {
  securityForm.patch(`${systemBaseUrl}/security`, { preserveScroll: true })
}

const showRoleModal = ref(false)
const editingRoleId = ref(null)
const roleForm = useForm({
  name: '',
  slug: '',
  description: '',
  is_default: false,
  parent_role_id: '',
  permission_ids: [],
})

function openRoleModal(item = null) {
  editingRoleId.value = item?.id || null
  roleForm.reset()
  roleForm.clearErrors()
  roleForm.name = item?.name || ''
  roleForm.slug = item?.slug || ''
  roleForm.description = item?.description || ''
  roleForm.is_default = item?.is_default ?? false
  roleForm.parent_role_id = item?.parent_role_id || ''
  roleForm.permission_ids = Array.isArray(item?.permission_ids) ? [...item.permission_ids] : []
  showRoleModal.value = true
}

function closeRoleModal() {
  showRoleModal.value = false
  editingRoleId.value = null
}

function submitRole() {
  const url = editingRoleId.value ? `${systemBaseUrl}/roles/${encodeURIComponent(editingRoleId.value)}` : `${systemBaseUrl}/roles`
  const method = editingRoleId.value ? roleForm.patch : roleForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeRoleModal() })
}

function deleteRole(id) {
  submitDelete(`${systemBaseUrl}/roles/${encodeURIComponent(id)}`, 'Hapus role ini?')
}

const showMembershipModal = ref(false)
const editingMembershipId = ref(null)
const membershipForm = useForm({
  user_id: '',
  role_id: '',
  is_owner: false,
  joined_at: '',
  expires_at: '',
})

function openMembershipModal(item = null) {
  editingMembershipId.value = item?.id || null
  membershipForm.reset()
  membershipForm.clearErrors()
  membershipForm.user_id = item?.user_id || ''
  membershipForm.role_id = item?.role_id || ''
  membershipForm.is_owner = item?.is_owner ?? false
  membershipForm.joined_at = item?.joined_at || ''
  membershipForm.expires_at = item?.expires_at || ''
  showMembershipModal.value = true
}

function closeMembershipModal() {
  showMembershipModal.value = false
  editingMembershipId.value = null
}

function submitMembership() {
  const url = editingMembershipId.value ? `${systemBaseUrl}/memberships/${encodeURIComponent(editingMembershipId.value)}` : `${systemBaseUrl}/memberships`
  const method = editingMembershipId.value ? membershipForm.patch : membershipForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeMembershipModal() })
}

function deleteMembership(id) {
  submitDelete(`${systemBaseUrl}/memberships/${encodeURIComponent(id)}`, 'Hapus membership ini?')
}

const showAuditModal = ref(false)
const editingAuditId = ref(null)
const auditForm = useForm({
  user_id: '',
  module: '',
  action: '',
  model_type: '',
  model_id: '',
  ip_address: '',
  user_agent: '',
  old_values_text: '',
  new_values_text: '',
})

function openAuditModal(item = null) {
  editingAuditId.value = item?.id || null
  auditForm.reset()
  auditForm.clearErrors()
  auditForm.user_id = item?.user_id || ''
  auditForm.module = item?.module || ''
  auditForm.action = item?.action || ''
  auditForm.model_type = item?.model_type || ''
  auditForm.model_id = item?.model_id || ''
  auditForm.ip_address = item?.ip_address || ''
  auditForm.user_agent = item?.user_agent || ''
  auditForm.old_values_text = item?.old_values_text || ''
  auditForm.new_values_text = item?.new_values_text || ''
  showAuditModal.value = true
}

function closeAuditModal() {
  showAuditModal.value = false
  editingAuditId.value = null
}

function submitAuditLog() {
  const url = editingAuditId.value ? `${systemBaseUrl}/audit-logs/${encodeURIComponent(editingAuditId.value)}` : `${systemBaseUrl}/audit-logs`
  const method = editingAuditId.value ? auditForm.patch : auditForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeAuditModal() })
}

function deleteAuditLog(id) {
  submitDelete(`${systemBaseUrl}/audit-logs/${encodeURIComponent(id)}`, 'Hapus log audit ini?')
}

const showApiKeyModal = ref(false)
const editingApiKeyId = ref(null)
const apiKeyForm = useForm({
  user_id: '',
  name: '',
  key_value: '',
  scopes_text: '',
  ip_whitelist_text: '',
  rate_limit_per_hour: 1000,
  expires_at: '',
  is_active: true,
})

function openApiKeyModal(item = null) {
  editingApiKeyId.value = item?.id || null
  apiKeyForm.reset()
  apiKeyForm.clearErrors()
  apiKeyForm.user_id = item?.user_id || ''
  apiKeyForm.name = item?.name || ''
  apiKeyForm.key_value = ''
  apiKeyForm.scopes_text = item?.scopes_text || ''
  apiKeyForm.ip_whitelist_text = item?.ip_whitelist_text || ''
  apiKeyForm.rate_limit_per_hour = item?.rate_limit_per_hour || 1000
  apiKeyForm.expires_at = item?.expires_at || ''
  apiKeyForm.is_active = item?.is_active ?? true
  showApiKeyModal.value = true
}

function closeApiKeyModal() {
  showApiKeyModal.value = false
  editingApiKeyId.value = null
}

function submitApiKey() {
  const url = editingApiKeyId.value ? `${systemBaseUrl}/api-keys/${encodeURIComponent(editingApiKeyId.value)}` : `${systemBaseUrl}/api-keys`
  const method = editingApiKeyId.value ? apiKeyForm.patch : apiKeyForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeApiKeyModal() })
}

function deleteApiKey(id) {
  submitDelete(`${systemBaseUrl}/api-keys/${encodeURIComponent(id)}`, 'Hapus API key ini?')
}

const showHelpModal = ref(false)
const editingHelpId = ref(null)
const helpForm = useForm({
  title: '',
  slug: '',
  category: '',
  content: '',
  is_published: true,
  view_count: 0,
})

function openHelpModal(item = null) {
  editingHelpId.value = item?.id || null
  helpForm.reset()
  helpForm.clearErrors()
  helpForm.title = item?.title || ''
  helpForm.slug = item?.slug || ''
  helpForm.category = item?.category || ''
  helpForm.content = item?.content || ''
  helpForm.is_published = item?.is_published ?? true
  helpForm.view_count = item?.view_count ?? 0
  showHelpModal.value = true
}

function closeHelpModal() {
  showHelpModal.value = false
  editingHelpId.value = null
}

function submitHelpArticle() {
  const url = editingHelpId.value ? `${systemBaseUrl}/help-articles/${encodeURIComponent(editingHelpId.value)}` : `${systemBaseUrl}/help-articles`
  const method = editingHelpId.value ? helpForm.patch : helpForm.post
  method(url, { preserveScroll: true, onSuccess: () => closeHelpModal() })
}

function deleteHelpArticle(id) {
  submitDelete(`${systemBaseUrl}/help-articles/${encodeURIComponent(id)}`, 'Hapus artikel ini?')
}

function openActiveModal() {
  if (activeTab.value === 'roles') openRoleModal()
  else if (activeTab.value === 'audit') openAuditModal()
  else if (activeTab.value === 'security') openApiKeyModal()
  else if (activeTab.value === 'help') openHelpModal()
}
</script>

<script>
export default {
  components: {
    FormInput: {
      props: ['modelValue', 'label', 'type', 'className'],
      emits: ['update:modelValue'],
      template: `
        <label :class="['space-y-2 text-sm', className]">
          <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ label }}</span>
          <input
            :value="modelValue"
            :type="type || 'text'"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
          />
        </label>`,
    },
    FormTextarea: {
      props: ['modelValue', 'label', 'rows', 'className'],
      emits: ['update:modelValue'],
      template: `
        <label :class="['space-y-2 text-sm', className]">
          <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-stone-400">{{ label }}</span>
          <textarea
            :value="modelValue"
            :rows="rows || 4"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 outline-none transition-all focus:border-stone-400 focus:bg-white"
          ></textarea>
        </label>`,
    },
  },
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
