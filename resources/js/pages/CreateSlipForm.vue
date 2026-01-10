<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import ExtendableAutocomplete from '@/components/ExtendableAutocomplete.vue';
import { Competitor, Discipline, EventType, Selection } from '@/types';
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';

const toast = useToast();

const form = useForm({
    events: [
        {
            is_live: false,
            home: null,
            away: null,
            event_date: '',
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
        event_date: '',
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
                event_date: '',
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
            class="mx-auto max-w-4xl rounded-xl border border-gray-700 bg-gray-800 p-6 text-white shadow-lg"
        >
            <div class="mb-8">
                <div
                    v-if="!imagePreview"
                    @click="fileInput?.click()"
                    class="group cursor-pointer rounded-xl border-2 border-dashed border-gray-700 bg-gray-900/50 p-8 text-center transition hover:border-emerald-500/50 hover:bg-gray-900"
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
                        <p class="text-sm font-bold">
                            Wgraj zrzut ekranu kuponu
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            AI automatycznie uzupełni pola formularza
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="relative rounded-xl border border-gray-700 bg-gray-900 p-4"
                >
                    <div class="flex items-center gap-4">
                        <img
                            :src="imagePreview"
                            alt="slip preview"
                            class="h-24 w-20 rounded-lg border border-gray-700 object-cover"
                        />
                        <div class="flex-1">
                            <div
                                v-if="isAnalyzing"
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="h-5 w-5 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"
                                ></div>
                                <p class="text-sm font-medium text-emerald-400">
                                    Skanowanie kuponu przez AI...
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-sm font-bold text-gray-200">
                                    Zdjęcie przetworzone
                                </p>
                                <p class="text-xs text-gray-400">
                                    Możesz teraz zweryfikować dane poniżej.
                                </p>
                            </div>
                        </div>
                        <button
                            @click="removeImage"
                            class="p-2 text-gray-500 hover:text-red-400"
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
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div
                v-for="(event, index) in form.events"
                :key="index"
                class="relative rounded-lg border border-gray-700 bg-gray-900/30 p-5 transition-all"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span
                            class="rounded bg-gray-700 px-2 py-0.5 text-xs font-bold text-gray-300"
                            >Zdarzenie {{ index + 1 }}</span
                        >

                        <div
                            class="flex rounded-lg border border-gray-700 bg-gray-950 p-1 shadow-inner"
                        >
                            <button
                                type="button"
                                @click="event.is_live = false"
                                :class="[
                                    'rounded-md px-3 py-1 text-[10px] font-black tracking-tighter uppercase transition-all',
                                    !event.is_live
                                        ? 'bg-gray-700 text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-300',
                                ]"
                            >
                                Prematch
                            </button>
                            <button
                                type="button"
                                @click="event.is_live = true"
                                :class="[
                                    'rounded-md px-3 py-1 text-[10px] font-black tracking-tighter uppercase transition-all',
                                    event.is_live
                                        ? 'bg-red-600 text-white shadow-sm'
                                        : 'text-gray-500 hover:text-gray-300',
                                ]"
                            >
                                <span class="flex items-center gap-1">
                                    <span
                                        v-if="event.is_live"
                                        class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"
                                    ></span>
                                    Live
                                </span>
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

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="grid grid-cols-2 gap-3">
                        <extendable-autocomplete
                            v-model="event.home"
                            :items="competitors"
                            label="Gospodarz"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/competitor"
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
                            @item-added="handleCompetitorAdded"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <extendable-autocomplete
                            v-model="event.discipline"
                            :items="disciplines"
                            label="Dyscyplina"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/discipline"
                            @item-added="handleDisciplineAdded"
                        />
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-gray-400"
                                >Data meczu</label
                            >
                            <input
                                v-model="event.event_date"
                                type="datetime-local"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 text-sm text-white transition-colors focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:col-span-2">
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
                            :add-link="'/addRecord/selection/' + event.event_type?.id"
                            source="api"
                            fetch-url="/get-selections"
                            query-param-name="event_type_id"
                            :query-param-value="event.event_type?.id"
                            @item-added="handleSelectionAdded"
                        />
                    </div>
                </div>
            </div>

            <button
                type="button"
                @click="addEvent"
                class="w-full rounded-lg border-2 border-dashed border-gray-700 bg-transparent py-3 text-gray-400 transition hover:border-emerald-500/50 hover:text-emerald-400"
            >
                + Dodaj kolejne zdarzenie do kuponu
            </button>

            <div
                class="rounded-xl border border-emerald-500/20 bg-emerald-900/10 p-6"
            >
                <h3 class="mb-4 flex items-center gap-2 font-bold text-white">
                    Podsumowanie kuponu
                </h3>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-400"
                                >Kurs łączny kuponu</label
                            >
                            <input
                                v-model="form.odds"
                                type="number"
                                step="0.01"
                                placeholder="Np. 3.50"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 text-lg font-bold text-white focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-400"
                                >Stawka (PLN)</label
                            >
                            <input
                                v-model="form.stake"
                                type="number"
                                placeholder="100"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 text-lg font-bold text-white focus:ring-emerald-500"
                            />
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-center justify-center rounded-lg bg-gray-900/40 p-4 md:items-end"
                    >
                        <p
                            class="text-sm tracking-wider text-gray-400 uppercase"
                        >
                            Ewentualna wygrana
                        </p>
                        <p class="text-4xl font-black text-emerald-500">
                            {{ potentialWin }} <span class="text-xl">zł</span>
                        </p>
                    </div>
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-lg bg-emerald-500 py-4 font-bold text-white shadow-xl transition hover:bg-emerald-600 disabled:bg-emerald-800"
            >
                <span v-if="form.processing">Zapisywanie w bazie...</span>
                <span v-else>Zapisz kupon</span>
            </button>
        </form>
    </div>
</template>
