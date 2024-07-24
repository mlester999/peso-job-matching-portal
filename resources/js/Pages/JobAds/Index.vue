<script setup>
import CheckboxList from '@/Components/CheckboxList.vue';
import SelectField from '@/Components/SelectField.vue';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { UserCircleIcon, BriefcaseIcon } from '@heroicons/vue/outline'
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'

const props = defineProps({
    jobPositions: Object
});

const form = useForm({
    job_position_id: "",
    role: "",
    position_level: "",
    years_of_experience: "",
    is_draft: ""

});

const selectedJobPositiondId = ref();
const selectedJobTitleSkills = ref();

watch(
    selectedJobPositiondId,
    debounce((value) => {
        form.job_position_id = value;
        const jobPosition = props.jobPositions.find((job) => job.id === Number(value))
        selectedJobTitleSkills.value = JSON.parse(jobPosition.skills)
    }, 500)
);
</script>

<template>
    <AuthenticatedLayout title="Job Ads">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Job Ads
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the job-positions in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                </div>
                <div class="mt-8 flow-root">
                    <div class="space-y-10 divide-y divide-gray-900/10">
                        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-4">

                            <form class=" md:col-span-2">
                                <h2 class="text-base font-semibold leading-7 text-gray-900 pb-2">
                                    Fill in the Job Ads details
                                </h2>
                                <div
                                    class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl px-4 py-6 sm:p-8 space-y-6">
                                    <div class="flex items-center space-x-2">
                                        <BriefcaseIcon class="h-6 w-6 text-gray-900" aria-hidden="true" />
                                        <h2 class="text-xl font-semibold leading-tight">
                                            Job Type
                                        </h2>
                                    </div>

                                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                        <div class="sm:col-span-6">
                                            <label for="website"
                                                class="block text-md font-semibold leading-6 text-gray-900">What is the
                                                Job title?</label>
                                            <div class="mt-2">
                                                <div class="sm:max-w-lg">
                                                    <SelectField id="jobPosition" v-model="selectedJobPositiondId"
                                                        :error="form.errors.job_position_id">
                                                        <option value="" disabled selected hidden>~ Select Job Title ~
                                                        </option>
                                                        <option v-for="(jobTitle, index) in jobPositions" :key="index"
                                                            :value="jobTitle.id">
                                                            {{ jobTitle.title }}
                                                        </option>
                                                    </SelectField>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-span-full">
                                            <label for="about"
                                                class="block text-sm font-medium leading-6 text-gray-900">About</label>
                                            <div class="mt-2">
                                                <textarea id="about" name="about" rows="3"
                                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                            </div>
                                            <p class="mt-3 text-sm leading-6 text-gray-600">Write a few sentences about
                                                yourself.</p>
                                        </div>

                                        <div class="col-span-full">
                                            <label for="photo"
                                                class="block text-sm font-medium leading-6 text-gray-900">Photo</label>
                                            <div class="mt-2 flex items-center gap-x-3">
                                                <UserCircleIcon class="h-12 w-12 text-gray-300" aria-hidden="true" />
                                                <button type="button"
                                                    class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Change</button>
                                            </div>
                                        </div>

                                        <div class="col-span-full">
                                            <label for="cover-photo"
                                                class="block text-sm font-medium leading-6 text-gray-900">Cover
                                                photo</label>
                                            <div
                                                class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                                <div class="text-center">
                                                    <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                                        <label for="file-upload"
                                                            class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                                            <span>Upload a file</span>
                                                            <input id="file-upload" name="file-upload" type="file"
                                                                class="sr-only" />
                                                        </label>
                                                        <p class="pl-1">or drag and drop</p>
                                                    </div>
                                                    <p class="text-xs leading-5 text-gray-600">PNG, JPG, GIF up to 10MB
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                                    <button type="button"
                                        class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                                    <button type="submit"
                                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                                </div>
                            </form>

                            <div class="px-4 sm:px-0 md:col-span-2">
                                <h2 class="text-base font-semibold leading-7 text-gray-900 pb-2">Related Skills: </h2>

                                <template v-if="selectedJobTitleSkills && selectedJobTitleSkills.length > 0">
                                    <template v-for="(jobSkill, index) in selectedJobTitleSkills" :key="index">
                                        <CheckboxList :title="jobSkill" />
                                    </template>
                                </template>

                                <template v-else>
                                    <CheckboxList :isNoRecord="true" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
