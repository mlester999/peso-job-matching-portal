<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    applicants: Object,
    pagination: Object,
    filters: Object,
});

let search = ref(props.filters.search);

const page = usePage()

const updateInfo = (applicantId) => {
    if (page.props.auth.user.employer) {
        router.get(`/employer/applicants/edit/${applicantId}`);
    } else if (page.props.auth.user.admin) {
        router.get(`/admin/applicants/edit/${applicantId}`);
    }
}

watch(
    search,
    debounce((value) => {
        const query = {};
        if (value) {
            query.search = value;
        }
        if (page.props.auth.user.employer) {
            router.get(`/employer/applicants`, query, {
                preserveState: true,
                replace: true,
            });
        } else if (page.props.auth.user.admin) {
            router.get(`/admin/applicants`, query, {
                preserveState: true,
                replace: true,
            });
        }


    }, 500)
);


</script>

<template>
    <AuthenticatedLayout title="Applicants">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Applicants
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the applicants in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for applicant..." type="search" />
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
                                                First Name</th>
                                            <th scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                                Last Name</th>
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
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="applicant in props.applicants.data" :key="applicant.id">
                                            <td
                                                class="whitespace py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ applicant.first_name }}</td>
                                            <td
                                                class="whitespace py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ applicant.last_name }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                applicant.email }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">+63{{
                                                applicant.contact_number
                                            }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                applicant.province }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                applicant.city
                                            }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                applicant.barangay
                                            }}</td>
                                            <td class="whitespace px-3 py-4 text-sm text-gray-500">{{
                                                applicant.street_address
                                            }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                applicant.zip_code
                                            }}</td>
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                                <span v-if="applicant.is_active"
                                                    class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                                                <span v-else
                                                    class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                                            </td>
                                        </tr>

                                        <tr v-if="applicants.data.length === 0">
                                            <td colspan="11"
                                                class="whitespace-nowrap text-center py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                No Applicant Found</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div
                                    class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between">
                                    <Pagination :users="applicants" :pagination="pagination" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
