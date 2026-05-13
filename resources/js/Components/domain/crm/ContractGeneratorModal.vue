<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/60 p-4 backdrop-blur-sm">
      <div class="w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col rounded-[2.5rem] bg-white shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-stone-100 bg-stone-50/50 px-8 py-6 shrink-0">
          <div>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-stone-950 px-3 py-1 text-[9px] font-bold uppercase tracking-[0.2em] text-amber-200">Langkah {{ currentStep }} dari 5</span>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-stone-400">Generator Kontrak</p>
            </div>
            <h3 class="mt-2 text-2xl font-bold text-stone-900">{{ stepTitle }}</h3>
          </div>
          <button @click="$emit('close')" class="rounded-full p-2 text-stone-500 transition hover:bg-stone-200">
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- Progress Bar -->
        <div class="h-1.5 w-full bg-stone-100 shrink-0">
            <div 
                class="h-full bg-stone-900 transition-all duration-500" 
                :style="{ width: `${(currentStep / 5) * 100}%` }"
            ></div>
        </div>

        <!-- Body: Steps -->
        <div class="flex-1 overflow-y-auto p-8 scrollbar-none">
          
          <!-- STEP 1: PARA PIHAK -->
          <div v-if="currentStep === 1" class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <section class="space-y-6">
              <div class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-stone-500">Pihak Pertama (Agensi)</h4>
              </div>
              <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Nama Agensi</label>
                  <input v-model="form.agensi_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">PIC Agensi</label>
                  <input v-model="form.agensi_pic" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Jabatan PIC</label>
                  <input v-model="form.agensi_pic_position" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="col-span-full space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Alamat Lengkap Agensi</label>
                  <input v-model="form.agensi_address" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Email Agensi</label>
                    <input v-model="form.agensi_email" type="email" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Nama Bank</label>
                    <input v-model="form.bank_name" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Nomor Rekening</label>
                    <input v-model="form.bank_acc_no" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
              </div>
            </section>

            <section class="space-y-6">
              <div class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-stone-500">Pihak Kedua (Klien)</h4>
              </div>
              <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Nama Perusahaan Klien</label>
                  <input v-model="form.client_company" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Nama PIC Klien</label>
                  <input v-model="form.client_pic" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Jabatan PIC Klien</label>
                  <input v-model="form.client_pic_position" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="col-span-full space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Alamat Lengkap Klien</label>
                  <input v-model="form.client_address" type="text" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
              </div>
            </section>
          </div>

          <!-- STEP 2: INFO PROYEK -->
          <div v-else-if="currentStep === 2" class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <section class="space-y-6">
              <div class="grid gap-6 sm:grid-cols-2">
                <div class="col-span-full space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Pilih Proyek (Auto-Fill)</label>
                  <select v-model="selectedProjectId" @change="autoFillProject" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all">
                    <option value="">-- Pilih Proyek untuk mengisi data otomatis --</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                  </select>
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Nama Proyek</label>
                  <input v-model="form.project_name" type="text" placeholder="Misal: Redesign Website PT. Maju" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Jenis Proyek</label>
                  <input v-model="form.project_type" type="text" placeholder="Misal: Web Development / UI Design" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Platform / Teknologi</label>
                  <input v-model="form.project_tech" type="text" placeholder="Misal: Laravel, Vue, Figma" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Tgl Mulai</label>
                        <input v-model="form.project_start_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Tgl Selesai</label>
                        <input v-model="form.project_end_date" type="date" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                    </div>
                </div>
              </div>
            </section>
          </div>

          <!-- STEP 3: BIAYA & PEMBAYARAN -->
          <div v-else-if="currentStep === 3" class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <section class="space-y-8">
              <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Jasa Pengerjaan (Rp)</label>
                  <input v-model="form.cost_service" type="number" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Biaya Domain (Rp)</label>
                  <input v-model="form.cost_domain" type="number" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Hosting / VPS (Rp)</label>
                  <input v-model="form.cost_hosting" type="number" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">SSL Certificate (Rp)</label>
                  <input v-model="form.cost_ssl" type="number" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Maintenance (Rp)</label>
                  <input v-model="form.cost_maintenance" type="number" class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 focus:bg-white transition-all" />
                </div>
                <div class="space-y-1">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Total Keseluruhan</label>
                  <div class="w-full rounded-2xl border border-stone-200 bg-stone-900 px-4 py-3 text-sm font-bold text-amber-200">
                    {{ formatCurrency(totalCost) }}
                  </div>
                </div>
              </div>

              <div class="rounded-3xl bg-stone-50 p-6 border border-stone-100">
                  <h4 class="text-[11px] font-bold uppercase tracking-widest text-stone-400 mb-6">Jadwal Pembayaran (Termin)</h4>
                  <div class="grid gap-6 sm:grid-cols-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Down Payment (DP %)</label>
                        <input v-model="form.payment_dp_percent" type="number" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 transition-all" />
                        <p class="text-[10px] text-stone-500 mt-2 font-bold">{{ formatCurrency(totalCost * (form.payment_dp_percent / 100)) }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Termin 2 (%)</label>
                        <input v-model="form.payment_t2_percent" type="number" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 transition-all" />
                        <p class="text-[10px] text-stone-500 mt-2 font-bold">{{ formatCurrency(totalCost * (form.payment_t2_percent / 100)) }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Pelunasan (%)</label>
                        <input v-model="form.payment_final_percent" type="number" class="w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 transition-all" />
                        <p class="text-[10px] text-stone-500 mt-2 font-bold">{{ formatCurrency(totalCost * (form.payment_final_percent / 100)) }}</p>
                    </div>
                  </div>
                  <p v-if="totalPercent !== 100" class="mt-4 text-xs font-bold text-rose-600">Peringatan: Total persentase termin adalah {{ totalPercent }}% (Harus 100%)</p>
              </div>
            </section>
          </div>

          <!-- STEP 4: SCOPE OF WORK -->
          <div v-else-if="currentStep === 4" class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <section class="space-y-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                    <h4 class="text-xs font-bold uppercase tracking-widest text-stone-500">Lampiran A: Scope of Work</h4>
                </div>
                <button type="button" @click="addSowRow" class="inline-flex items-center gap-2 rounded-xl bg-stone-900 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-white hover:bg-stone-800 transition-all">
                    <Plus class="h-3 w-3" />
                    <span>Tambah Fitur</span>
                </button>
              </div>

              <div class="overflow-hidden rounded-3xl border border-stone-200 shadow-sm">
                  <table class="w-full text-left text-sm">
                      <thead class="bg-stone-50 text-[10px] font-bold uppercase tracking-widest text-stone-400">
                          <tr>
                              <th class="px-5 py-4 w-[240px]">Fitur / Menu</th>
                              <th class="px-5 py-4">Deskripsi Singkat</th>
                              <th class="px-5 py-4 w-[160px]">Platform</th>
                              <th class="px-5 py-4 w-[100px]">Aksi</th>
                          </tr>
                      </thead>
                      <tbody class="divide-y divide-stone-100 bg-white">
                          <tr v-for="(row, index) in form.sow_items" :key="index">
                              <td class="px-4 py-3">
                                  <input v-model="row.feature" type="text" placeholder="Nama Fitur" class="w-full border-none bg-transparent p-0 text-sm focus:ring-0" />
                              </td>
                              <td class="px-4 py-3">
                                  <input v-model="row.description" type="text" placeholder="Apa yang didapat?" class="w-full border-none bg-transparent p-0 text-sm focus:ring-0" />
                              </td>
                              <td class="px-4 py-3">
                                  <input v-model="row.platform" type="text" placeholder="Web/Mobile" class="w-full border-none bg-transparent p-0 text-sm focus:ring-0" />
                              </td>
                              <td class="px-4 py-3 text-center">
                                  <button @click="removeSowRow(index)" class="text-stone-300 hover:text-rose-600 transition-colors">
                                      <Trash2 class="h-4 w-4" />
                                  </button>
                              </td>
                          </tr>
                          <tr v-if="form.sow_items.length === 0">
                              <td colspan="4" class="px-5 py-10 text-center text-stone-400 italic">Belum ada item scope of work. Tambahkan fitur untuk memulai.</td>
                          </tr>
                      </tbody>
                  </table>
              </div>

              <div class="space-y-1 pt-6 border-t border-stone-100">
                  <label class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Jumlah Maksimal Revisi</label>
                  <input v-model="form.max_revisions" type="number" class="w-[120px] rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-900 outline-none focus:border-stone-400 transition-all" />
              </div>
            </section>
          </div>

          <!-- STEP 5: PRATINJAU KONTRAK -->
          <div v-else-if="currentStep === 5" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="rounded-4xl border border-stone-200 bg-white p-12 shadow-inner max-h-[60vh] overflow-y-auto scrollbar-none border-dashed mx-auto max-w-[800px]">
                <div class="contract-preview" v-html="parseMarkdownToHtml(previewContent)"></div>
            </div>
            <div class="flex items-center gap-3 p-5 rounded-3xl bg-amber-50 border border-amber-100 text-amber-900 text-sm">
                <Info class="h-5 w-5 shrink-0" />
                <p>Silakan tinjau draf kontrak di atas. Pastikan semua data pihak, biaya, dan scope sudah benar sebelum disimpan. Kontrak ini nantinya dapat diekspor menjadi PDF.</p>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center border-t border-stone-100 bg-stone-50/50 px-8 py-6 shrink-0">
          <div>
              <button 
                v-if="currentStep > 1" 
                @click="currentStep--" 
                class="rounded-2xl border border-stone-200 bg-white px-6 py-3 text-sm font-bold text-stone-600 transition hover:bg-stone-50"
              >
                Kembali
              </button>
          </div>
          
          <div class="flex gap-3">
              <button 
                v-if="currentStep < 5" 
                @click="currentStep++" 
                class="rounded-2xl bg-stone-900 px-10 py-3 text-sm font-bold text-white shadow-lg transition hover:scale-[1.02] active:scale-[0.98]"
              >
                Lanjutkan
              </button>
              <button 
                v-else 
                @click="submit" 
                :disabled="form.processing || totalPercent !== 100" 
                class="rounded-2xl bg-stone-950 px-10 py-3 text-sm font-bold text-white shadow-lg shadow-stone-900/20 transition hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
              >
                {{ form.processing ? 'Menyimpan...' : 'Simpan & Selesaikan Kontrak' }}
              </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { X, Plus, Trash2, Info } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  workspace: Object,
  client: Object,
  projects: {
    type: Array,
    default: () => [],
  },
  initialData: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const currentStep = ref(1)
const selectedProjectId = ref('')

const stepTitle = computed(() => {
    const titles = [
        'Data Para Pihak (Agensi & Klien)',
        'Informasi Proyek & Timeline',
        'Rincian Biaya & Termin Pembayaran',
        'Lingkup Kerja (Scope of Work)',
        'Pratinjau Dokumen Kontrak'
    ]
    return (props.initialData ? 'Ubah ' : 'Buat ') + titles[currentStep.value - 1]
})

// ... form initialization ...

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.initialData) {
            // Populate form from existing contract
            form.project_name = props.initialData.title.replace('Kontrak Proyek: ', '')
            form.cost_service = props.initialData.value
            form.project_start_date = props.initialData.start_date
            form.project_end_date = props.initialData.end_date
            // Content parsing is complex, we assume user might want to regenerate or we'd need structured metadata
        } else {
            form.reset()
            currentStep.value = 1
        }
    }
})

