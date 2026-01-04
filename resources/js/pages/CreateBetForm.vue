<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import ExtendableAutocomplete from '@/components/ExtendableAutocomplete.vue';
import { Competitor, Discipline, EventType, Selection } from '@/types';
import { onMounted, ref } from 'vue';
import axios from 'axios';

const toast = useToast();
const form = useForm({
    home: null,
    away: null,
    event_date: '',
    discipline: null,
    event_type: null,
    selection: null,
    odds: null,
    stake: null,
});
const competitors = ref<Competitor[]>([]);
const disciplines = ref<Discipline[]>([]);
const eventTypes = ref<EventType[]>([]);
const selections = ref<Selection[]>([]);

onMounted(() => {
    //TODO: Zamienić na jeden request
    getCompetitors();
    getDisciplines();
    getEventTypes();
    getSelections();
});

const getCompetitors = () => {
    axios
        .get('/get-competitors')
        .then((response) => (competitors.value = response.data))
        .catch((error) => {
            console.error(error);
            toast.error('Wystąpił błąd podczas pobierania uczestników');
        });
};
const getDisciplines = () => {
    axios
        .get('/get-disciplines')
        .then((response) => (disciplines.value = response.data))
        .catch((error) => {
            console.error(error);
            toast.error('Wystąpił błąd podczas pobierania dyscyplin');
        });
};

const getEventTypes = () => {
    axios
        .get('/get-event-types')
        .then((response) => (eventTypes.value = response.data))
        .catch((error) => {
            console.error(error);
            toast.error('Wystąpił błąd podczas pobierania rodzajów zakładów');
        });
};
const getSelections = () => {
    axios
        .get('/get-selections')
        .then((response) => (selections.value = response.data))
        .catch((error) => {
            console.error(error);
            toast.error('Wystąpił błąd podczas pobierania typów');
        });
};
const submit = () => {
    form.post('/storeBet', {
        onSuccess: () => {
            toast.success('Dodano typ', { timeout: 2000 });
            window.location.href = '/';
        },
        onError: (error) => {
            toast.error(error.error)
        },
    });
};

const handleCompetitorAdded = (
    newItem: Competitor,
    updatedList: Competitor[],
) => {
    competitors.value = updatedList;
};

const handleDisciplineAdded = (
    newItem: Discipline,
    updatedList: Discipline[],
) => {
    console.log(updatedList);
    disciplines.value = updatedList;
};

const handleSelectionAdded = (newItem: Selection, updatedList: Selection[]) => {
    selections.value = updatedList;
};

const handleEventTypeAdded = (newItem: EventType, updatedList: EventType[]) => {
    eventTypes.value = updatedList;
};
</script>

<template>
    <div class="rounded-xl border border-gray-700 bg-gray-800 p-6 shadow-lg">
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AppLogoIcon class="h-10 w-10" />
                <h1 class="text-2xl font-bold tracking-tight">Bet Tracker</h1>
            </div>
        </header>

        <h2 class="mb-6 text-xl font-bold text-white">Dodaj nowy typ</h2>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <extendable-autocomplete
                        v-model="form.home"
                        :items="competitors"
                        label="Gospodarz"
                        item-title="name"
                        item-value="id"
                        backend-name="name"
                        add-link="/addRecord/competitor"
                        :multiple="false"
                        @item-added="handleCompetitorAdded"
                    />
                </div>
                <div>
                    <extendable-autocomplete
                        v-model="form.away"
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
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <extendable-autocomplete
                        v-model="form.discipline"
                        :items="disciplines"
                        label="Wyszukaj lub dodaj dyscyplinę"
                        item-title="name"
                        item-value="id"
                        backend-name="name"
                        add-link="/addRecord/discipline"
                        :multiple="false"
                        @item-added="handleDisciplineAdded"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-400"
                        >Data i godzina</label
                    >
                    <input
                        v-model="form.event_date"
                        type="datetime-local"
                        class="w-full rounded-lg border-gray-700 bg-gray-900 text-white focus:ring-emerald-500"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <extendable-autocomplete
                        v-model="form.event_type"
                        :items="eventTypes"
                        label="Rodzaj zakładu"
                        item-title="name"
                        item-value="id"
                        backend-name="name"
                        add-link="/addRecord/eventType"
                        :multiple="false"
                        @item-added="handleEventTypeAdded"
                    />
                </div>
                <div>
                    <extendable-autocomplete
                        v-model="form.selection"
                        :items="selections"
                        label="Typ"
                        item-title="name"
                        item-value="id"
                        backend-name="name"
                        add-link="/addRecord/selection"
                        :multiple="false"
                        @item-added="handleSelectionAdded"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-400"
                        >Kurs</label
                    >
                    <input
                        v-model="form.odds"
                        type="number"
                        step="0.01"
                        placeholder="1.85"
                        class="w-full rounded-lg border-gray-700 bg-gray-900 text-white focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-400"
                        >Stawka</label
                    >
                    <input
                        v-model="form.stake"
                        type="number"
                        placeholder="100"
                        class="w-full rounded-lg border-gray-700 bg-gray-900 text-white focus:ring-emerald-500"
                    />
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="mt-4 w-full rounded-lg bg-emerald-500 py-3 font-bold text-white transition hover:bg-emerald-600 disabled:bg-emerald-800"
            >
                <span v-if="form.processing">Zapisywanie...</span>
                <span v-else>Zapisz typ</span>
            </button>
        </form>
    </div>
</template>
