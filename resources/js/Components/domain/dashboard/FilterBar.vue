<script setup>
import { ref, watch } from 'vue';
import { 
    Calendar, 
    User, 
    Tag, 
    Layers, 
    Filter,
    X,
    ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    options: {
        type: Object,
        required: true,
        default: () => ({
            pics: [],
            categories: [],
            tiers: [],
            datePresets: []
        })
    },
    activeFilters: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['change', 'clear']);

const localFilters = ref({
    date_range: props.activeFilters.date_range || '',
    pic_id: props.activeFilters.pic_id || '',
    category: props.activeFilters.category || '',
    tier: props.activeFilters.tier || ''
});

const isFilterActive = (key) => !!localFilters.value[key];
const hasAnyFilter = () => Object.values(localFilters.value).some(v => !!v);

const handleChange = () => {
    emit('change', localFilters.value);
};

const clearFilters = () => {
    localFilters.value = {
        date_range: '',
        pic_id: '',
        category: '',
        tier: ''
    };
    emit('clear');
};

watch(() => props.activeFilters, (newVal) => {
    localFilters.value = {
        date_range: newVal.date_range || '',
        pic_id: newVal.pic_id || '',
        category: newVal.category || '',
        tier: newVal.tier || ''
    };
}, { deep: true });
</script>

<template>
    <div class="flex flex-wrap items-center gap-3 py-4 border-b border-stone-200/40">
        <!-- Date Range -->
        <div class="relative group transition-all duration-300 hover:-translate-y-0.5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-amber-200/60 group-focus-within:text-amber-300 transition-colors">
                <Calendar class="w-4 h-4" />
            </div>
            <select 
                v-model="localFilters.date_range"
                @change="handleChange"
                class="pl-11 pr-10 py-3 bg-stone-900 border border-stone-800 rounded-2xl text-sm text-stone-50 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/40 outline-none transition-all appearance-none min-w-[180px] shadow-sm font-medium hover:border-stone-700"
            >
                <option value="" class="bg-stone-900 text-stone-50">Semua Waktu</option>
                <option v-for="opt in options.datePresets" :key="opt.value" :value="opt.value" class="bg-stone-900 text-stone-50">
                    {{ opt.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-stone-500">
                <ChevronDown class="w-4 h-4" />
            </div>
        </div>

        <!-- PIC / Team -->
        <div class="relative group transition-all duration-300 hover:-translate-y-0.5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-amber-200/60 group-focus-within:text-amber-300 transition-colors">
                <User class="w-4 h-4" />
            </div>
            <select 
                v-model="localFilters.pic_id"
                @change="handleChange"
                class="pl-11 pr-10 py-3 bg-stone-900 border border-stone-800 rounded-2xl text-sm text-stone-50 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/40 outline-none transition-all appearance-none min-w-[180px] shadow-sm font-medium hover:border-stone-700"
            >
                <option value="" class="bg-stone-900 text-stone-50">Semua PIC</option>
                <option v-for="opt in options.pics" :key="opt.value" :value="opt.value" class="bg-stone-900 text-stone-50">
                    {{ opt.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-stone-500">
                <ChevronDown class="w-4 h-4" />
            </div>
        </div>

        <!-- Category -->
        <div class="relative group transition-all duration-300 hover:-translate-y-0.5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-amber-200/60 group-focus-within:text-amber-300 transition-colors">
                <Tag class="w-4 h-4" />
            </div>
            <select 
                v-model="localFilters.category"
                @change="handleChange"
                class="pl-11 pr-10 py-3 bg-stone-900 border border-stone-800 rounded-2xl text-sm text-stone-50 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/40 outline-none transition-all appearance-none min-w-[180px] shadow-sm font-medium hover:border-stone-700"
            >
                <option value="" class="bg-stone-900 text-stone-50">Semua Kategori</option>
                <option v-for="opt in options.categories" :key="opt.value" :value="opt.value" class="bg-stone-900 text-stone-50">
                    {{ opt.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-stone-500">
                <ChevronDown class="w-4 h-4" />
            </div>
        </div>

        <!-- Tier -->
        <div class="relative group transition-all duration-300 hover:-translate-y-0.5">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-amber-200/60 group-focus-within:text-amber-300 transition-colors">
                <Layers class="w-4 h-4" />
            </div>
            <select 
                v-model="localFilters.tier"
                @change="handleChange"
                class="pl-11 pr-10 py-3 bg-stone-900 border border-stone-800 rounded-2xl text-sm text-stone-50 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500/40 outline-none transition-all appearance-none min-w-[180px] shadow-sm font-medium hover:border-stone-700"
            >
                <option value="" class="bg-stone-900 text-stone-50">Semua Tier</option>
                <option v-for="opt in options.tiers" :key="opt.value" :value="opt.value" class="bg-stone-900 text-stone-50">
                    {{ opt.label }}
                </option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-stone-500">
                <ChevronDown class="w-4 h-4" />
            </div>
        </div>

        <!-- Clear Filters -->
        <button 
            v-if="hasAnyFilter()"
            @click="clearFilters"
            class="flex items-center gap-2 px-5 py-3 text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 rounded-2xl transition-all font-medium border border-rose-900/20"
        >
            <X class="w-4 h-4" />
            <span>Reset</span>
        </button>

        <div v-else class="text-[10px] text-stone-400 flex items-center gap-2 ml-auto uppercase tracking-[0.2em] font-bold">
            <Filter class="w-3.5 h-3.5 text-amber-200/40" />
            <span>Filter Global Aktif</span>
        </div>
    </div>
</template>

<style scoped>
select {
    background-image: none;
}
</style>
