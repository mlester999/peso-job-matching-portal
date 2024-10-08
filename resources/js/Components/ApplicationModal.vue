<script setup>
import { ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XCircleIcon, ExclamationCircleIcon, InformationCircleIcon } from '@heroicons/vue/outline'
import { Link } from '@inertiajs/vue3';
import SelectField from './SelectField.vue';
import debounce from 'lodash.debounce';

const props = defineProps({
    title: String,
    onClose: Function,
    onSubmit: Function,
    href: String,
    linkTitle: String,
    jobAdvertisements: Array,
    applicantSkills: Array
})

const open = ref(true)
const selectedJobAdvertisementId = ref(null)
const matchedPercentage = ref('')

const handleCloseModal = () => {
    open.value = false;
    props.onClose();
}

watch(
    selectedJobAdvertisementId,
    debounce((value) => {
        const jobAds = props.jobAdvertisements.find(jobAds => jobAds.id === Number(value))
        const jobAdsSkills = JSON.parse(jobAds.skills);

        // Find the intersection (common elements)
        const commonSkills = jobAdsSkills.filter(item => props.applicantSkills.includes(item));

        // Calculate the percentage of matched items
        const matchPercentage = (commonSkills.length / jobAdsSkills.length) * 100;

        matchedPercentage.value = matchPercentage.toFixed(2);
        console.log(matchedPercentage.value);
    }, 500)
);

</script>

<template>
    <TransitionRoot as="template" :show="open">
        <Dialog class="relative z-50" @close="handleCloseModal">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                            <div class="absolute right-0 top-0 hidden pr-2 pt-2 sm:block">
                                <button type="button"
                                    class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                                    @click="handleCloseModal">
                                    <span class="sr-only">Close</span>
                                    <XCircleIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                    <InformationCircleIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                        {{ title }}</DialogTitle>
                                    <div class="mt-2 sm:max-w-lg">
                                        <SelectField id="jobAdvertisement" v-model="selectedJobAdvertisementId">
                                            <option value="" disabled selected hidden>~ Select Job Advertisement ~
                                            </option>
                                            <option v-for="(jobAd, index) in props.jobAdvertisements" :key="index"
                                                :value="jobAd.id">
                                                {{ jobAd.job_position.title }}
                                            </option>
                                        </SelectField>
                                    </div>

                                </div>
                            </div>
                            <div v-if="matchedPercentage" class="mt-4 sm:max-w-lg text-center">
                                <p class="text-sm">Matched Percentage:
                                    <span v-if="Number(matchedPercentage) === 50"
                                        class="inline-flex items-center gap-x-0.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 text-ellipsis">{{
                                            matchedPercentage }}%</span>
                                    <span v-else-if="Number(matchedPercentage) < 50"
                                        class="inline-flex items-center gap-x-0.5 rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-600 text-ellipsis">{{
                                            matchedPercentage }}%</span>
                                    <span v-else-if="Number(matchedPercentage) > 50"
                                        class="inline-flex items-center gap-x-0.5 rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-600 text-ellipsis">{{
                                            matchedPercentage }}%</span>
                                </p>
                            </div>
                            <div class="mx-auto text-center mt-4">
                                <button @click="props.onSubmit(selectedJobAdvertisementId)" type="button"
                                    :disabled="!selectedJobAdvertisementId || !matchedPercentage"
                                    class="disabled:bg-blue-300 inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    Submit</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
