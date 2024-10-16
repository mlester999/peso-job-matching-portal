<script setup>
import { ref, watch, onMounted } from 'vue';
import Badge from './Badge.vue';

const props = defineProps({
    id: String,
    modelValue: String,
    label: String,
    type: String,
    placeholder: String,
    disabled: Boolean,
    error: String,
    class: String,
    isContactNumber: Boolean,
    isNumber: Boolean,
    isMultiline: Boolean,
    min: String,
    max: String,
    isTextArea: Boolean
});

const previewSkills = ref(props.modelValue);

const addTag = (event) => {
    if (event.code == 'Comma' || event.code == 'Enter') {
        event.preventDefault();

        let val = event.target.value.trim()

        if (val.length > 0) {
            previewSkills.value.push(val);
            event.target.value = ''
            emit('update:modelValue', previewSkills.value);
        }
    }
}

const removeTag = (index) => {
    previewSkills.value.splice(index, 1);
    emit('update:modelValue', previewSkills.value);
}

const removeLastTag = (event) => {
    if (event.target.value.length === 0) {
        removeTag(previewSkills.value.length - 1)
        emit('update:modelValue', previewSkills.value);
    }
}

const emit = defineEmits(['update:modelValue']);

const modelValue = ref(props.modelValue);

const checkDigit = (event) => {
    if (props.isNumber && event.key.length === 1 && isNaN(Number(event.key))) {
        event.preventDefault();
    }

    if (!isNaN(Number(event.key)) && event.key < 1) {
        event.key = 1;
    } else if (!isNaN(Number(event.key)) && event.key > 100) {
        event.key = 100;
    }
};

const handleInput = (event) => {
    let value = event.target.value;
    if (props.isContactNumber) {
        // Allow only numbers and restrict to 10 digits
        if (/^\d*$/.test(value) && value.length <= 10) {
            modelValue.value = value;
            emit('update:modelValue', value);
        } else {
            // Remove any invalid characters and trim to 10 digits
            value = value.slice(0, 10).replace(/\D/g, '');
            modelValue.value = value;
            emit('update:modelValue', value);
            event.target.value = value;
        }
    } else if (props.isNumber) {
        if (Number(value) < 1) {
            modelValue.value = '';
            emit('update:modelValue', '');
            event.target.value = '';
        } else if (Number(value) > 100) {
            modelValue.value = 100;
            emit('update:modelValue', 100);
            event.target.value = 100;
        } else {
            modelValue.value = Number(value);
            emit('update:modelValue', Number(value));
            event.target.value = Number(value);
        }
    } else {
        modelValue.value = value;
        emit('update:modelValue', value);
    }

}

const truncate = (text) => {
    return text.length > 20 ? text.substring(0, 20) + '...' : text;
};

// Watch for changes in props.modelValue and update modelValue ref accordingly
watch(() => props.modelValue, (newVal) => {
    modelValue.value = newVal;
});
</script>

<template>

    <label :for="props.id" class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">
        {{ props.label }}</label>
    <div v-if="!isMultiline" class="mt-2 sm:col-span-2 sm:mt-0">
        <p v-if="isContactNumber" class="absolute text-sm mt-2 ml-2">+63</p>
        <input v-if="!isTextArea" :type="props.type" :value="modelValue" @input="handleInput" @keydown="checkDigit"
            :id="props.id" :placeholder="props.placeholder" :aria-label="props.label" :disabled="props.disabled"
            :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 disabled:bg-gray-200', props.class, props.isContactNumber ? 'pl-10' : '']" />

        <textarea v-else rows="6" :type="props.type" :value="modelValue" @input="handleInput" @keydown="checkDigit"
            :id="props.id" :placeholder="props.placeholder" :aria-label="props.label" :disabled="props.disabled"
            :class="['block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 disabled:bg-gray-200', props.class, props.isContactNumber ? 'pl-10' : '']" />

        <p v-if="props.error" class="text-red-500 text-sm mt-2">
            {{ props.error }}
        </p>
    </div>

    <!-- <div v-else>
        <div v-for="(tag, index) in previewSkills" :key="tag" class="tag-input__tag">
            <span @click="removeTag(index)">x</span>
            {{ tag }}
        </div>

        <input type="text" class="tag-input__text" @keydown="addTag" />
    </div> -->

    <div v-else>
        <div
            :class="['flex flex-wrap items-center w-full mt-2 sm:col-span-2 sm:mt-0 block bg-white px-3 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 disabled:bg-gray-200', props.class]">
            <div class="flex flex-wrap gap-1 items-center">
                <div class="ml-2" v-for="(skill, index) in previewSkills" :key="skill">
                    <Badge :title="truncate(skill)" :removeTag="removeTag" :index="index" :isClosable="true" />
                </div>
                <input type="text" class="bg-transparent border-none focus:ring-0 text-sm -ml-1" @keydown="addTag"
                    @keydown.delete="removeLastTag" />
            </div>
        </div>
        <p class="text-sm mt-1.5 text-gray-500">Enter a comma after each tag or press Enter.</p>
        <p v-if="props.error" class="text-red-500 text-sm mt-2">
            {{ props.error }}
        </p>
    </div>
</template>

<style scoped>
.tag-input {
    width: 100%;
    border: 1px solid #eee;
    font-size: 0.9em;
    height: 50px;
    box-sizing: border-box;
    padding: 0 10px;
}

.tag-input__tag {
    height: 30px;
    float: left;
    margin-right: 10px;
    background-color: #eee;
    margin-top: 10px;
    line-height: 30px;
    padding: 0 5px;
    border-radius: 5px;
}

.tag-input__tag>span {
    cursor: pointer;
    opacity: 0.75;
}

.tag-input__text {
    border: none;
    outline: none;
    font-size: 0.9em;
    line-height: 58px;
    background: none;
}
</style>