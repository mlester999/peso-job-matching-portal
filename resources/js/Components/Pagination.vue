<script setup>
import { Link } from "@inertiajs/vue3";
import PreviousButton from "@/Components/PreviousButton.vue";
import NextButton from "@/Components/NextButton.vue";

defineProps({
    employers: Object,
    pagination: Object,
});
</script>

<template>
    <div class="flex items-center mb-4 sm:mb-0">
        <span class="text-sm font-normal text-gray-500">Showing
            <span v-if="employers.from && employers.to" class="font-semibold text-gray-900">{{
                employers.from }} -
                {{ employers.to }}</span>

            <span v-else class="font-semibold text-gray-900">0 - 0</span>
            of
            <span class="font-semibold text-gray-900">{{
                employers.total
                }}</span></span>
    </div>
    <div class="flex items-center space-x-3">
        <li class="list-none" v-if="pagination.links[0].label !== 'Previous'">
            <PreviousButton element="button" :link="pagination.links[0].url" :disabled="true">
                Previous
            </PreviousButton>
        </li>

        <li class="list-none" v-for="(link, index) in pagination.links" :key="index"
            :class="{ active: link.label === pagination.current_page }">
            <Link :href="link.url" v-if="
                link.label !== '...' &&
                link.label !== 'Previous' &&
                link.label !== 'Next' &&
                pagination.links.length !== 1
            " class="inline-flex items-center justify-center flex-1 px-3 py-2 text-sm text-center text-gray-900 rounded-lg hover:bg-gray-300 focus:ring-4 focus:ring-primary-300"
                :class="{
                    'bg-gray-200 font-semibold':
                        link.label === pagination.current_page,
                }" @click.prevent="$inertia.visit(link.url)">{{ link.label }}</Link>

            <button v-else-if="link.label === '...'"
                class="inline-flex items-center justify-center flex-1 px-1 py-2 text-sm font-medium text-center text-gray-900 rounded-lg"
                disabled>
                ...
            </button>

            <PreviousButton v-else-if="link.label === 'Previous'" element="link" :link="link.url">
                {{ link.label }}
            </PreviousButton>

            <NextButton v-else-if="link.label === 'Next'" element="link" :link="link.url">
                {{ link.label }}
            </NextButton>

            <button v-else
                class="inline-flex items-center justify-center flex-1 px-3 py-2 text-sm text-center text-gray-900 rounded-lg bg-gray-200 font-semibold"
                disabled>
                {{ link.label }}
            </button>
        </li>

        <li class="list-none" v-if="
            pagination.links[pagination.links.length - 1].label !== 'Next'
        ">
            <NextButton element="button" :link="pagination.links[pagination.links.length - 1].url" :disabled="true">
                Next
            </NextButton>
        </li>
    </div>
</template>