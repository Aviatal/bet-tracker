<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import ExtendableAutocomplete from '@/components/ExtendableAutocomplete.vue';
import { Competitor, Discipline, EventType, Selection } from '@/types';
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const toast = useToast();

const form = useForm({
    events: [
        {
            is_live: false,
            home: null,
            away: null,
            event_date: null,
            discipline: null,
            event_type: null,
            selection: null,
        },
    ],
    odds: null,
    stake: null,
});

const competitors = ref<Competitor[]>([]);
const disciplines = ref<Discipline[]>([]);
const eventTypes = ref<EventType[]>([]);
const selections = ref<Selection[]>([]);
const isAnalyzing = ref(false);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const potentialWin = computed(() => {
    const total = (Number(form.odds) || 0) * (Number(form.stake) || 0);
    return total.toFixed(2);
});

onMounted(() => {
    //TODO: Zamienić na jeden request
    getCompetitors();
    getDisciplines();
    getEventTypes();
    getSelections();
});

const getCompetitors = () =>
    axios.get('/get-competitors').then((res) => (competitors.value = res.data));
const getDisciplines = () =>
    axios.get('/get-disciplines').then((res) => (disciplines.value = res.data));
const getEventTypes = () =>
    axios.get('/get-event-types').then((res) => (eventTypes.value = res.data));
const getSelections = () =>
    axios.get('/get-selections').then((res) => (selections.value = res.data));

const addEvent = () => {
    form.events.push({
        is_live: false,
        home: null,
        away: null,
        event_date: null,
        discipline: null,
        event_type: null,
        selection: null,
    });
};

const removeEvent = (index: number) => {
    if (form.events.length > 1) {
        form.events.splice(index, 1);
    }
};

const submit = () => {
    form.post('/storeSlip', {
        onSuccess: () => {
            toast.success('Dodano kupon', { timeout: 2000 });
            window.location.href = '/';
        },
        onError: (error) => {
            toast.error(error.error || 'Wystąpił błąd');
        },
    });
};

const handleCompetitorAdded = (newItem: any, updatedList: Competitor[]) =>
    (competitors.value = updatedList);
const handleDisciplineAdded = (newItem: any, updatedList: Discipline[]) =>
    (disciplines.value = updatedList);
const handleSelectionAdded = (newItem: any, updatedList: Selection[]) =>
    (selections.value = updatedList);
const handleEventTypeAdded = (newItem: any, updatedList: EventType[]) =>
    (eventTypes.value = updatedList);
const handleFileUpload = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    imagePreview.value = URL.createObjectURL(file);
    analyzeScreenshot(file);
};

