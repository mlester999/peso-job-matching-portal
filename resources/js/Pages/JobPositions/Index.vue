<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    jobPositions: Object,
    pagination: Object,
    filters: Object,
});

let search = ref(props.filters.search);

const updateInfo = (userId) => {
    router.get(`/admin/job-positions/edit/${userId}`);
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

        router.get(`/admin/jobPositions`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);


</script>

<template>
    <AuthenticatedLayout title="Job Positions">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Job Positions
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the job-positions in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <Link :href="route('admin.job-positions.add')"
                            class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        Add
                        Job Position</Link>
                    </div>
                </div>
                <div class="sm:flex sm:items-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for job position..." type="search" />
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
                                                Description
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                Skills
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
                                        <tr v-for="jobPosition in props.jobPositions.data" :key="jobPosition.id">
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ jobPosition.title }}</td>
                                            <td class="whitespace px-3 py-4 text-sm text-gray-500">{{
                                                jobPosition.description }}</td>
                                            <td
                                                class="whitespace px-3 py-4 text-sm text-gray-500 flex flex-wrap gap-2 items-center">
                                                <div class="ml-2"
                                                    v-for="(skill, index) in JSON.parse(jobPosition.skills)"
                                                    :key="skill">
                                                    <Badge :title="truncate(skill)" :removeTag="removeTag"
                                                        :index="index" :isClosable="false" />
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                                <span v-if="jobPosition.is_active"
                                                    class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                                                <span v-else
                                                    class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                                            </td>
                                            <td
                                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <button type="button" @click="updateInfo(jobPosition.id)"
                                                    class="text-blue-600 hover:text-blue-900">Edit<span
                                                        class="sr-only">, {{ jobPosition.name }}</span></button>
                                            </td>
                                        </tr>

                                        <tr v-if="jobPositions.data.length === 0">
                                            <td colspan="10"
                                                class="whitespace-nowrap text-center py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                No Job Position Found</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div
                                    class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between">
                                    <Pagination :users="jobPositions" :pagination="pagination" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
