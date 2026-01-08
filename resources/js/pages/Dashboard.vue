<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { createSlip } from '@/routes';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

interface Bet {
    id: number;
    home: { name: string };
    away: { name: string };
    event_type: { name: string };
    selection: { name: string };
}

interface Slip {
    id: number;
    odds: number;
    stake: number;
    status: 'pending' | 'won' | 'lost';
    played: boolean;
    bets: Bet[];
}

const toast = useToast();
const props = defineProps<{
    slips: Slip[];
}>();

const searchQuery = ref('');
const editingStakeId = ref<number | null>(null);
const editStakeValue = ref<number>(0);

const filteredSlips = computed(() => {
    return props.slips?.filter((slip) =>
        slip.bets.some((bet) =>
            `${bet.home?.name} ${bet.away?.name}`
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()),
        ),
    );
});

const stats = ref({
    profit: 0,
    total_profit: 0,
    accuracy: 0,
    total_accuracy: 0,
    expert_pay: 0,
});

const getStats = () => {
    axios
        .get('/get-stats')
        .then((response) => (stats.value = response.data))
        .catch(() => toast.error('Nie udało się pobrać statystyk'));
};

onMounted(() => getStats());
watch(
    () => props.slips,
    () => getStats(),
    { deep: true },
);

const changeSlipStatus = (id: number, status: string) => {
    router.patch(
        `/change-slip-status/${id}`,
        { currentStatus: status },
        {
            preserveScroll: true,
            onSuccess: () => toast.success(`Zaktualizowano status kuponu`),
        },
    );
};

// Funkcje do obsługi edycji stawki
const startEditingStake = (slip: Slip) => {
    editingStakeId.value = slip.id;
    editStakeValue.value = slip.stake;
};

const cancelEdit = () => {
    editingStakeId.value = null;
};

const updateStake = (id: number) => {
    router.patch(
        `/update-slip-stake/${id}`,
        { stake: editStakeValue.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Stawka została zaktualizowana');
                editingStakeId.value = null;
            },
            onError: () => toast.error('Błąd podczas aktualizacji stawki'),
        },
    );
};

const deleteSlip = (id: number) => {
    router.delete(`/delete-slip/${id}`, {
        onSuccess: () => toast.success('Kupon został usunięty'),
        onError: () => toast.error('Wystąpił błąd podczas usuwania'),
    });
};

