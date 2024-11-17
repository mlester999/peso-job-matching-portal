<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { router, useForm, usePage, Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { computed } from 'vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    application: Object
})

const form = useForm({
    status: null
});

const page = usePage();

const toast = useToast();

const getCancelLink = computed(() => {
    if (page.props.auth.user.employer) {
        return route('employer.disqualified.indexForDisqualified');
    } else if (page.props.auth.user.admin) {
        return route('admin.disqualified.indexForDisqualified');
    }
});

const truncate = (text) => {
    return text.length > 20 ? text.substring(0, 20) + '...' : text;
};

const submit = (status) => {
    form.status = status;

    if (page.props.auth.user.employer) {
        form.put(`/employer/applications/update-status/${props.application.id}`, {
            onSuccess: () => {
                toast.success("Application updated successfully!");
                router.visit('/employer/deployed');
            },
        });
    } else if (page.props.auth.user.admin) {
        form.put(`/admin/applications/update-status/${props.application.id}`, {
            onSuccess: () => {
                toast.success("Application updated successfully!");
                router.visit('/admin/deployed');
            },
        });
    }
};

const formattedDate = (date) => {
    const dateObj = new Date(date)
    return dateObj.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
    })
}
</script>

<template>
    <AuthenticatedLayout title="View Not Qualified">
        <template #header>
            <div class="space-y-12 sm:space-y-16 px-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight">
                        View Not Qualified
                    </h2>
                    <div class="mt-10 overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-6 sm:px-6">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Personal Information</h3>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">This section gathers key
                                details like name, contact info, and identifiers.</p>
                        </div>
                        <div class="border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">First Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        props.application.applicant.first_name }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Middle Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        props.application.applicant.middle_name ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Last Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        props.application.applicant.last_name }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Email Address</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.applicant.user.email }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Contact Number</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        +63{{ props.application.applicant.contact_number }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Birth Date</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ formattedDate(props.application.birth_date) }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Sex</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.sex }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Province</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.province }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">City</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.city }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Barangay</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.barangay }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Street Address</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.street_address }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Zip Code</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        {{ props.application.zip_code }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-10 overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-6 sm:px-6">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Educational Background</h3>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">This section details an
                                individual's academic history, including degrees, and institutions attended.</p>
                        </div>
                        <div v-for="(education, index) in JSON.parse(props.application.education)"
                            class="border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div v-if="index" v class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900"></dt>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">School Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        education.schoolName }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Educational Level</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        education.educationalLevel }}
                                    </dd>
                                </div>
                                <div v-if="education.level" class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Level</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        education.level }}
                                    </dd>
                                </div>
                                <div v-if="education.course" class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Course</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        education.course }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Start Date</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        formattedDate(education.startDate) }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">End Date</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        formattedDate(education.endDate) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-10 overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-6 sm:px-6">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Work Experience</h3>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">This section outlines an
                                individual's previous job roles, responsibilities, and professional accomplishments.</p>
                        </div>
                        <div v-for="(work, index) in JSON.parse(props.application.work_experience)"
                            class="border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div v-if="index" v class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900"></dt>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Company Name</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        work.companyName ? work.companyName : "N/A" }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Company Address</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        work.companyAddress ? work.companyAddress : "N/A" }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Employment Type</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        work.employmentType ? work.employmentType : "N/A" }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Job Title</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        work.jobTitle ? work.jobTitle : "N/A" }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Industry</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        work.industry ? work.industry : "N/A" }}
                                    </dd>
                                </div>
                                <div v-if="work.startDate" class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Start Date</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        formattedDate(work.startDate) }}
                                    </dd>
                                </div>
                                <div v-if="work.endDate" class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">End Date</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        formattedDate(work.endDate) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-10 overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-6 sm:px-6">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Skills & Profession</h3>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">This section highlights an
                                individual’s key abilities, competencies, and professional expertise.</p>
                        </div>
                        <div class="border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Job Position</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{
                                        JSON.parse(props.application.skills).jobPositionTitle }}
                                    </dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-900">Skills</dt>
                                    <dd
                                        class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0 whitespace flex flex-wrap gap-2 items-center">
                                        <div v-for="(skill, index) in JSON.parse(props.application.skills).skills"
                                            :key="skill">
                                            <Badge :title="truncate(skill)" :removeTag="removeTag" :index="index"
                                                :isClosable="false" />
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6 mr-4">
                <Link :href="getCancelLink"
                    class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Cancel</Link>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