const analyzeScreenshot = async (file: File) => {
    isAnalyzing.value = true;
    const formData = new FormData();
    formData.append('screenshot', file);

    try {
        const response = await axios.post('/analyze-bet-screenshot', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        const data = response.data;

        if (data.odds) form.odds = data.odds;
        if (data.stake) form.stake = data.stake;

        form.events = [];
        if (data.bets && data.bets.length > 0) {
            form.events = data.bets.map((e: any) => ({
                home: e.home || null,
                away: e.away || null,
                event_date: null,
                discipline: e.discipline || null,
                event_type: e.event_type || null,
                selection: e.selection || null,
                is_live: false,
            }));
        }
        toast.success('Dane zostały uzupełnione automatycznie!');
    } catch (error) {
        console.log(error);
        toast.error('Nie udało się przeanalizować zdjęcia.');
    } finally {
        isAnalyzing.value = false;
    }
};

const removeImage = () => {
    imagePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};
</script>

<template>
    <div
        class="mx-auto max-w-4xl rounded-xl border border-gray-700 bg-gray-800 p-6 shadow-lg"
    >
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AppLogoIcon class="h-10 w-10" />
                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Bet Tracker
                </h1>
            </div>
            <div
                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1"
            >
                <span
                    class="text-xs font-bold tracking-widest text-emerald-500 uppercase"
                >
                    {{ form.events.length > 1 ? 'Kupon AKO' : 'Kupon Single' }}
                </span>
            </div>
        </header>

        <div
            class="mb-8 overflow-hidden rounded-xl border border-gray-700 bg-gray-900/50"
        >
            <div
                v-if="!imagePreview"
                @click="fileInput?.click()"
                class="group cursor-pointer p-8 text-center transition hover:bg-gray-900"
            >
                <input
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleFileUpload"
                />
                <div class="flex flex-col items-center">
                    <div
                        class="mb-3 rounded-full bg-gray-800 p-4 transition group-hover:scale-110 group-hover:text-emerald-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-white">
                        Wgraj zrzut ekranu kuponu
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        AI automatycznie uzupełni pola formularza
                    </p>
                </div>
            </div>
            <div v-else class="relative flex items-center gap-4 p-4 text-white">
                <img
                    :src="imagePreview"
                    alt="slip preview"
                    class="h-20 w-16 rounded-lg border border-gray-700 object-cover"
                />
                <div class="flex-1">
                    <div
                        v-if="isAnalyzing"
                        class="flex items-center gap-3 text-emerald-400"
                    >
                        <div
                            class="h-4 w-4 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"
                        ></div>
                        <p class="text-sm font-medium">
                            Analizowanie przez AI...
                        </p>
                    </div>
                    <p v-else class="text-sm font-bold">Zdjęcie przetworzone</p>
                </div>
                <button
                    @click="removeImage"
                    class="p-2 text-gray-500 transition-colors hover:text-red-400"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div
                v-for="(event, index) in form.events"
                :key="index"
                class="relative rounded-xl border border-gray-700 bg-gray-900/30 p-5 transition-all"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span
                            class="rounded bg-gray-700 px-2 py-0.5 text-[10px] font-black text-gray-300 uppercase"
                            >Zdarzenie {{ index + 1 }}</span
                        >
                        <div
                            class="flex rounded-lg border border-gray-700 bg-gray-950 p-1 shadow-inner"
                        >
                            <button
                                type="button"
                                @click="event.is_live = false"
                                :class="[
                                    !event.is_live
                                        ? 'bg-gray-700 text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-300',
                                    'rounded-md px-3 py-1 text-[10px] font-black uppercase transition-all',
                                ]"
                            >
                                Prematch
                            </button>
                            <button
                                type="button"
                                @click="event.is_live = true"
                                :class="[
                                    event.is_live
                                        ? 'bg-red-600 text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-300',
                                    'rounded-md px-3 py-1 text-[10px] font-black uppercase transition-all',
                                ]"
                            >
                                Live
                            </button>
                        </div>
                    </div>
                    <button
                        v-if="form.events.length > 1"
                        type="button"
                        @click="removeEvent(index)"
                        class="flex items-center gap-1 text-xs text-red-400 transition-colors hover:text-red-300"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                        </svg>
                        Usuń
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-5">
                    <div
                        class="grid grid-cols-1 items-end gap-3 md:grid-cols-2"
                    >
                        <extendable-autocomplete
                            v-model="event.home"
                            :items="competitors"
                            label="Gospodarz"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/competitor"
                            :multiple="false"
                            @item-added="handleCompetitorAdded"
                        />
                        <extendable-autocomplete
                            v-model="event.away"
                            :items="competitors"
                            label="Gość"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/competitor"
                            :multiple="false"
                            @item-added="handleCompetitorAdded"
                        />
                    </div>

                    <div
                        class="grid grid-cols-1 items-end gap-3 md:grid-cols-2"
                    >
                        <extendable-autocomplete
                            v-model="event.discipline"
                            :items="disciplines"
                            label="Dyscyplina"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/discipline"
                            :multiple="false"
                            @item-added="handleDisciplineAdded"
                        />
                        <div class="flex w-full flex-col">
                            <label
                                class="mb-1.5 text-[10px] font-black tracking-widest text-gray-500 uppercase"
                                >Data meczu</label
                            >
                            <div class="w-full">
                                <VueDatePicker
                                    v-model="event.event_date"
                                    dark
                                    teleport="body"
                                    :enable-time-picker="true"
                                    model-type="yyyy-MM-dd HH:mm:ss"
                                    placeholder="Wybierz datę"
                                    :action-row="{
                                        selectBtnLabel: 'Wybierz',
                                        cancelBtnLabel: 'Zamknij',
                                    }"
                                    class="bet-tracker-datepicker w-full"
                                >
                                    <template #input-icon>
                                        <div class="pl-3 text-gray-500">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </div>
                                    </template>
                                </VueDatePicker>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 items-end gap-3 md:grid-cols-2"
                    >
                        <extendable-autocomplete
                            v-model="event.event_type"
                            :items="eventTypes"
                            label="Rodzaj zakładu"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/eventType"
                            :multiple="false"
                            @item-added="handleEventTypeAdded"
                        />
                        <extendable-autocomplete
                            v-model="event.selection"
                            :items="selections"
                            label="Wybrany typ"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            :add-link="
                                '/addRecord/selection/' + event.event_type?.id
                            "
                            source="api"
                            fetch-url="/get-selections"
                            query-param-name="event_type_id"
                            :multiple="false"
                            :query-param-value="event.event_type?.id"
                            @item-added="handleSelectionAdded"
                        />
                    </div>
                </div>
            </div>

            <button
                type="button"
                @click="addEvent"
                class="w-full rounded-xl border-2 border-dashed border-gray-700 bg-transparent py-4 text-sm font-bold text-gray-500 transition hover:border-emerald-500/50 hover:bg-emerald-500/5 hover:text-emerald-400"
            >
                + Dodaj kolejne zdarzenie
            </button>

            <div
                class="rounded-xl border border-emerald-500/20 bg-emerald-950/20 p-6"
            >
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-gray-400"
                                >Kurs łączny</label
                            >
                            <input
                                v-model="form.odds"
                                type="number"
                                step="0.01"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 p-2.5 text-lg font-black text-white focus:ring-emerald-500"
                                placeholder="0.00"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-bold text-gray-400"
                                >Stawka (PLN)</label
                            >
                            <input
                                v-model="form.stake"
                                type="number"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 p-2.5 text-lg font-black text-white focus:ring-emerald-500"
                                placeholder="0"
                            />
                        </div>
                    </div>
                    <div
                        class="flex flex-col items-center justify-center rounded-xl border border-gray-700 bg-gray-900/50 p-4 md:items-end"
                    >
                        <p
                            class="text-[10px] font-black tracking-widest text-gray-500 uppercase"
                        >
                            Ewentualna wygrana
                        </p>
                        <p class="text-3xl font-black text-emerald-400">
                            {{ potentialWin }} <span class="text-sm">zł</span>
                        </p>
                    </div>
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-emerald-500 py-4 font-black tracking-widest text-white uppercase shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400 disabled:opacity-50"
            >
                {{ form.processing ? 'Zapisywanie...' : 'Zapisz kupon' }}
            </button>
        </form>
    </div>
</template>
<style>
:root {
    --dp-background-color: #111827;
    --dp-text-color: #ffffff;
    --dp-border-color: #374151;
    --dp-menu-border-color: #374151;
    --dp-border-color-hover: #10b981;
    --dp-primary-color: #10b981;
    --dp-icon-color: #6b7280;
    --dp-border-radius: 12px;
}
</style>
<style scoped>
.custom-datetime-input::-webkit-calendar-picker-indicator {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%2310b981" viewBox="0 0 24 24" stroke="%2310b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
    cursor: pointer;
    filter: drop-shadow(0 0 2px rgba(16, 185, 129, 0.2));
    padding: 5px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.custom-datetime-input::-webkit-calendar-picker-indicator:hover {
    background-color: rgba(16, 185, 129, 0.1);
    transform: scale(1.1);
}

input[type='datetime-local'] {
    color-scheme: dark;
}

.dp__input {
    background-color: #111827 !important;
    border: 1px solid #374151 !important;
    padding-left: 40px !important;
    font-size: 0.875rem !important;
    font-family: inherit;
    transition: all 0.2s;
}

.dp__input:focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1) !important;
}

.dp__menu {
    border: 1px solid #374151 !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
    font-family: inherit;
}
</style>