const form = useForm({
  // Agensi
  agensi_name: 'Kantor Digital',
  agensi_email: 'hello@kantordigital.com',
  agensi_phone: '0812-xxxx-xxxx',
  agensi_address: 'Jakarta, Indonesia',
  agensi_pic: 'Pak Hakim',
  agensi_pic_position: 'Founder / CEO',
  bank_name: 'BCA (Kantor Digital)',
  bank_acc_no: '5555555555',
  
  // Klien
  client_id: props.client.id,
  client_company: props.client.company_name,
  client_pic: props.client.pic_name || '',
  client_pic_position: 'Owner / Manager',
  client_email: props.client.email || '',
  client_phone: props.client.phone || '',
  client_address: props.client.address || '',

  // Proyek
  project_id: '',
  project_name: '',
  project_type: '',
  project_description: '',
  project_tech: '',
  project_start_date: '',
  project_end_date: '',

  // Biaya
  cost_service: 0,
  cost_domain: 0,
  cost_hosting: 0,
  cost_ssl: 0,
  cost_maintenance: 0,
  
  // Pembayaran
  payment_dp_percent: 50,
  payment_t2_percent: 0,
  payment_final_percent: 50,
  
  // Revisi & SOW
  max_revisions: 5,
  sow_items: [
      { feature: 'Manajemen Proyek', description: 'Akses dashboard dan pelaporan progres', platform: 'Web', status: 'Include' }
  ],
})

