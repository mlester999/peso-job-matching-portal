<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <Link
        class="block w-full px-4 py-2 text-sm leading-5 text-left text-gray-700 transition duration-150 ease-in-out  hover:bg-gray-100 focus:outline-none focus:bg-gray-100">
    <slot />
    </Link>
</template>
