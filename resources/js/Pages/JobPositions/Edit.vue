<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import InputField from '@/Components/InputField.vue';
import SelectField from '@/Components/SelectField.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const props = defineProps({
    jobPosition: Object
})

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

const form = useForm({
    title: props.jobPosition.title,
    description: props.jobPosition.description,
    skills: JSON.parse(props.jobPosition.skills) ?? [],
    is_active: props.jobPosition.is_active,
});
console.log('form: ', form);
const page = usePage();

const toast = useToast();

const submit = () => {
    form.put(`/admin/job-positions/update/${props.jobPosition.id}`, {
        onSuccess: () => {
            toast.success("Job position updated successfully!");
            router.visit('/admin/job-positions');
        },
    });
};
</script>

<template>
    <AuthenticatedLayout title="Edit Job Position">
        <template #header>
            <form @submit.prevent="submit">
                <div class="space-y-12 sm:space-y-16 px-4">
                    <div>
                        <h2 class="text-xl font-semibold leading-tight">
                            Edit Job Position
                        </h2>
                        <!-- <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-600">Use a permanent address where you can
                            receive mail.</p> -->

                        <div
                            class="mt-10 space-y-8 border-b border-gray-900/10 pb-12 sm:space-y-0 sm:divide-y sm:divide-gray-900/10 sm:border-t sm:pb-0">
                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="title" v-model="form.title" type="text" label="Title"
                                    class="sm:max-w-sm" :error="form.errors.title" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="description" v-model="form.description" type="text" label="Description"
                                    class="sm:max-w-md" :error="form.errors.description" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="skills" v-model="form.skills" type="text" label="Skills"
                                    class="sm:max-w-md" :error="form.errors.skills" :isMultiline="true" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <SelectField id="is_active" label="Status" v-model="form.is_active"
                                    :error="form.errors.is_active" class="sm:max-w-xs">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </SelectField>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <a :href="route('admin.job-positions.index')"
                        class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                    <button type="submit"
                        class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Save</button>
                </div>
            </form>
        </template>
    </AuthenticatedLayout>
</template>
