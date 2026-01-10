<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

interface Props {
    source?: 'api' | 'local';
    items?: any[];
    fetchUrl?: string;
    queryParamName?: string;
    queryParamValue?: any;
    addLink: string;
    backendName: string;
    itemTitle?: string;
    itemValue?: string;
    additionalData?: Record<string, any>;
    multiple?: boolean;
    label?: string;
    modelValue?: any | any[];
}

const props = withDefaults(defineProps<Props>(), {
    source: 'local',
    items: () => [],
    itemTitle: 'name',
    itemValue: 'id',
    additionalData: () => ({}),
    multiple: true,
    label: 'Wybierz lub dodaj...',
    modelValue: null,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
    (e: 'item-added', newItem: any, updatedList: any[]): void;
}>();

const search = ref('');
const isOpen = ref(false);
const isLoading = ref(false);
const inputRef = ref<HTMLInputElement | null>(null);
const highlightedIndex = ref(0);
const selectedItems = ref<any[]>([]);
const remoteItems = ref<any[]>([]);

const dataSource = computed(() => {
    return props.source === 'api' ? remoteItems.value : props.items;
});

const fetchItems = async () => {
    if (
        props.source !== 'api' ||
        !props.fetchUrl ||
        (props.queryParamName && !props.queryParamValue)
    )
        return;

    isLoading.value = true;
    try {
        const response = await axios.get(
            `${props.fetchUrl}?${props.queryParamName}=${props.queryParamValue}`,
        );
        remoteItems.value = Array.isArray(response.data)
            ? response.data
            : response.data.items || [];
    } catch (error) {
        console.error('Błąd pobierania:', error);
        remoteItems.value = [];
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    if (props.source === 'api') {
        fetchItems();
    }

    if (props.modelValue) {
        selectedItems.value = props.multiple
            ? Array.isArray(props.modelValue)
                ? props.modelValue
                : [props.modelValue]
            : [props.modelValue];
    }
});

watch(
    [() => props.fetchUrl, () => props.queryParamValue],
    () => {
        console.log('watch')
        if (props.source === 'api') fetchItems();
    },
);

const filteredItems = computed(() => {
    if (!search.value) return dataSource.value;
    return dataSource.value.filter((item) =>
        item[props.itemTitle]
            ?.toString()
            .toLowerCase()
            .includes(search.value.toLowerCase()),
    );
});

const showAddOption = computed(() => {
    if (!search.value || isLoading.value) return false;
    return !dataSource.value.some(
        (item) =>
            item[props.itemTitle]?.toString().toLowerCase() ===
            search.value.trim().toLowerCase(),
    );
});

const selectItem = (item: any) => {
    if (props.multiple) {
        if (
            !selectedItems.value.some(
                (s) => s[props.itemValue] === item[props.itemValue],
            )
        ) {
            selectedItems.value.push(item);
        }
    } else {
        selectedItems.value = [item];
        closeDropdown();
    }
    search.value = '';
    emit('update:modelValue', props.multiple ? selectedItems.value : item);
};

const removeItem = (item: any) => {
    selectedItems.value = selectedItems.value.filter(
        (s) => s[props.itemValue] !== item[props.itemValue],
    );
    emit('update:modelValue', props.multiple ? selectedItems.value : null);
};

const addNewItem = async () => {
    const query = search.value.trim();
    if (!query) return;

    try {
        const postData = {
            [props.backendName]: query,
            ...props.additionalData,
        };
        const response = await axios.post(props.addLink, postData);
        const newItem = response.data;

        if (props.source === 'api') {
            remoteItems.value.push(newItem);
        }

        emit('item-added', newItem, dataSource.value);
        selectItem(newItem);
    } catch (error) {
        console.error('Błąd podczas dodawania:', error);
    }
};

const handleEnter = () => {
    if (
        highlightedIndex.value >= 0 &&
        highlightedIndex.value < filteredItems.value.length
    ) {
        selectItem(filteredItems.value[highlightedIndex.value]);
    } else if (showAddOption.value) {
        addNewItem();
    }
};

const moveHighlight = (direction: number) => {
    const max = filteredItems.value.length + (showAddOption.value ? 1 : 0);
    highlightedIndex.value = (highlightedIndex.value + direction + max) % max;
};

const closeDropdown = () => {
    isOpen.value = false;
    highlightedIndex.value = 0;
};

const focusInput = () => inputRef.value?.focus();

const clearAll = () => {
    selectedItems.value = [];
    search.value = '';
    emit('update:modelValue', props.multiple ? [] : null);
};

const vClickOutside = {
    mounted(el: any, binding: any) {
        el.clickOutsideEvent = (event: Event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el: any) {
        document.removeEventListener('click', el.clickOutsideEvent);
    },
};
</script>

<template>
    <div class="relative w-full" v-click-outside="closeDropdown">
        <label
            v-if="label"
            class="mb-1 block text-sm font-medium text-gray-400"
            >{{ label }}</label
        >

        <div
            class="flex min-h-[42px] w-full cursor-text flex-wrap items-center gap-2 rounded-lg border border-gray-700 bg-gray-900 p-2 transition-colors focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500"
            :class="{ 'pointer-events-none opacity-70': isLoading }"
            @click="focusInput"
        >
            <div
                v-for="item in selectedItems"
                :key="item[itemValue]"
                class="flex items-center gap-1 rounded border border-emerald-500/30 bg-emerald-500/20 px-2 py-0.5 text-sm font-medium text-emerald-400"
            >
                {{ item[itemTitle] }}
                <span
                    class="ml-1 cursor-pointer text-lg leading-none hover:text-emerald-200"
                    @click.stop="removeItem(item)"
                    >×</span
                >
            </div>

            <input
                ref="inputRef"
                v-model="search"
                type="text"
                :disabled="isLoading"
                :placeholder="
                    selectedItems.length === 0
                        ? isLoading
                            ? 'Ładowanie...'
                            : 'Szukaj...'
                        : ''
                "
                class="min-w-[50px] flex-1 border-none bg-transparent p-0 text-sm text-white outline-none placeholder:text-gray-600 focus:ring-0"
                @focus="isOpen = true"
                @keydown.down.prevent="moveHighlight(1)"
                @keydown.up.prevent="moveHighlight(-1)"
                @keydown.enter.prevent="handleEnter"
                @keydown.esc="closeDropdown"
            />

            <span
                v-if="selectedItems.length > 0 || search"
                class="cursor-pointer px-1 text-gray-500 hover:text-white"
                @click="clearAll"
                >×</span
            >
        </div>

        <ul
            v-if="
                isOpen &&
                (filteredItems.length > 0 || showAddOption || isLoading)
            "
            class="absolute z-100 mt-2 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-700 bg-gray-800 py-1 shadow-2xl"
        >
            <li v-if="isLoading" class="px-4 py-2 text-sm text-gray-500 italic">
                Pobieranie danych...
            </li>

            <template v-else>
                <li
                    v-for="(item, index) in filteredItems"
                    :key="item[itemValue]"
                    :class="[
                        'cursor-pointer px-4 py-2 text-sm transition-colors',
                        index === highlightedIndex
                            ? 'bg-emerald-500 text-white'
                            : 'text-gray-300 hover:bg-gray-700',
                    ]"
                    @mousedown="selectItem(item)"
                    @mouseenter="highlightedIndex = index"
                >
                    {{ item[itemTitle] }}
                </li>

                <li
                    v-if="showAddOption"
                    class="cursor-pointer border-t border-gray-700 px-4 py-2 text-sm text-emerald-400 hover:bg-gray-700"
                    :class="{
                        'bg-gray-700':
                            highlightedIndex === filteredItems.length,
                    }"
                    @mousedown="addNewItem"
                    @mouseenter="highlightedIndex = filteredItems.length"
                >
                    + Dodaj: <span class="font-bold">"{{ search }}"</span>
                </li>
            </template>
        </ul>
    </div>
</template>
