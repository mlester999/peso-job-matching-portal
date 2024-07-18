<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import InputField from '@/Components/InputField.vue';
import SelectField from '@/Components/SelectField.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

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
    name: "",
    email: "",
    province: "Laguna",
    city: "Cabuyao",
    barangay: "",
    street_address: "",
    contact_number: "",
    zip_code: "4025"
});

const page = usePage();

const toast = useToast();

const submit = () => {
    form.post(`/admin/employers/store`, {
        onSuccess: () => {
            toast.success("Employer created successfully!");
            router.visit('/admin/employers');
        },
    });
};
</script>

<template>
    <AuthenticatedLayout title="Add Employer">
        <template #header>
            <form @submit.prevent="submit">
                <div class="space-y-12 sm:space-y-16 px-4">
                    <div>
                        <h2 class="text-xl font-semibold leading-tight">
                            Add Employer
                        </h2>
                        <!-- <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-600">Use a permanent address where you can
                            receive mail.</p> -->

                        <div
                            class="mt-10 space-y-8 border-b border-gray-900/10 pb-12 sm:space-y-0 sm:divide-y sm:divide-gray-900/10 sm:border-t sm:pb-0">
                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="name" v-model="form.name" type="text" label="Name" class="sm:max-w-sm"
                                    :error="form.errors.name" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="email" v-model="form.email" type="text" label="Email"
                                    class="sm:max-w-md" :error="form.errors.email" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <SelectField id="province" label="Province" v-model="form.province"
                                    :error="form.errors.province" class="sm:max-w-xs" disabled>
                                    <option>Laguna</option>
                                </SelectField>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <SelectField id="city" label="City" v-model="form.city" :error="form.errors.city"
                                    class="sm:max-w-xs" disabled>
                                    <option>Cabuyao</option>
                                </SelectField>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <SelectField id="barangay" label="Barangay" v-model="form.barangay"
                                    :error="form.errors.barangay" class="sm:max-w-xs">
                                    <option value="" disabled selected hidden>~ Select Barangay ~</option>
                                    <option v-for="(barangay, index) in barangays" :key="index">
                                        {{ barangay }}
                                    </option>
                                </SelectField>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="street-address" v-model="form.street_address" type="text"
                                    label="Street Address" class="sm:max-w-lg" :error="form.errors.street_address" />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="zip-code" v-model="form.zip_code" type="text" label="ZIP / Postal Code"
                                    class="sm:max-w-sm" :error="form.errors.zip_code" :isNumber="true" disabled />
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <InputField id="contact-number" v-model="form.contact_number" type="text"
                                    label="Contact Number" class="sm:max-w-sm" :error="form.errors.contact_number"
                                    :isContactNumber="true" :isNumber="true" maxlength="10" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <button type="button" class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                    <button type="submit"
                        class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Save</button>
                </div>
            </form>
        </template>
    </AuthenticatedLayout>
</template>
