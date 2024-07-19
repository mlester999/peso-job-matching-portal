<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';

const props = defineProps({
    employers: Object,
    pagination: Object,
    filters: Object,
});

let search = ref(props.filters.search);

const updateInfo = (userId) => {
    router.get(`/admin/employers/edit/${userId}`);
}

watch(
    search,
    debounce((value) => {
        const query = {};
        if (value) {
            query.search = value;
        }

        router.get(`/admin/employers`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);


</script>

<template>
    <AuthenticatedLayout title="Employers">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Employers
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the employers in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <Link :href="route('admin.employers.add')"
                            class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        Add
                        employer</Link>
                    </div>
                </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for employer..." type="search" />
                    </div>

                </div>
                <div class="mt-8 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                Name</th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email
                                                Address
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Contact Number
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Province
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                City
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Barangay
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Street Address
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Zip Code
                                            </th>
                                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                <span class="sr-only">Edit</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="employer in props.employers.data" :key="employer.id">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ employer.name }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                employer.email }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">+63{{
                                                employer.contact_number
                                                }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                employer.province }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                employer.city
                                                }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                employer.barangay
                                                }}</td>
                                            <td class="whitespace px-3 py-4 text-sm text-gray-500">{{
                                                employer.street_address
                                                }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                employer.zip_code
                                                }}</td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button type="button" @click="updateInfo(employer.id)"
                                                    class="text-blue-600 hover:text-blue-900">Edit<span
                                                        class="sr-only">, {{ employer.name }}</span></button>
                                            </td>
                                        </tr>

                                        <tr v-if="employers.data.length === 0">
                                            <td colspan="9"
                                                class="whitespace-nowrap text-center py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                No Employer Found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
