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
            home: null,
            away: null,
            event_date: '',
            discipline: null,
            event_type: null,
            selection: null,
        }
    ],
    odds: null,
    stake: null,
});

const competitors = ref<Competitor[]>([]);
const disciplines = ref<Discipline[]>([]);
const eventTypes = ref<EventType[]>([]);
const selections = ref<Selection[]>([]);

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

const getCompetitors = () => axios.get('/get-competitors').then(res => competitors.value = res.data);
const getDisciplines = () => axios.get('/get-disciplines').then(res => disciplines.value = res.data);
const getEventTypes = () => axios.get('/get-event-types').then(res => eventTypes.value = res.data);
const getSelections = () => axios.get('/get-selections').then(res => selections.value = res.data);

const addEvent = () => {
    form.events.push({
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

const handleCompetitorAdded = (newItem: any, updatedList: Competitor[]) => competitors.value = updatedList;
const handleDisciplineAdded = (newItem: any, updatedList: Discipline[]) => disciplines.value = updatedList;
const handleSelectionAdded = (newItem: any, updatedList: Selection[]) => selections.value = updatedList;
const handleEventTypeAdded = (newItem: any, updatedList: EventType[]) => eventTypes.value = updatedList;
</script>

<template>
    <div class="rounded-xl border border-gray-700 bg-gray-800 p-6 shadow-lg max-w-4xl mx-auto">
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AppLogoIcon class="h-10 w-10" />
                <h1 class="text-2xl font-bold tracking-tight text-white">Bet Tracker</h1>
            </div>
            <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                <span class="text-emerald-500 text-xs font-bold uppercase tracking-widest">
                    {{ form.events.length > 1 ? 'Kupon AKO' : 'Kupon Single' }}
                </span>
            </div>
        </header>

        <form @submit.prevent="submit" class="space-y-6">

            <div v-for="(event, index) in form.events" :key="index" class="relative p-5 border border-gray-700 rounded-lg bg-gray-900/30 transition-all">
                <div class="flex justify-between items-center mb-4">
                    <span class="bg-gray-700 text-gray-300 px-2 py-0.5 rounded text-xs font-bold">Zdarzenie {{ index + 1 }}</span>
                    <button
                        v-if="form.events.length > 1"
                        type="button"
                        @click="removeEvent(index)"
                        class="text-red-400 hover:text-red-300 text-xs flex items-center gap-1"
                    >
                        Usuń
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                            <label class="mb-1 block text-xs font-medium text-gray-400">Data meczu</label>
                            <input
                                v-model="event.event_date"
                                type="datetime-local"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 text-sm text-white focus:ring-emerald-500"
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
                            @item-added="handleEventTypeAdded"
                        />
                        <extendable-autocomplete
                            v-model="event.selection"
                            :items="selections"
                            label="Wybrany typ"
                            item-title="name"
                            item-value="id"
                            backend-name="name"
                            add-link="/addRecord/selection"
                            @item-added="handleSelectionAdded"
                        />
                    </div>
                </div>
            </div>

            <button
                type="button"
                @click="addEvent"
                class="w-full py-3 border-2 border-dashed border-gray-700 rounded-lg text-gray-400 hover:border-emerald-500/50 hover:text-emerald-400 transition bg-transparent"
            >
                + Dodaj kolejne zdarzenie do kuponu
            </button>

            <div class="bg-emerald-900/10 border border-emerald-500/20 p-6 rounded-xl">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                    Podsumowanie kuponu
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-400">Kurs łączny kuponu</label>
                            <input
                                v-model="form.odds"
                                type="number"
                                step="0.01"
                                placeholder="Np. 3.50"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 text-white focus:ring-emerald-500 text-lg font-bold"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-400">Stawka (PLN)</label>
                            <input
                                v-model="form.stake"
                                type="number"
                                placeholder="100"
                                class="w-full rounded-lg border-gray-700 bg-gray-900 text-white focus:ring-emerald-500 text-lg font-bold"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col justify-center items-center md:items-end p-4 bg-gray-900/40 rounded-lg">
                        <p class="text-gray-400 text-sm uppercase tracking-wider">Ewentualna wygrana</p>
                        <p class="text-4xl font-black text-emerald-500">{{ potentialWin }} <span class="text-xl">zł</span></p>
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
