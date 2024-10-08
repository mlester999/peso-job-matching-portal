<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { router, useForm, usePage, Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { computed } from 'vue';
import Badge from '@/Components/Badge.vue';
import InputField from '@/Components/InputField.vue';
import SelectField from '@/Components/SelectField.vue';

const props = defineProps({
    application: Object
})

const form = useForm({
    status: null,
    work_location: null,
    start_date: null,
    notes: null,
});

const page = usePage();

const toast = useToast();

const getCancelLink = computed(() => {
    if (page.props.auth.user.employer) {
        return route('employer.qualified.indexForQualified');
    } else if (page.props.auth.user.admin) {
        return route('admin.qualified.indexForQualified');
    }
});

const truncate = (text) => {
    return text.length > 20 ? text.substring(0, 20) + '...' : text;
};

const submit = (status) => {
    form.status = status;

    if (page.props.auth.user.employer) {
        if (status) {
            form.put(`/employer/for-deployment/update-for-deployment/${props.application.id}`, {
                onSuccess: () => {
                    toast.success("Application updated successfully!");
                    router.visit('/employer/for-deployment');
                },
            });
        } else {
            form.put(`/employer/applications/update-status/${props.application.id}`, {
                onSuccess: () => {
                    toast.success("Application updated successfully!");
                    router.visit('/employer/for-deployment');
                },
            });
        }

    } else if (page.props.auth.user.admin) {
        if (status) {
            form.put(`/admin/for-deployment/update-for-deployment/${props.application.id}`, {
                onSuccess: () => {
                    toast.success("Application updated successfully!");
                    router.visit('/admin/for-deployment');
                },
            });
        } else {
            form.put(`/employer/applications/update-status/${props.application.id}`, {
                onSuccess: () => {
                    toast.success("Application updated successfully!");
                    router.visit('/employer/for-deployment');
                },
            });
        }
    }
};
</script>

<template>
    <AuthenticatedLayout title="View For Deployment">
        <template #header>
            <div class="space-y-12 sm:space-y-16 px-4">
                <div>
                    <h2 class="text-xl font-semibold leading-tight">
                        Set For Deployment
                    </h2>
                    <div class="mt-10 overflow-hidden bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-6 sm:px-6">
                            <h3 class="text-base font-semibold leading-7 text-gray-900">Set For Deployment</h3>
                            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">This section is for setting
                                for deployment applicants.</p>
                        </div>
                        <div
                            class="px-4 sm:px-6 space-y-8 border-b border-gray-900/10 pb-12 sm:space-y-0 sm:divide-y sm:divide-gray-900/10 sm:border-t sm:pb-0">
                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField class="sm:max-w-sm" id="work_location" type="text" label="Work Location"
                                    v-model="form.work_location" :error="form.errors.work_location" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField class="sm:max-w-sm" id="start_date" type="date" label="Start Date"
                                    v-model="form.start_date" :error="form.errors.start_date" />
                            </div>
                        </div>
                        <div
                            class="px-4 sm:px-6 space-y-8 border-b border-gray-900/10 pb-12 sm:space-y-0 sm:divide-y sm:divide-gray-900/10 sm:border-t sm:pb-0">
                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField class="sm:max-w-sm" id="notes" type="text" label="Notes (Optional)"
                                    v-model="form.notes" :error="form.errors.notes" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6 mr-4">
                <Link :href="getCancelLink" class="text-sm font-semibold leading-6 text-gray-900">Cancel</Link>
                <button @click="submit(0)" type="button"
                    class="inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Disapprove</button>
                <button @click="submit(6)" type="button"
                    class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Set
                    For Deployment</button>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
