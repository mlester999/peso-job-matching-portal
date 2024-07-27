<script setup>
import CheckboxList from '@/Components/CheckboxList.vue';
import SelectField from '@/Components/SelectField.vue';
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { UserCircleIcon, BriefcaseIcon } from '@heroicons/vue/outline'
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount, onBeforeUpdate } from 'vue';
import debounce from 'lodash.debounce'
import { useToast } from 'vue-toastification';

const roles = [
    { id: 'full-time', title: 'Full Time' },
    { id: 'part-time', title: 'Part Time' },
    { id: 'contract', title: 'Contract' },
    { id: 'temporary', title: 'Temporary' },
]

const positionLevels = [
    { id: 'trainee', title: 'Trainee' },
    { id: 'junior-associate', title: 'Junior Associate' },
    { id: 'associate', title: 'Associate' },
    { id: 'senior-associate', title: 'Senior Associate' },
    { id: 'junior-executive', title: 'Junior Executive' },
    { id: 'executive', title: 'Executive' },
    { id: 'senior-executive', title: 'Senior Executive' },
    { id: 'assistant-manager', title: 'Assistant Manager' },
    { id: 'manager', title: 'Manager' },
]

const yearsOfExperiences = [
    { id: '1', title: '1' },
    { id: '2', title: '2' },
    { id: '3', title: '3' },
    { id: '4', title: '4' },
    { id: '5', title: '5' },
    { id: '6', title: '6' },
    { id: '7', title: '7' },
    { id: '8', title: '8' },
    { id: '9', title: '9' },
    { id: '10', title: '10' },
]

const barangays = [
    'Baclaran',
    'Banay-Banay',
    'Banlic',
    'Bigaa',
    'Butong',
    'Casile',
    'Diezmo',
    'Gulod',
    'Mamatid',
    'Marinig',
    'Niugan',
    'Pittland',
    'Pulo',
    'Sala',
    'San Isidro',
    'Barangay I Poblacion',
    'Barangay II Poblacion',
    'Barangay III Poblacion',
]

const props = defineProps({
    jobPositions: Object,
    jobAdvertisement: Object
});
console.log(props);
const form = useForm({
    job_position_id: props.jobAdvertisement.job_position_id,
    role: props.jobAdvertisement.role,
    skills: JSON.parse(props.jobAdvertisement.skills),
    position_level: props.jobAdvertisement.position_level,
    years_of_experience: props.jobAdvertisement.years_of_experience,
    location: props.jobAdvertisement.location,
    is_draft: props.jobAdvertisement.is_draft
});

const addSkill = (val) => {
    form.skills.push(val);
}

const removeSkill = (title) => {
    const index = form.skills.indexOf(title);
    if (index !== -1) {
        form.skills.splice(index, 1);
    }
}

const selectedJobPositiondId = ref(props.jobAdvertisement.job_position_id);
const selectedJobTitleSkills = ref(JSON.parse(props.jobAdvertisement.job_position.skills));

const toast = useToast();

const autoSaveDraft = () => {
    form.post(`/employer/job-ads/edit-draft/${props.jobAdvertisement.id}`);
};

const submit = () => {
    form.post(`/employer/job-ads/store`, {
        onSuccess: () => {
            toast.success("Job ads created successfully!");
            router.visit('/employer/job-ads');
        },
    });
};

watch(
    selectedJobPositiondId,
    debounce((value) => {
        form.job_position_id = value;
        const jobPosition = props.jobPositions.find((job) => job.id === Number(value))
        selectedJobTitleSkills.value = JSON.parse(jobPosition.skills)
    }, 500)
);

onBeforeUnmount(() => {
    if (form.job_position_id || form.role || form.skills.length > 0 || form.position_level || form.years_of_experience || form.location) {
        autoSaveDraft();
    }
})
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

                            <form @submit.prevent="submit" class=" md:col-span-2">
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
                                        <div class="col-span-full">
                                            <label for="title"
                                                class="block text-md font-semibold leading-6 text-gray-900">What is the
                                                job title?</label>
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
                                            <fieldset>
                                                <label for="role"
                                                    class="block text-md font-semibold leading-6 text-gray-900">What
                                                    type of role is it?</label>
                                                <div class="mt-6 space-y-4">
                                                    <div v-for="role in roles" :key="role.id" class="flex items-center">
                                                        <input :id="role.id" v-model="form.role" :value="role.title"
                                                            name="role" type="radio"
                                                            class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-600" />
                                                        <label :for="role.id"
                                                            class="ml-3 block text-sm font-medium leading-6 text-gray-900">{{
                                                                role.title }}</label>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>

                                        <div class="col-span-full">
                                            <label for="title"
                                                class="block text-md font-semibold leading-6 text-gray-900">What is the
                                                position level?</label>
                                            <div class="mt-2">
                                                <div class="sm:max-w-lg">
                                                    <SelectField id="positionLevel" v-model="form.position_level"
                                                        :error="form.errors.position_level">
                                                        <option value="" disabled selected hidden>~ Select Position
                                                            Level ~
                                                        </option>
                                                        <option v-for="(positionLevel, index) in positionLevels"
                                                            :key="index" :value="positionLevel.title">
                                                            {{ positionLevel.title }}
                                                        </option>
                                                    </SelectField>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-span-full">
                                            <label for="title"
                                                class="block text-md font-semibold leading-6 text-gray-900">What are the
                                                required years of experience?</label>
                                            <div class="mt-2">
                                                <div class="sm:max-w-lg">
                                                    <SelectField id="yearsOfExperience"
                                                        v-model="form.years_of_experience"
                                                        :error="form.errors.years_of_experience">
                                                        <option value="" disabled selected hidden>~ Select Years of
                                                            Experience ~
                                                        </option>
                                                        <option v-for="(yearsOfExperience, index) in yearsOfExperiences"
                                                            :key="index" :value="yearsOfExperience.title">
                                                            {{ yearsOfExperience.title }}
                                                        </option>
                                                    </SelectField>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-span-full">
                                            <label for="title"
                                                class="block text-md font-semibold leading-6 text-gray-900">Work
                                                Location</label>
                                            <div class="mt-2">
                                                <div class="sm:max-w-lg">
                                                    <SelectField id="location" v-model="form.location"
                                                        :error="form.errors.location">
                                                        <option value="" disabled selected hidden>~ Select Location ~
                                                        </option>
                                                        <option v-for="(barangay, index) in barangays" :key="index"
                                                            :value="barangay">
                                                            {{ barangay }}
                                                        </option>
                                                    </SelectField>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                                    <Link :href="route('dashboard')"
                                        class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                                    <button type="submit"
                                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Save</button>
                                </div>
                            </form>

                            <div class="px-4 sm:px-0 md:col-span-2">
                                <h2 class="text-base font-semibold leading-7 text-gray-900 pb-2">Related Skills: </h2>

                                <template v-if="selectedJobTitleSkills && selectedJobTitleSkills.length > 0">
                                    <template v-for="(jobSkill, index) in selectedJobTitleSkills" :key="index">
                                        <CheckboxList :index="index" :title="jobSkill" :addSkill="addSkill"
                                            :removeSkill="removeSkill" />
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
