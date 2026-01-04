<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

interface Props {
    items: any[];
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
const inputRef = ref<HTMLInputElement | null>(null);
const highlightedIndex = ref(0);
const selectedItems = ref<any[]>([]);

onMounted(() => {
    if (props.modelValue) {
        selectedItems.value = props.multiple
            ? Array.isArray(props.modelValue)
                ? props.modelValue
                : [props.modelValue]
            : [props.modelValue];
    }
});

const filteredItems = computed(() => {
    if (!search.value) return props.items;
    return props.items.filter((item) =>
        item[props.itemTitle]
            .toLowerCase()
            .includes(search.value.toLowerCase()),
    );
});

const showAddOption = computed(() => {
    if (!search.value) return false;
    return !props.items.some(
        (item) =>
            item[props.itemTitle].toLowerCase() ===
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
        const response = await axios.post(props.addLink, { postData });
        const newItem = response.data;

        emit('item-added', newItem, [...props.items, newItem]);
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
                :placeholder="selectedItems.length === 0 ? 'Szukaj...' : ''"
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
            v-if="isOpen && (filteredItems.length > 0 || showAddOption)"
            class="absolute z-[100] mt-2 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-700 bg-gray-800 py-1 shadow-2xl"
        >
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
                    'bg-gray-700': highlightedIndex === filteredItems.length,
                }"
                @mousedown="addNewItem"
                @mouseenter="highlightedIndex = filteredItems.length"
            >
                + Dodaj: <span class="font-bold">"{{ search }}"</span>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.autocomplete-container {
    position: relative;
    width: 100%;
    font-family: sans-serif;
}

.input-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 40px;
    cursor: text;
    align-items: center;
}

.input-wrapper:focus-within {
    border-color: #2196f3;
    box-shadow: 0 0 0 1px #2196f3;
}

input {
    border: none;
    outline: none;
    flex: 1;
    min-width: 50px;
    font-size: 14px;
}

.chip {
    background: #e0e0e0;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.remove-chip {
    cursor: pointer;
    font-weight: bold;
}

.dropdown-list {
    position: absolute;
    top: 105%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    list-style: none;
    margin: 0;
    padding: 0;
    z-index: 100;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dropdown-list li {
    padding: 10px;
    cursor: pointer;
}

.dropdown-list li.is-highlighted {
    background-color: #f0f0f0;
}

.add-option {
    border-top: 1px solid #eee;
    color: #2196f3;
}

.clear-btn {
    cursor: pointer;
    padding: 0 5px;
    color: #999;
}
</style>
