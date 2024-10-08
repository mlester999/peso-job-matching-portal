<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    users: Object,
    pagination: Object,
    filters: Object,
});

let search = ref(props.filters.search);

const updateInfo = (userId) => {
    router.get(`/admin/users/edit/${userId}`);
}

watch(
    search,
    debounce((value) => {
        const query = {};
        if (value) {
            query.search = value;
        }

        router.get(`/admin/users`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);


</script>

<template>
    <AuthenticatedLayout title="Users">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Users
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the users in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <Link :href="route('admin.users.add')"
                            class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        Add
                        user</Link>
                    </div>
                </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for user..." type="search" />
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
                                                Type
                                            </th>

                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Status
                                            </th>
                                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                <span class="sr-only">Edit</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="user in props.users.data" :key="user.id">
                                            <td
                                                class="whitespace py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ user.first_name }}</td>
                                            <td
                                                class="whitespace py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ user.last_name }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{
                                                user.email }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">+63{{
                                                user.contact_number
                                                }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">Applicant</td>

                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                                <span v-if="user.is_active"
                                                    class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                                                <span v-else
                                                    class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button type="button" @click="updateInfo(user.id)"
                                                    class="text-blue-600 hover:text-blue-900">Edit<span
                                                        class="sr-only">, {{ user.name }}</span></button>
                                            </td>
                                        </tr>

                                        <tr v-if="users.data.length === 0">
                                            <td colspan="11"
                                                class="whitespace-nowrap text-center py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                No User Found</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div
                                    class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between">
                                    <Pagination :users="users" :pagination="pagination" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