const totalCost = computed(() => {
  return Number(form.cost_service) + Number(form.cost_domain) + Number(form.cost_hosting) + Number(form.cost_ssl) + Number(form.cost_maintenance)
})

const totalPercent = computed(() => {
    return Number(form.payment_dp_percent) + Number(form.payment_t2_percent) + Number(form.payment_final_percent)
})

const previewContent = computed(() => generateMarkdown())

function addSowRow() {
    form.sow_items.push({ feature: '', description: '', platform: '', status: 'Include' })
}

function removeSowRow(index) {
    form.sow_items.splice(index, 1)
}

function autoFillProject() {
  const project = props.projects.find(p => p.id === selectedProjectId.value)
  if (project) {
    form.project_id = project.id
    form.project_name = project.name
    form.project_start_date = project.start_date || ''
    form.project_end_date = project.end_date || ''
    form.cost_service = project.budget || 0
    
    // Update Agensi Details based on Project Brand
    if (project.brand === 'Maven Forge') {
        form.agensi_name = 'Maven Forge'
        form.agensi_email = 'hello@mavenforge.com'
        form.agensi_pic = 'Pak Hakim'
        form.bank_name = 'BCA (Maven Forge)'
        form.bank_acc_no = '1234567890'
    } else if (project.brand === 'Velora ID') {
        form.agensi_name = 'Velora ID'
        form.agensi_email = 'hello@velora.id'
        form.agensi_pic = 'PIC Velora'
        form.bank_name = 'Mandiri (Velora ID)'
        form.bank_acc_no = '0987654321'
    } else {
        form.agensi_name = 'Kantor Digital'
        form.agensi_email = 'hello@kantordigital.com'
        form.agensi_pic = 'Admin Kantor Digital'
        form.bank_name = 'BCA (Kantor Digital)'
        form.bank_acc_no = '5555555555'
    }
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function parseMarkdownToHtml(md) {
  if (!md) return ''
  
  let html = md
  // Headings
  html = html.replace(/^# (.*$)/gm, '<h1 class="text-3xl font-bold border-b-2 border-stone-900 pb-2 mb-6 uppercase text-center">$1</h1>')
  html = html.replace(/^## (.*$)/gm, '<h2 class="text-xl font-bold mt-8 mb-4 border-l-4 border-stone-900 pl-3 uppercase">$1</h2>')
  html = html.replace(/^### (.*$)/gm, '<h3 class="text-lg font-bold mt-6 mb-3">$1</h3>')
  
  // Bold
  html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
  
  // Tables
  const lines = html.split('\n')
  let inTable = false
  let tableHtml = ''
  
  const finalLines = []
  lines.forEach(line => {
    if (line.trim().startsWith('|')) {
      if (!inTable) {
        inTable = true
        tableHtml = '<div class="my-6 overflow-hidden border border-stone-300 rounded-lg"><table class="w-full text-left border-collapse">'
      }
      
      // Skip separator row |---|---|
      if (line.includes('---')) return
      
      const cells = line.split('|').filter(c => c.trim() !== '' || line.indexOf('|') === 0)
      // Filter out empty ends from split
      if (line.startsWith('|')) cells.shift()
      
      const isHeader = tableHtml.includes('<thead>') === false
      if (isHeader) {
          tableHtml += '<thead class="bg-stone-50"><tr>'
          cells.forEach(c => { tableHtml += `<th class="px-4 py-3 border border-stone-300 font-bold text-[11px] uppercase">${c.trim()}</th>` })
          tableHtml += '</tr></thead><tbody>'
      } else {
          tableHtml += '<tr>'
          cells.forEach(c => { tableHtml += `<td class="px-4 py-3 border border-stone-300 text-sm">${c.trim()}</td>` })
          tableHtml += '</tr>'
      }
    } else {
      if (inTable) {
        inTable = false
        tableHtml += '</tbody></table></div>'
        finalLines.push(tableHtml)
      }
      finalLines.push(line)
    }
  })
  
  html = finalLines.join('\n')
  
  // Lists
  html = html.replace(/^- (.*$)/gm, '<li class="ml-6 mb-1 list-disc">$1</li>')
  
  // Paragraphs & Br
  html = html.replace(/\n\n/g, '</p><p class="mb-4 text-justify leading-relaxed">')
  html = html.replace(/\n/g, '<br />')
  
  // Horizontal Rule
  html = html.replace(/^---$/gm, '<hr class="my-10 border-stone-200" />')

  return '<div class="legal-doc">' + html + '</div>'
}

function buildSowTable() {
    if (form.sow_items.length === 0) return '| ______________________________ | ______________________________ | ______ | ✅ Include |\n';
    
    let table = '';
    form.sow_items.forEach((item, index) => {
        table += '| ' + (index + 1) + ' | ' + (item.feature || '-') + ' | ' + (item.description || '-') + ' | ' + (item.platform || '-') + ' | ✅ Include |\n';
    });
    return table;
}

function generateMarkdown() {
  const dateStr = new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
  const noKontrak = 'MF-' + new Date().getFullYear() + '-' + Math.random().toString(36).substring(2, 8).toUpperCase()
  const projectName = form.project_name || '______________________________'
  
  let md = '# ' + form.agensi_name.toUpperCase() + '\n'
  md += '## Digital Agency & Technology Solutions\n\n'
  md += '# PERJANJIAN KERJA SAMA PROYEK\n'
  md += '### (PROJECT AGREEMENT)\n\n'
  md += '| **No. Kontrak** | ' + noKontrak + ' |\n'
  md += '|---|---|\n'
  md += '| **Tanggal** | ' + dateStr + ' |\n'
  md += '| **Nama Proyek** | ' + projectName + ' |\n\n'
  md += '*Dokumen ini merupakan perjanjian yang sah dan mengikat secara hukum antara ' + form.agensi_name + ' dan Klien yang tertera di bawah ini.*\n\n'
  md += '---\n\n'
  md += '## BAB I — DATA PARA PIHAK\n\n'
  md += '### Pasal 1.1 — Pihak Pertama (Agensi)\n\n'
  md += '| **Nama Agensi** | ' + form.agensi_name + ' |\n'
  md += '|---|---|\n'
  md += '| **Jenis Usaha** | Digital Agency & Technology Solutions |\n'
  md += '| **Alamat** | ' + form.agensi_address + ' |\n'
  md += '| **Nomor Telepon** | ' + form.agensi_phone + ' |\n'
  md += '| **Email** | ' + form.agensi_email + ' |\n'
  md += '| **PIC / Penanggung Jawab** | ' + form.agensi_pic + ' |\n'
  md += '| **Jabatan PIC** | ' + form.agensi_pic_position + ' |\n\n'
  md += '### Pasal 1.2 — Pihak Kedua (Klien)\n\n'
  md += '| **Nama Klien / PIC** | ' + (form.client_pic || '______________________________') + ' |\n'
  md += '|---|---|\n'
  md += '| **Nama Perusahaan** | ' + (form.client_company || '______________________________') + ' |\n'
  md += '| **Alamat** | ' + (form.client_address || '______________________________') + ' |\n'
  md += '| **Nomor Telepon** | ' + (form.client_phone || '______________________________') + ' |\n'
  md += '| **Email** | ' + (form.client_email || '______________________________') + ' |\n'
  md += '| **Jabatan** | ' + (form.client_pic_position || '______________________________') + ' |\n\n'
  md += 'Pihak Pertama dan Pihak Kedua selanjutnya secara bersama-sama disebut **PARA PIHAK**, dan masing-masing disebut **PIHAK**, sepakat untuk mengadakan Perjanjian Kerja Sama Proyek dengan syarat dan ketentuan sebagai berikut.\n\n'
  md += '---\n\n'
  md += '## BAB II — DASAR HUKUM & DEFINISI\n\n'
  md += '### Pasal 2.1 — Dasar Hukum\n\n'
  md += 'Perjanjian ini dibuat berdasarkan dan tunduk pada peraturan perundang-undangan yang berlaku di Republik Indonesia, antara lain:\n\n'
  md += '- Kitab Undang-Undang Hukum Perdata (KUH Perdata) Pasal 1313 tentang Perjanjian\n'
  md += '- Undang-Undang No. 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik (UU ITE)\n'
  md += '- Undang-Undang No. 28 Tahun 2014 tentang Hak Cipta\n'
  md += '- Undang-Undang No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP)\n'
  md += '- Undang-Undang No. 40 Tahun 2007 tentang Perseroan Terbatas (jika berlaku)\n\n'
  md += '### Pasal 2.2 — Definisi\n\n'
  md += 'Dalam perjanjian ini, istilah-istilah berikut memiliki arti sebagai berikut:\n\n'
  md += '- **"Proyek"** berarti pekerjaan yang disepakati sebagaimana tercantum dalam Lampiran A perjanjian ini.\n'
  md += '- **"Scope of Work"** berarti daftar fitur, menu, dan deliverable yang termasuk dalam Proyek.\n'
  md += '- **"Change Request"** berarti permintaan perubahan atau penambahan di luar Scope of Work.\n'
  md += '- **"Deliverable"** berarti hasil pekerjaan yang wajib diserahkan oleh Pihak Pertama kepada Pihak Kedua.\n'
  md += '- **"Source Code"** berarti kode sumber dari hasil pengerjaan Proyek.\n'
  md += '- **"Revisi"** berarti perubahan pada Deliverable yang masih dalam lingkup Scope of Work.\n'
  md += '- **"Force Majeure"** berarti kejadian di luar kendali Para Pihak seperti bencana alam, perang, atau kebijakan pemerintah.\n\n'
  md += '---\n\n'
  md += '## BAB III — INFORMASI PROYEK\n\n'
  md += '### Pasal 3.1 — Detail Proyek\n\n'
  md += '| **Nama Proyek** | ' + projectName + ' |\n'
  md += '|---|---|\n'
  md += '| **Jenis Proyek** | ' + (form.project_type || '______________________________') + ' |\n'
  md += '| **Deskripsi Singkat** | ' + (form.project_description || '______________________________') + ' |\n'
  md += '| **Platform / Teknologi** | ' + (form.project_tech || '______________________________') + ' |\n'
  md += '| **Tanggal Mulai** | ' + (form.project_start_date || '______________________________') + ' |\n'
  md += '| **Estimasi Selesai** | ' + (form.project_end_date || '______________________________') + ' |\n'
  md += '| **PIC dari Pihak Pertama** | ' + form.agensi_pic + ' |\n'
  md += '| **PIC dari Pihak Kedua** | ' + (form.client_pic || '______________________________') + ' |\n\n'
  md += '### Pasal 3.2 — Scope of Work\n\n'
  md += 'Daftar lengkap fitur dan menu yang termasuk dalam Proyek tercantum dalam Lampiran A. Segala pekerjaan di luar Lampiran A dikategorikan sebagai Change Request dan akan diatur tersendiri.\n\n'
  md += '---\n\n'
  md += '## BAB IV — BIAYA & PEMBAYARAN\n\n'
  md += '### Pasal 4.1 — Rincian Biaya\n\n'
  md += '| **No** | **Item Biaya** | **Keterangan** | **Harga (Rp)** |\n'
  md += '|---|---|---|---|\n'
  md += '| 1 | Jasa Pengerjaan | Sesuai Scope of Work | ' + formatCurrency(form.cost_service) + ' |\n'
  md += '| 2 | Domain (1 Tahun) | Terlampir | ' + formatCurrency(form.cost_domain) + ' |\n'
  md += '| 3 | Hosting / Server (1 Tahun) | Terlampir | ' + formatCurrency(form.cost_hosting) + ' |\n'
  md += '| 4 | SSL Certificate | Terlampir | ' + formatCurrency(form.cost_ssl) + ' |\n'
  md += '| 5 | Maintenance | Terlampir | ' + formatCurrency(form.cost_maintenance) + ' |\n'
  md += '| 6 | Lisensi Plugin / Tools | Terlampir | 0 |\n'
  md += '| 7 | Lainnya | - | 0 |\n'
  md += '|  | **TOTAL** |  | **' + formatCurrency(totalCost.value) + '** |\n\n'
  md += '### Pasal 4.2 — Jadwal Pembayaran\n\n'
  md += '| **No** | **Termin** | **Milestone / Syarat** | **Persentase** | **Nominal (Rp)** |\n'
  md += '|---|---|---|---|---|\n'
  md += '| 1 | Down Payment (DP) | Sebelum pengerjaan dimulai | ' + form.payment_dp_percent + ' % | ' + formatCurrency(totalCost.value * (form.payment_dp_percent / 100)) + ' |\n'
  md += '| 2 | Termin 2 | Tengah Proyek / Milestone 1 | ' + form.payment_t2_percent + ' % | ' + formatCurrency(totalCost.value * (form.payment_t2_percent / 100)) + ' |\n'
  md += '| 3 | Pelunasan | Setelah proyek selesai & diterima | ' + form.payment_final_percent + ' % | ' + formatCurrency(totalCost.value * (form.payment_final_percent / 100)) + ' |\n\n'
  md += 'Pembayaran dilakukan melalui rekening resmi ' + form.agensi_name + '. Bukti pembayaran wajib dikirimkan ke email atau WhatsApp PIC Pihak Pertama. Pihak Pertama berhak menunda pengerjaan apabila pembayaran sesuai termin tidak diterima dalam waktu yang disepakati.\n\n'
  md += '---\n\n'
  md += '## BAB V — PELAKSANAAN & TIMELINE\n\n'
  md += '### Pasal 5.1 — Kewajiban Pihak Pertama (' + form.agensi_name + ')\n\n'
  md += '- Mengerjakan Proyek sesuai Scope of Work yang tercantum dalam Lampiran A.\n'
  md += '- Memberikan laporan progres secara berkala kepada Pihak Kedua.\n'
  md += '- Menyelesaikan Proyek sesuai estimasi timeline yang disepakati.\n'
  md += '- Memberikan pemberitahuan tertulis apabila terjadi keterlambatan.\n'
  md += '- Menjaga kerahasiaan data dan informasi Pihak Kedua.\n'
  md += '- Menyerahkan Source Code setelah pelunasan 100% diterima.\n\n'
  md += '### Pasal 5.2 — Kewajiban Pihak Kedua (Klien)\n\n'
  md += '- Menyediakan konten, aset, dan informasi yang dibutuhkan dalam waktu yang wajar.\n'
  md += '- Memberikan feedback atau persetujuan atas hasil pekerjaan maksimal dalam 3 hari kerja.\n'
  md += '- Melakukan pembayaran sesuai jadwal yang telah disepakati.\n'
  md += '- Tidak mempekerjakan pihak lain untuk mengerjakan proyek yang sama selama perjanjian berlaku.\n'
  md += '- Menjaga kerahasiaan proses, metode, dan harga yang telah disepakati dengan Pihak Pertama.\n\n'
  md += '### Pasal 5.3 — Keterlambatan Pihak Pertama\n\n'
  md += 'Apabila Pihak Pertama mengalami keterlambatan dalam penyelesaian Proyek:\n\n'
  md += '- Pihak Pertama wajib memberikan pemberitahuan tertulis kepada Pihak Kedua.\n'
  md += '- Pihak Pertama memberikan kompensasi minimal 1 (satu) kali revisi gratis.\n'
  md += '- Kompensasi tambahan dapat disepakati bersama sesuai kondisi keterlambatan.\n'
  md += '- Keterlambatan akibat Force Majeure atau keterlambatan penyediaan aset oleh Pihak Kedua tidak termasuk dalam ketentuan ini.\n\n'
  md += '### Pasal 5.4 — Keterlambatan Pihak Kedua\n\n'
  md += 'Apabila Pihak Kedua terlambat dalam:\n\n'
  md += '- Melakukan pembayaran sesuai termin yang disepakati.\n'
  md += '- Menyediakan konten, aset, atau informasi yang dibutuhkan.\n'
  md += '- Memberikan feedback atau persetujuan dalam waktu yang ditentukan.\n\n'
  md += 'Maka Pihak Pertama berhak me-**PAUSE** pengerjaan Proyek hingga kewajiban Pihak Kedua dipenuhi. Estimasi timeline akan disesuaikan dengan durasi pause.\n\n'
  md += '---\n\n'
  md += '## BAB VI — REVISI & CHANGE REQUEST\n\n'
  md += '### Pasal 6.1 — Ketentuan Revisi\n\n'
  md += 'Revisi yang termasuk dalam perjanjian ini adalah perubahan pada Deliverable yang masih berada dalam lingkup Scope of Work.\n\n'
  md += '| **Ketentuan Revisi** | **Keterangan** |\n'
  md += '|---|---|\n'
  md += '| Jumlah Revisi | Maksimal ' + form.max_revisions + ' kali |\n'
  md += '| Waktu Pengajuan Revisi | Maksimal 7 hari setelah Deliverable diterima |\n'
  md += '| Cakupan Revisi | Perubahan dalam Scope of Work yang telah disepakati |\n'
  md += '| Di Luar Cakupan | Dikategorikan sebagai Change Request — dikenakan biaya tambahan |\n\n'
  md += '### Pasal 6.2 — Change Request\n\n'
  md += 'Permintaan perubahan, penambahan fitur, atau pekerjaan di luar Scope of Work dikategorikan sebagai Change Request dengan ketentuan:\n\n'
  md += '- Pihak Kedua mengajukan Change Request secara tertulis kepada Pihak Pertama.\n'
  md += '- Pihak Pertama memberikan estimasi biaya dan timeline tambahan dalam 3 hari kerja.\n'
  md += '- Change Request dianggap disetujui setelah Pihak Kedua memberikan persetujuan tertulis.\n'
  md += '- Biaya Change Request disepakati secara custom melalui addendum terpisah.\n'
  md += '- Pengerjaan Change Request dimulai setelah pembayaran DP Change Request diterima.\n\n'
  md += '---\n\n'
  md += '## BAB VII — HAK KEPEMILIKAN & SERAH TERIMA\n\n'
  md += '### Pasal 7.1 — Hak Kepemilikan\n\n'
  md += '- Selama proses pengerjaan, Source Code dan semua Deliverable merupakan aset Pihak Pertama.\n'
  md += '- Hak kepemilikan penuh atas Source Code berpindah ke Pihak Kedua setelah pelunasan 100% diterima oleh Pihak Pertama.\n'
  md += '- Pihak Pertama berhak menampilkan Proyek sebagai portofolio kecuali ada perjanjian kerahasiaan khusus.\n\n'
  md += '### Pasal 7.2 — Serah Terima Proyek\n\n'
  md += '- Serah terima Proyek dilakukan setelah Deliverable selesai dan disetujui Pihak Kedua.\n'
  md += '- Source Code diserahkan dalam bentuk repository atau file ZIP setelah pelunasan diterima.\n'
  md += '- Pihak Kedua wajib menandatangani Berita Acara Serah Terima (BAST) sebagai tanda penerimaan resmi.\n'
  md += '- Setelah BAST ditandatangani, Proyek dinyatakan selesai dan garansi bug mulai berjalan.\n\n'
  md += '---\n\n'
  md += '## BAB VIII — GARANSI & MAINTENANCE\n\n'
  md += '### Pasal 8.1 — Garansi Bug\n\n'
  md += '| **Ketentuan** | **Detail** |\n'
  md += '|---|---|\n'
  md += '| Masa Garansi | 2 (dua) bulan sejak tanggal BAST ditandatangani |\n'
  md += '| Yang Ditanggung | Bug / error yang berasal dari hasil pengerjaan Pihak Pertama |\n'
  md += '| Yang Tidak Ditanggung | Error akibat modifikasi oleh Pihak Kedua atau pihak lain |\n'
  md += '| Biaya Garansi | Gratis untuk bug dalam cakupan garansi |\n\n'
  md += '### Pasal 8.2 — Modifikasi oleh Pihak Kedua\n\n'
  md += 'Apabila Pihak Kedua atau pihak lain yang ditunjuk Pihak Kedua melakukan modifikasi pada Deliverable:\n\n'
  md += '- Garansi bug otomatis gugur untuk bagian yang dimodifikasi.\n'
  md += '- Perbaikan atas kerusakan akibat modifikasi tersebut merupakan tanggung jawab Pihak Kedua.\n'
  md += '- Biaya perbaikan disepakati secara custom sesuai kompleksitas masalah.\n\n'
  md += '---\n\n'
  md += '## BAB IX — KERAHASIAAN (NDA)\n\n'
  md += '### Pasal 9.1 — Kewajiban Kerahasiaan\n\n'
  md += 'Para Pihak sepakat untuk menjaga kerahasiaan informasi rahasia satu sama lain, termasuk namun tidak terbatas pada:\n\n'
  md += '- **Pihak Pertama:** menjaga kerahasiaan data bisnis, data pelanggan, strategi, dan informasi teknis Pihak Kedua.\n'
  md += '- **Pihak Kedua:** menjaga kerahasiaan metode kerja, harga, proses internal, dan informasi bisnis Pihak Pertama.\n'
  md += '- Para Pihak tidak diperkenankan mengungkapkan informasi rahasia kepada pihak ketiga tanpa persetujuan tertulis.\n\n'
  md += '### Pasal 9.2 — Perlindungan Data Pribadi\n\n'
  md += 'Sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi:\n\n'
  md += '- Data pribadi yang diterima dalam rangka pelaksanaan Proyek hanya digunakan untuk keperluan Proyek.\n'
  md += '- Para Pihak wajib menerapkan langkah keamanan yang wajar untuk melindungi data pribadi.\n'
  md += '- Kewajiban kerahasiaan ini tetap berlaku setelah perjanjian berakhir.\n\n'
  md += '---\n\n'
  md += '## BAB X — PEMBATALAN KONTRAK\n\n'
  md += '### Pasal 10.1 — Pembatalan oleh Pihak Kedua (Klien)\n\n'
  md += 'Apabila Pihak Kedua membatalkan kontrak setelah pengerjaan dimulai:\n\n'
  md += '- Down Payment (DP) dipotong **15% (lima belas persen)** sebagai biaya administrasi dan cancellation fee.\n'
  md += '- Sisa DP setelah pemotongan 15% dikembalikan (refund) kepada Pihak Kedua dalam 14 hari kerja.\n'
  md += '- Pihak Kedua tidak berhak atas Deliverable yang belum selesai dikerjakan.\n\n'
  md += '### Pasal 10.2 — Pembatalan oleh Pihak Pertama (' + form.agensi_name + ')\n\n'
  md += 'Apabila Pihak Pertama membatalkan kontrak:\n\n'
  md += '- Down Payment (DP) dikembalikan (refund) **100% (seratus persen)** kepada Pihak Kedua dalam 14 hari kerja.\n'
  md += '- Pihak Pertama wajib memberikan pemberitahuan tertulis minimal 7 hari sebelum pembatalan.\n\n'
  md += '### Pasal 10.3 — Force Majeure\n\n'
  md += 'Para Pihak dibebaskan dari kewajiban perjanjian apabila terjadi kondisi Force Majeure. Pihak yang mengalami Force Majeure wajib memberitahukan pihak lainnya dalam waktu 3 x 24 jam. Para Pihak akan bermusyawarah untuk menentukan tindakan selanjutnya.\n\n'
  md += '---\n\n'
  md += '## BAB XI — PENYELESAIAN SENGKETA\n\n'
  md += '### Pasal 11.1 — Musyawarah & Mediasi\n\n'
  md += 'Apabila terjadi perselisihan antara Para Pihak sehubungan dengan perjanjian ini:\n\n'
  md += '- Para Pihak mengutamakan penyelesaian secara musyawarah mufakat dalam 30 hari kalender.\n'
  md += '- Apabila musyawarah tidak mencapai kesepakatan, Para Pihak sepakat menempuh jalur mediasi.\n'
  md += '- Biaya mediasi ditanggung bersama secara proporsional oleh Para Pihak.\n\n'
  md += '### Pasal 11.2 — Jalur Hukum\n\n'
  md += 'Apabila mediasi tidak berhasil, Para Pihak sepakat untuk menyelesaikan perselisihan melalui jalur hukum dengan ketentuan:\n\n'
  md += '- Pengadilan yang berwenang adalah pengadilan yang disepakati bersama oleh Para Pihak.\n'
  md += '- Perjanjian ini tunduk pada hukum yang berlaku di Republik Indonesia.\n\n'
  md += '--- \n\n'
  md += '## PENANDATANGANAN\n\n'
  md += 'Perjanjian ini dibuat dalam 2 (dua) rangkap asli yang masing-masing memiliki kekuatan hukum yang sama, ditandatangani oleh Para Pihak pada tanggal yang tercantum di bawah ini.\n\n'
  md += '| **PIHAK PERTAMA** | **PIHAK KEDUA** |\n'
  md += '|---|---|\n'
  md += '| ' + form.agensi_name + ' | ' + (form.client_company || 'Klien') + ' |\n'
  md += '| | |\n'
  md += '| ( ______________________________ ) | ( ______________________________ ) |\n'
  md += '| Nama & Jabatan: ' + form.agensi_pic + ' | Nama & Jabatan: ' + (form.client_pic || '______________________________') + ' |\n'
  md += '| Tgl: ' + dateStr + ' | Tgl: ' + dateStr + ' |\n\n'
  md += '*Materai Rp 10.000*\n\n'
  md += '---\n\n'
  md += '## LAMPIRAN A — SCOPE OF WORK\n\n'
  md += '### Detail Fitur & Menu Proyek\n\n'
  md += 'Tabel berikut merupakan daftar lengkap fitur dan menu yang termasuk dalam Proyek. Pekerjaan di luar tabel ini dikategorikan sebagai Change Request.\n\n'
  md += '| **No** | **Fitur / Menu** | **Deskripsi** | **Platform** | **Status** |\n'
  md += '|---|---|---|---|---|\n'
  md += buildSowTable()
  md += '\n**Keterangan Status:**\n\n'
  md += '- ✅ Include — Termasuk dalam Proyek tanpa biaya tambahan\n'
  md += '- ❌ Tidak Include — Di luar Proyek, dapat diajukan sebagai Change Request\n\n'
  md += 'Disetujui oleh Para Pihak:\n\n'
  md += '| Pihak Pertama: ' + form.agensi_pic + ' | Pihak Kedua: ' + (form.client_pic || '______________________________') + ' |\n'
  md += '|---|---|\n\n'
  md += '---\n\n'
  md += '## LAMPIRAN B — RINCIAN BIAYA DETAIL\n\n'
  md += '### Breakdown Biaya Proyek\n\n'
  md += '| **No** | **Item** | **Keterangan Detail** | **Harga (Rp)** |\n'
  md += '|---|---|---|---|\n'
  md += '| 1 | Jasa Pengerjaan | Sesuai Scope of Work | ' + formatCurrency(form.cost_service) + ' |\n'
  md += '| 2 | Domain (.com / .id / dll) | - | ' + formatCurrency(form.cost_domain) + ' |\n'
  md += '| 3 | Hosting / VPS / Cloud | - | ' + formatCurrency(form.cost_hosting) + ' |\n'
  md += '| 4 | SSL Certificate | - | ' + formatCurrency(form.cost_ssl) + ' |\n'
  md += '| 5 | Maintenance (per bulan) | - | ' + formatCurrency(form.cost_maintenance) + ' |\n'
  md += '| 6 | Lisensi Plugin / Tools | - | 0 |\n'
  md += '| 7 | Email Profesional | - | 0 |\n'
  md += '| 8 | Backup & Security | - | 0 |\n'
  md += '| 9 | Training / Handover | - | 0 |\n'
  md += '| 10 | Lainnya | - | 0 |\n'
  md += '|  | **SUBTOTAL** | | **' + formatCurrency(totalCost.value) + '** |\n'
  md += '|  | **TOTAL KESELURUHAN** | | **' + formatCurrency(totalCost.value) + '** |\n\n'
  md += '### Rekening Pembayaran\n\n'
  md += '| **Bank** | ' + (form.bank_name || 'BCA (Kantor Digital)') + ' |\n'
  md += '|---|---|\n'
  md += '| **Nama Rekening** | ' + form.agensi_name + ' |\n'
  md += '| **Nomor Rekening** | ' + (form.bank_acc_no || '1234567890') + ' |\n'
  md += '| **Atas Nama** | ' + form.agensi_name + ' |\n\n'
  md += '*Harap konfirmasi setiap pembayaran kepada PIC Pihak Pertama via WhatsApp atau email dengan menyertakan bukti transfer.*\n\n'
  md += 'Disetujui oleh Para Pihak:\n\n'
  md += '| Pihak Pertama: ' + form.agensi_pic + ' | Pihak Kedua: ' + (form.client_pic || '______________________________') + ' |\n'
  md += '|---|---|\n\n'
  md += '---\n\n'
  md += '*© 2026 ' + form.agensi_name + ' — Dokumen ini bersifat rahasia dan mengikat secara hukum*'
  
  return md
}

function submit() {
  const content = generateMarkdown()
  const payload = {
    title: 'Kontrak Proyek: ' + (form.project_name || form.client_company),
    content: content,
    value: totalCost.value,
    start_date: form.project_start_date,
    end_date: form.project_end_date,
    client_id: props.client.id,
    project_id: form.project_id
  }

  const options = {
    onSuccess: () => {
      emit('success')
      emit('close')
    },
  }

  if (props.initialData) {
      form.transform(() => payload).patch(`/w/${encodeURIComponent(props.workspace.slug)}/projects/contracts/${props.initialData.id}`, options)
  } else {
      form.transform(() => payload).post(`/w/${encodeURIComponent(props.workspace.slug)}/projects/contracts`, options)
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}

.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Legal Document Styling */
:deep(.legal-doc) {
  font-family: 'Times New Roman', Times, serif;
  color: #1c1917;
}

:deep(.legal-doc h1) {
  font-size: 1.75rem;
  text-align: center;
  margin-bottom: 2rem;
  font-weight: bold;
  text-transform: uppercase;
}

:deep(.legal-doc h2) {
  font-size: 1.25rem;
  margin-top: 2.5rem;
  margin-bottom: 1.5rem;
  font-weight: bold;
  border-left: 4px solid #1c1917;
  padding-left: 1rem;
}

:deep(.legal-doc h3) {
  font-size: 1.1rem;
  margin-top: 1.5rem;
  margin-bottom: 1rem;
  font-weight: bold;
}

:deep(.legal-doc p) {
  margin-bottom: 1rem;
  text-align: justify;
  line-height: 1.6;
}

:deep(.legal-doc table) {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
}

:deep(.legal-doc th), :deep(.legal-doc td) {
  border: 1px solid #d6d3d1;
  padding: 0.75rem;
  text-align: left;
}

:deep(.legal-doc thead) {
  background-color: #f5f5f4;
}

:deep(.legal-doc hr) {
  border: 0;
  border-top: 1px solid #e7e5e4;
  margin: 3rem 0;
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slide-in-from-bottom {
  from { transform: translateY(20px); }
  to { transform: translateY(0); }
}
.animate-in {
  animation-fill-mode: both;
}
.fade-in {
  animation-name: fade-in;
}
.slide-in-from-bottom-4 {
  animation-name: slide-in-from-bottom;
}
</style>
