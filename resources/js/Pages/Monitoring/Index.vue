<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import SelectField from '@/Components/SelectField.vue';

const props = defineProps({
    users: Object,
    pagination: Object,
    filters: Object,
});

let search = ref(props.filters.search);
let userType = ref(props.filters.userType);

const statuses = [
    'Disqualified',
    'In Progress',
    'Interview',
    'For Interview',
    'Requirements',
    'For Requirements',
    'Qualified',
    'For Deployment',
    'Deployed',
]

watch(
    search,
    debounce((value) => {
        const query = {};
        if (value) {
            query.search = value;
            query.userType = userType.value;
        }

        router.get(`/admin/monitoring`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

watch(
    userType,
    debounce((value) => {
        const query = {};
        if (value) {
            query.userType = value;
            query.search = search.value;
        }

        router.get(`/admin/monitoring`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);
</script>

<template>
    <AuthenticatedLayout title="Monitoring">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Monitoring
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the job-positions in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <SelectField id="userType" v-model="userType">
                            <option value="" selected>~ Select User Type ~
                            </option>
                            <option value="applicants">Applicants
                            </option>
                            <option value="employers">Employers
                            </option>
                        </SelectField>
                    </div>
                </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for monitoring..." type="search" />
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
                                                Title</th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Email Address
                                            </th>
                                            <th v-if="userType === 'employers'" scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Total Hired
                                            </th>
                                            <th v-else scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Application Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="user in props.users.data" :key="user.id">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ userType === 'employers' ? `${user.name || `${user.first_name}
                                                ${user.last_name}`}` :
                                                    `${user.first_name || user.name}
                                                ${user.last_name || ''}` }}</td>
                                            <td class="whitespace px-3 py-4 text-sm text-gray-500">{{
                                                user.email }}</td>
                                            <td
                                                class="whitespace px-3 py-4 text-sm text-gray-500 flex flex-wrap gap-2 items-center">
                                                <div v-if="userType === 'employers'" class="ml-2">
                                                    {{ user.total_hired }}
                                                </div>
                                                <div v-else class="ml-2">
                                                    {{ statuses[user.status] }}
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-if="users.data.length === 0">
                                            <td colspan="10"
                                                class="whitespace-nowrap text-center py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                No Monitoring Found</td>
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
