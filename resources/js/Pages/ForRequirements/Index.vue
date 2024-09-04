<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    applications: Object,
    pagination: Object,
    filters: Object,
});

let search = ref(props.filters.search);

const page = usePage()

const updateInfo = (applicationId) => {
    if (page.props.auth.user.employer) {
        router.get(`/employer/for-requirements/view/${applicationId}`);
    } else if (page.props.auth.user.admin) {
        router.get(`/admin/for-requirements/view/${applicationId}`);
    }
}

const truncate = (text) => {
    return text.length > 20 ? text.substring(0, 20) + '...' : text;
};

watch(
    search,
    debounce((value) => {
        const query = {};
        if (value) {
            query.search = value;
        }
        if (page.props.auth.user.employer) {
            router.get(`/employer/for-requirements`, query, {
                preserveState: true,
                replace: true,
            });
        } else if (page.props.auth.user.admin) {
            router.get(`/admin/for-requirements`, query, {
                preserveState: true,
                replace: true,
            });
        }


    }, 500)
);
</script>

<template>
    <AuthenticatedLayout title="For Requirements">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            For Requirements
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the applications in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for application..." type="search" />
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
                                                Applied Job
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Applicant Skills
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Applied Date
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Status
                                            </th>
                                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                <span class="sr-only">View</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="application in props.applications.data" :key="application.id">
                                            <td
                                                class="whitespace py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ application.first_name }}</td>
                                            <td
                                                class="whitespace py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ application.last_name }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                application.email }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">+63{{
                                                application.contact_number
                                            }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                JSON.parse(application.skills).jobPositionTitle }}</td>
                                            <td
                                                class="whitespace px-3 py-4 text-sm text-gray-500 flex flex-wrap gap-2 items-center">
                                                <div class="ml-2"
                                                    v-for="(skill, index) in JSON.parse(application.skills).skills"
                                                    :key="skill">
                                                    <Badge :title="truncate(skill)" :removeTag="removeTag"
                                                        :index="index" :isClosable="false" />
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                application.created_at
                                            }}</td>
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                                <span v-if="application.is_active"
                                                    class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                                                <span v-else
                                                    class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button type="button" @click="updateInfo(application.id)"
                                                    class="text-blue-600 hover:text-blue-900">View</button>
                                            </td>
                                        </tr>

                                        <tr v-if="applications.data.length === 0">
                                            <td colspan="11"
                                                class="whitespace-nowrap text-center py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                No For Requirements Found</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div
                                    class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between">
                                    <Pagination :users="applications" :pagination="pagination" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
