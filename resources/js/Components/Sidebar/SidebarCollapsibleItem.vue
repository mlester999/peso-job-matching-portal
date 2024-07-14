<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    href: String,
    title: String,
    active: {
        type: Boolean,
        default: false,
    },
    external: {
        type: Boolean,
        default: false,
    }
})

const { external } = props

const Tag = external ? 'a' : Link
</script>

<template>
    <li :class="[
        'relative leading-8 m-0 pl-6',
        'before:block before:w-4 before:h-0 before:absolute before:left-0 before:top-4 before:border-t-2 before:border-t-gray-200 before:-mt-0.5',
        'last:before:bg-white last:before:h-auto last:before:top-4 last:before:bottom-0'
    ]">
        <component :is="Tag" :href="href" v-bind="$attrs" :class="[
            'transition-colors hover:text-gray-900',
            {
                'text-gray-900': active,
                'text-gray-500': !active,
            },
        ]">
            {{ title }}
        </component>
    </li>
</template>
