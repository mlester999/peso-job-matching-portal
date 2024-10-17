<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import { BriefcaseIcon } from '@heroicons/vue/solid';
import { useToast } from 'vue-toastification';

const props = defineProps({
    jobAdvertisements: Object,
    filters: Object,
    draftJobsCounts: Number,
    postedJobsCount: Number,
    applications: Object
});

onMounted(() => {
    if (props.applications && props.jobAdvertisements) {
        props.jobAdvertisements.forEach(el => {
            const jobAds = props.jobAdvertisements.find(jobAds => jobAds.id === Number(el.id))
            const jobAdsSkills = JSON.parse(jobAds.skills);
            let totalCount = 0;

            props.applications.forEach(el => {
                const applicantSkills = JSON.parse(el.skills).skills;
                // Find the intersection (common elements)
                const commonSkills = jobAdsSkills.filter(item => applicantSkills.includes(item));

                // Calculate the percentage of matched items
                const matchPercentage = (commonSkills.length / jobAdsSkills.length) * 100;

                if (matchPercentage > 0) totalCount += 1;
            })
            browseMatchesCount.value.push(totalCount);
        });
    }
})

const toast = useToast();

const matchedPercentage = ref('');

let browseMatchesCount = ref([]);

const tabs = [
    { name: 'Posted Jobs', alias: 'postedJobs', href: 'employer.reports.index', count: props.postedJobsCount, params: { tab: 'postedJobs' } },
    { name: 'Drafts', alias: 'drafts', href: 'employer.reports.index', count: props.draftJobsCounts, params: { tab: 'drafts' } },
]

const updateDraft = (jobAdsId) => {
    router.get(`/employer/job-ads/edit/${jobAdsId}`);
}

const deactivate = (id) => {
    router.put(`/employer/job-ads/deactivate/${id}`, {}, {
        onSuccess: () => {
            toast.success("Job ads deactivated successfully!");
        }
    });
}

const activate = (id) => {
    router.put(`/employer/job-ads/activate/${id}`, {}, {
        onSuccess: () => {
            toast.success("Job ads activated successfully!");
        }
    });
}

const browseMatches = (id) => {
    const query = {};
    query.jobAdvertisementId = id;
    router.get(`/employer/applications`, query, {
        preserveState: true,
        replace: true,
    });
}

// let search = ref(props.filters.search);

// const updateInfo = (userId) => {
//     router.get(`/admin/employers/edit/${userId}`);
// }

// watch(
//     search,
//     debounce((value) => {
//         const query = {};
//         if (value) {
//             query.search = value;
//         }

//         router.get(`/admin/employers`, query, {
//             preserveState: true,
//             replace: true,
//         });
//     }, 500)
// );


</script>

<template>
    <AuthenticatedLayout title="Employers">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Reports
                        </h2>
                    </div>
                    <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                        <Link :href="route('employer.job-ads.index')"
                            class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        Add
                        Job Ads</Link>
                    </div>
                </div>
                <div class="mt-8 flow-root">
                    <div>
                        <div class="sm:hidden">
                            <label for="tabs" class="sr-only">Select a tab</label>
                            <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
                            <select id="tabs" name="tabs"
                                class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                                <option v-for="tab in tabs" :key="tab.name" :selected="filters.tab === tab.alias">{{
                                    tab.name }}
                                </option>
                            </select>
                        </div>
                        <div class="block mb-8">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    <Link v-for="tab in tabs" :key="tab.name" :href="route(tab.href, tab.params)"
                                        :class="[filters.tab === tab.alias || (tab.alias === 'postedJobs' && !filters.tab) ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-200 hover:text-gray-700', 'flex whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium']"
                                        :aria-current="filters.tab === tab.alias ? 'page' : undefined">
                                    {{ tab.name }}
                                    <span
                                        :class="[filters.tab === tab.alias ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-900', 'ml-3 hidden rounded-full py-0.5 px-2.5 text-xs font-medium md:inline-block']">{{
                                            tab.count }}</span>
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div v-for="(jobAdvertisement, index) in jobAdvertisements" :key="jobAdvertisement.id"
                        class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden rounded-lg bg-white shadow">
                                <h2 class="sr-only" id="profile-overview-title">Job Report</h2>
                                <div class="bg-white p-6">
                                    <div class="sm:flex sm:items-center sm:justify-between">
                                        <div class="sm:flex sm:space-x-5 items-center">
                                            <div class="flex-shrink-0">
                                                <BriefcaseIcon class="flex-shrink-0 w-16 h-16" aria-hidden="true" />
                                            </div>
                                            <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                                                <p class="text-xl font-bold text-gray-900 sm:text-2xl">{{
                                                    jobAdvertisement.job_position.title }}
                                                </p>
                                                <p class="text-sm font-medium text-gray-600">Location: {{
                                                    jobAdvertisement.location ?? 'N/A' }}</p>
                                                <p class="text-sm font-medium text-gray-600 mt-1">Status:
                                                    <span v-if="jobAdvertisement.is_active"
                                                        class="inline-flex items-center gap-x-0.5 rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-600 text-ellipsis">
                                                        Active
                                                    </span>
                                                    <span v-else
                                                        class="inline-flex items-center gap-x-0.5 rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-600 text-ellipsis">
                                                        Inactive
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div v-if="filters.tab === 'drafts'" class="mt-5 flex justify-center sm:mt-0">
                                            <button @click="updateDraft(jobAdvertisement.id)"
                                                class="flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                View
                                                draft</button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="filters.tab !== 'drafts'"
                                    class="grid grid-cols-1 divide-y divide-gray-200 border-t border-gray-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                                    <button @click="updateDraft(jobAdvertisement.id)"
                                        class="px-6 py-5 text-center text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-200">
                                        Edit
                                    </button>
                                    <button v-if="jobAdvertisement.is_active" @click="deactivate(jobAdvertisement.id)"
                                        class="px-6 py-5 text-center text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-200">
                                        Deactivate
                                    </button>
                                    <button v-else @click="activate(jobAdvertisement.id)"
                                        class="px-6 py-5 text-center text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-200">
                                        Activate
                                    </button>
                                    <button @click="browseMatches(jobAdvertisement.id)"
                                        class="px-6 py-5 text-center text-sm font-medium text-gray-600 bg-gray-50 hover:bg-gray-200">
                                        <span class=" relative inline-block">
                                            <span>Browse matches</span>
                                            <span
                                                class="absolute -top-2 ml-1 text-xs rounded-full ring-2 ring-red-400 text-red-500 px-1 py-0">{{
                                                    browseMatchesCount[index] }}</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="jobAdvertisements.length === 0" class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <div class="overflow-hidden rounded-lg bg-white shadow">
                                <h2 class="sr-only" id="profile-overview-title">Job Report</h2>
                                <div class="bg-white p-6">
                                    <div>
                                        <div class="items-center">
                                            <div class="mt-4 text-center sm:mt-0 sm:pt-1">
                                                <p class="text-lg text-center font-semibold text-gray-900 sm:text-xl">No
                                                    Posted Jobs
                                                    found
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
