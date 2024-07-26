<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    index: Number,
    title: String,
    isNoRecord: Boolean,
    addSkill: Function,
    removeSkill: Function
})

const isTapped = ref(false);

// Computed property to capitalize the first letter of the title
const capitalizedTitle = computed(() => {
    if (!props.title) return '';
    return props.title.charAt(0).toUpperCase() + props.title.slice(1);
});

const toggleBorder = () => {
    if (!isTapped.value) {
        props.addSkill(props.title);
    } else {
        props.removeSkill(props.title);
    }

    isTapped.value = !isTapped.value;
};
</script>

<template>
    <!-- component -->
    <div class="bg-white">
        <div v-if="!isNoRecord" @click="toggleBorder" class="container mx-auto">
            <div>
                <div v-if="isTapped"
                    class="flex items-center justify-between px-8 py-4 border border-blue-500 cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 sm:h-9 sm:w-9"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>

                        <div class="flex flex-col items-center mx-5 space-y-1">
                            <h2 class="text-lg font-medium text-gray-700 sm:text-2xl">{{ capitalizedTitle }}</h2>
                        </div>
                    </div>
                </div>

                <div v-else class="flex items-center justify-between px-8 py-4 border cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 sm:h-9 sm:w-9"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>

                        <div class="flex flex-col items-center mx-5 space-y-1">
                            <h2 class="text-lg font-medium text-gray-700 sm:text-2xl">{{ capitalizedTitle }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="container mx-auto">
            <div>
                <div class="max-w-2xl px-8 py-4 mx-auto">
                    <div>
                        <div class=" mx-5 space-y-1">
                            <h2 class="text-lg font-medium text-gray-700 sm:text-xl text-center">Please select a Job
                                Title</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>