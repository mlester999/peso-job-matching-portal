<script setup>
import { onMounted, ref } from 'vue'

defineProps({
    modelValue: String,
    withIcon: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['update:modelValue'])

const input = ref(null)

const focus = () => input.value?.focus()

defineExpose({
    input,
    focus
})

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus()
    }
})
</script>

<template>
    <input :class="[
        'py-2 border-gray-400 rounded-md',
        'focus:border-gray-400 focus:ring focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white',
        {
            'px-4': !withIcon,
            'pl-11 pr-4': withIcon,
        },
    ]" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" ref="input" />
</template>
