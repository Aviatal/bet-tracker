<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLogoIcon from '@/Components/AppLogoIcon.vue';
import { createBet } from '@/routes';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const props = defineProps<{
    bets: Array<{
        id: number;
        event: string;
        event_type: string;
        selection: string;
        odds: number;
        stake: number;
        status: 'pending' | 'won' | 'lost';
    }>;
}>();
onMounted(() => getStats());

const searchQuery = ref('');

const filteredBets = computed(() => {
    return props.bets?.filter((bet) =>
        bet.event.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});
const stats = ref<{
    profit: number;
    accuracy: number;
}>({ profit: 0, accuracy: 0 });
const getStats = () => {
    axios
        .get('/get-stats')
        .then((response) => (stats.value = response.data))
        .catch((error) => {
            console.log(error);
            toast.error('Nie udało się pobrać statystyk');
        });
};

watch(props, () => {
    console.log('watch')
    getStats();
}, { deep: true })

const changeBetStatus = (id: number, status: string) => {
    router.patch(
        '/change-bet-status/' + id,
        {currentStatus: status}, {
            preserveScroll: true,
            onSuccess: () => toast.success(`Zmieniono status`),
            onError: () => toast.error('Wystąpił błąd podczas zmiany statusu'),
        }
    );
};
</script>

<template>
    <Head title="Moje Typy" />

    <div class="min-h-screen bg-gray-900 p-6 text-white">
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AppLogoIcon class="h-10 w-10" />
                <h1 class="text-2xl font-bold tracking-tight">Bet Tracker</h1>
            </div>
            <Link
                :href="createBet()"
                class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold transition hover:bg-emerald-600"
            >
                + Dodaj Typ
            </Link>
        </header>

        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <p class="text-sm text-gray-400">Całkowity Profit</p>
                <p
                    :class="
                        'text-3xl font-bold ' +
                        (stats.profit > 0 ? 'text-emerald-400' : 'text-red-400')
                    "
                >
                    {{ stats.profit }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <p class="text-sm text-gray-400">Skuteczność</p>
                <p class="text-3xl font-bold text-purple-400">
                    {{ stats.accuracy }}%
                </p>
            </div>
        </div>

        <div class="mb-6">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Szukaj meczu lub dyscypliny..."
                class="w-full rounded-lg border-gray-700 bg-gray-800 text-white focus:border-emerald-500 focus:ring-emerald-500"
            />
        </div>

        <div
            class="overflow-hidden rounded-xl border border-gray-700 bg-gray-800"
        >
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr
                        class="bg-gray-750 border-b border-gray-700 text-sm text-gray-400 uppercase"
                    >
                        <th class="p-4">Wydarzenie</th>
                        <th class="p-4">Zdarzenie</th>
                        <th class="p-4">Typ</th>
                        <th class="p-4 text-center">Kurs</th>
                        <th class="p-4 text-center">Stawka</th>
                        <th class="p-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <tr
                        v-for="bet in filteredBets"
                        :key="bet.id"
                        class="hover:bg-gray-750 transition"
                    >
                        <td class="p-4 font-medium">
                            {{ bet.event }}
                        </td>
                        <td class="p-4 text-gray-300">{{ bet.event_type }}</td>
                        <td class="p-4 text-gray-300">{{ bet.selection }}</td>
                        <td class="p-4 text-center font-mono">
                            {{ bet.odds }}
                        </td>
                        <td class="p-4 text-center">{{ bet.stake }} zł</td>
                        <td class="p-4 text-right cursor-pointer">
                            <span
                                :class="{
                                    'rounded bg-emerald-500/10 px-2 py-1 text-xs font-bold text-emerald-500 uppercase':
                                        bet.status === 'won',
                                    'rounded bg-red-500/10 px-2 py-1 text-xs font-bold text-red-500 uppercase':
                                        bet.status === 'lost',
                                    'rounded bg-yellow-500/10 px-2 py-1 text-xs font-bold text-yellow-500 uppercase':
                                        bet.status === 'pending',
                                }"
                                @click="changeBetStatus(bet.id, bet.status)"
                            >
                                {{ bet.status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
