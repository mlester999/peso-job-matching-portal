<script setup>
import { computed } from 'vue'

const emit = defineEmits(['update:checked'])

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        default: null,
    },
})

const proxyChecked = computed({
    get() {
        return props.checked
    },

    set(val) {
        emit('update:checked', val)
    },
})
</script>

<template>
    <input type="checkbox" :value="value" v-model="proxyChecked"
        class="text-blue-500 border-gray-300 rounded focus:border-blue-300 focus:ring focus:ring-blue-500" />
</template>
