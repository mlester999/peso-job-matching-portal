<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import PageFooter from '@/Components/PageFooter.vue'
import Button from '@/Components/Button.vue'
import { toggleDarkMode, isDark } from '@/Composables'

defineProps({
    title: String
})
</script>

<template>

    <Head :title="title" />

    <div class="flex flex-col items-center justify-center min-h-screen gap-4 py-6 bg-gray-100">
        <div class="flex-shrink-0">
            <Link href="/">
            <ApplicationLogo class="w-32 h-32" />
            </Link>
        </div>

        <div class="max-w-lg text-center">
            <h1 class="text-4xl">PESO Job Matching Portal</h1>
        </div>

        <main class="flex items-center flex-1 w-full sm:max-w-md">
            <div class="w-full px-6 py-4 overflow-hidden bg-white shadow-md  sm:rounded-lg">
                <slot />
            </div>
        </main>
    </div>
</template>