const toggleSlipPlayed = (slip: Slip) => {
    router.patch(
        `/toggle-slip-played/${slip.id}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                toast.success(
                    slip.played
                        ? 'Oznaczono jako niezagrany'
                        : 'Oznaczono jako zagrany',
                ),
        },
    );
};
</script>

<template>
    <Head title="Moje Kupony" />

    <div class="min-h-screen bg-gray-900 p-6 text-white">
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <AppLogoIcon class="h-10 w-10" />
                <h1 class="text-2xl font-bold tracking-tight">Bet Tracker</h1>
            </div>
            <Link
                :href="createSlip()"
                class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600"
            >
                + Dodaj Kupon
            </Link>
        </header>
        <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div
                class="rounded-xl border-2 border-emerald-500/30 bg-gray-800 p-6 shadow-lg shadow-emerald-500/5"
            >
                <div class="mb-2 flex items-center justify-between">
                    <p
                        class="text-xs font-black tracking-widest text-emerald-500 uppercase"
                    >
                        Realny Portfel
                    </p>
                    <span
                        class="rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white"
                        >LIVE</span
                    >
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Profit</p>
                        <p
                            :class="[
                                'text-3xl font-black',
                                stats.profit >= 0
                                    ? 'text-emerald-400'
                                    : 'text-red-400',
                            ]"
                        >
                            {{ stats.profit }} zł
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">Skuteczność</p>
                        <p class="text-xl font-bold text-purple-400">
                            {{ stats.accuracy }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <p
                    class="mb-2 text-xs font-black tracking-widest text-gray-500 uppercase"
                >
                    Wszystkie propozycje
                </p>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Profit</p>
                        <p
                            :class="[
                                'text-3xl font-black',
                                stats.total_profit >= 0
                                    ? 'text-emerald-400'
                                    : 'text-red-400',
                            ]"
                        >
                            {{ stats.total_profit }} zł
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">Skuteczność</p>
                        <p class="text-xl font-bold text-gray-400">
                            {{ stats.total_accuracy }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-700 bg-gray-800 p-6">
                <p
                    class="mb-2 text-xs font-black tracking-widest text-purple-500 uppercase"
                >
                    Gaża eksperta
                </p>
                <p class="text-3xl font-black text-purple-400">
                    {{ stats.expert_pay }} zł
                </p>
            </div>
        </div>

        <div class="space-y-6">
            <div
                v-for="slip in filteredSlips"
                :key="slip.id"
                :class="[
                    'group/slip overflow-hidden rounded-2xl border shadow-xl transition',
                    slip.played
                        ? 'border-gray-700 bg-gray-800'
                        : 'border-dashed border-gray-600 bg-gray-800/40 opacity-80',
                ]"
            >
                <div
                    class="bg-gray-750/50 flex flex-wrap items-center justify-between border-b border-gray-700 p-5"
                >
                    <div class="flex items-center gap-4">
                        <button
                            @click="toggleSlipPlayed(slip)"
                            :class="[
                                'flex items-center gap-2 rounded-lg px-3 py-1.5 text-[10px] font-black uppercase transition',
                                slip.played
                                    ? 'border border-emerald-500/40 bg-emerald-500/20 text-emerald-400'
                                    : 'border border-gray-600 bg-gray-700 text-gray-400',
                            ]"
                        >
                            <div
                                :class="[
                                    'h-2 w-2 rounded-full',
                                    slip.played
                                        ? 'animate-pulse bg-emerald-500'
                                        : 'bg-gray-500',
                                ]"
                            ></div>
                            {{ slip.played ? 'PLAYED' : 'NOT PLAYED' }}
                        </button>

                        <div class="h-8 w-px bg-gray-700"></div>

                        <div class="flex flex-col">
                            <span
                                class="text-[10px] font-black tracking-widest text-gray-500 uppercase"
                                >ID</span
                            >
                            <span class="font-mono text-sm font-bold"
                                >#{{ slip.id }}</span
                            >
                        </div>
                        <div class="h-8 w-px bg-gray-700"></div>
                        <span
                            v-if="slip.bets.length > 1"
                            class="rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-xs font-black text-emerald-400"
                            >AKO ({{ slip.bets.length }})</span
                        >
                        <span
                            v-else
                            class="rounded-md bg-gray-700 px-2 py-1 text-xs font-black text-gray-400"
                            >SINGLE</span
                        >

                        <button
                            @click="deleteSlip(slip.id)"
                            class="ml-2 p-2 text-gray-500 opacity-0 transition-colors group-hover/slip:opacity-100 hover:text-red-500"
                            title="Usuń kupon"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
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
                        </button>
                    </div>

                    <div class="flex items-center gap-8">
                        <div class="text-right">
                            <p
                                class="text-[10px] font-bold text-gray-500 uppercase"
                            >
                                Kurs
                            </p>
                            <p class="text-lg font-black text-white">
                                {{ slip.odds }}
                            </p>
                        </div>

                        <div class="min-w-[100px] text-right">
                            <p
                                class="text-[10px] font-bold text-gray-500 uppercase"
                            >
                                Stawka
                            </p>
                            <div
                                v-if="editingStakeId === slip.id"
                                class="mt-1 flex items-center gap-1"
                            >
                                <input
                                    v-model="editStakeValue"
                                    type="number"
                                    class="w-20 rounded border-gray-600 bg-gray-900 p-1 text-sm text-white focus:ring-emerald-500"
                                    @keyup.enter="updateStake(slip.id)"
                                    @keyup.esc="cancelEdit"
                                    autofocus
                                />
                                <button
                                    @click="updateStake(slip.id)"
                                    class="text-emerald-500 hover:text-emerald-400"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <p
                                v-else
                                @click="startEditingStake(slip)"
                                class="flex cursor-pointer items-center justify-end gap-1 text-lg font-black text-white transition hover:text-emerald-400"
                                title="Kliknij, aby edytować"
                            >
                                {{ slip.stake }} zł
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3 w-3 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    />
                                </svg>
                            </p>
                        </div>

                        <div class="min-w-[100px] text-right">
                            <button
                                @click="changeSlipStatus(slip.id, slip.status)"
                                :class="{
                                    'w-full rounded-lg px-3 py-2 text-xs font-black tracking-tighter uppercase transition': true,
                                    'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20':
                                        slip.status === 'won',
                                    'bg-red-500 text-white shadow-lg shadow-red-500/20':
                                        slip.status === 'lost',
                                    'bg-yellow-500 text-gray-900':
                                        slip.status === 'pending',
                                }"
                            >
                                {{ slip.status }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900/20">
                    <div
                        v-for="(bet, index) in slip.bets"
                        :key="bet.id"
                        class="group flex items-center justify-between border-b border-gray-700/30 p-4 transition last:border-0 hover:bg-gray-700/20"
                    >
                        <div class="flex items-center gap-4">
                            <span class="font-mono text-xs text-gray-600"
                                >{{ index + 1 }}.</span
                            >
                            <div>
                                <p
                                    class="text-sm font-bold text-gray-200 transition group-hover:text-white"
                                >
                                    {{ bet.home?.name }} – {{ bet.away?.name }}
                                </p>
                                <p
                                    class="text-[11px] font-medium text-gray-500 uppercase"
                                >
                                    {{ bet.event_type?.name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span
                                class="mb-1 block text-[10px] font-bold text-gray-500 uppercase"
                                >Wybrany Typ</span
                            >
                            <span
                                class="rounded bg-gray-700 px-2 py-1 text-xs font-bold text-emerald-400"
                            >
                                {{ bet.selection?.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
