<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import debounce from 'lodash.debounce'
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import Badge from '@/Components/Badge.vue';
import SelectField from '@/Components/SelectField.vue';
import { EyeIcon } from '@heroicons/vue/solid';
import ApplicationInfo from '@/Components/ApplicationInfo.vue';

const props = defineProps({
    applications: Object,
    pagination: Object,
    filters: Object,
    jobAds: Object,
    jobPositions: Object,
    jobAdvertisementId: String
});

onMounted(() => {
    if (props.jobAdvertisementId) {
        const jobAds = props.jobAds.find(jobAds => jobAds.id === Number(props.jobAdvertisementId))
        const jobAdsSkills = JSON.parse(jobAds.skills);

        props.applications.data.forEach(el => {
            const applicantSkills = JSON.parse(el.skills).skills;
            // Find the intersection (common elements)
            const commonSkills = jobAdsSkills.filter(item => applicantSkills.includes(item));

            // Calculate the percentage of matched items
            const matchPercentage = (commonSkills.length / jobAdsSkills.length) * 100;

            matchedPercentage.value = matchPercentage.toFixed(2);

            applicantsPercentages.value.push(matchedPercentage.value);
        })
    }
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

const industries = [
    { value: "Agriculture", text: "Agriculture" },
    { value: "ActivitiesOfPrivateHouseholds", text: "Activities Of Private Households" },
    { value: "Construction", text: "Construction" },
    { value: "Education", text: "Education" },
    { value: "ElectricityGasAndWaterSupply", text: "Electricity, Gas And Water Supply" },
    { value: "ExtraTerritorialOrganizationsAndBodies", text: "Extra-Territorial Organizations And Bodies" },
    { value: "FinancialIntermediation", text: "Financial Intermediation" },
    { value: "Fishing", text: "Fishing" },
    { value: "HealthAndSocialWork", text: "Health And Social Work" },
    { value: "HotelsAndRestaurants", text: "Hotels And Restaurants" },
    { value: "IT", text: "IT BPO" },
    { value: "Manufacturing", text: "Manufacturing" },
    { value: "MiningAndQuarrying", text: "Mining And Quarrying" },
    { value: "PublicAdministrationAndDefense", text: "Public Administration And Defense" },
    { value: "RealEstateRentingAndBusinessActivities", text: "Real Estate, Renting And Business Activities" },
    { value: "TransportStorageAndCommunication", text: "Transport, Storage And Communication" },
    { value: "WholesaleAndRetailTrade", text: "Wholesale And Retail Trade" }
];

const roles = [
    { id: 'full-time', title: 'Full Time' },
    { id: 'part-time', title: 'Part Time' },
    { id: 'contract', title: 'Contract' },
    { id: 'temporary', title: 'Temporary' },
]

const page = usePage()

let search = ref(props.filters.search);
let classification = ref(props.filters.classification);
let location = ref(props.filters.location);
let jobAdvertisement = ref(props.filters.jobAdvertisement || props.jobAdvertisementId);
let listedTime = ref(props.filters.listedTime);

const selectedApplication = ref(null);

const matchedPercentage = ref('')

let applicantsPercentages = ref([])

const updateInfo = (applicationId) => {
    if (page.props.auth.user.employer) {
        router.get(`/employer/applications/view/${applicationId}`);
    } else if (page.props.auth.user.admin) {
        router.get(`/admin/applications/view/${applicationId}`);
    }
}

const truncate = (text) => {
    return text.length > 20 ? text.substring(0, 20) + '...' : text;
};

const selectApplication = (application) => {
    selectedApplication.value = application;
}
const query = {};
watch(
    search,
    debounce((value) => {
        if (value) {
            query.search = value;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        } else {
            query.search = null;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        }
        if (page.props.auth.user.employer) {
            router.get(`/employer/applications`, query, {
                preserveState: true,
                replace: true,
            });
        } else if (page.props.auth.user.admin) {
            router.get(`/admin/applications`, query, {
                preserveState: true,
                replace: true,
            });
        }


    }, 500)
);

watch(
    classification,
    debounce((value) => {
        if (value) {
            query.classification = value;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        } else {
            query.classification = null;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        }
        if (page.props.auth.user.employer) {
            router.get(`/employer/applications`, query, {
                preserveState: true,
                replace: true,
            });
        } else if (page.props.auth.user.admin) {
            router.get(`/admin/applications`, query, {
                preserveState: true,
                replace: true,
            });
        }


    }, 500)
);

watch(
    location,
    debounce((value) => {
        if (value) {
            query.location = value;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        } else {
            query.location = null;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        }
        if (page.props.auth.user.employer) {
            router.get(`/employer/applications`, query, {
                preserveState: true,
                replace: true,
            });
        } else if (page.props.auth.user.admin) {
            router.get(`/admin/applications`, query, {
                preserveState: true,
                replace: true,
            });
        }


    }, 500)
);

watch(
    jobAdvertisement,
    debounce((value) => {
        if (value) {
            applicantsPercentages.value = [];
            const jobAds = props.jobAds.find(jobAds => jobAds.id === Number(value))
            const jobAdsSkills = JSON.parse(jobAds.skills);

            props.applications.data.forEach(el => {
                const applicantSkills = JSON.parse(el.skills).skills;
                // Find the intersection (common elements)
                const commonSkills = jobAdsSkills.filter(item => applicantSkills.includes(item));

                // Calculate the percentage of matched items
                const matchPercentage = (commonSkills.length / jobAdsSkills.length) * 100;

                matchedPercentage.value = matchPercentage.toFixed(2);

                applicantsPercentages.value.push(matchedPercentage.value);
            })
            query.jobAdvertisementId = value;
            router.get(`/employer/applications`, query, {
                preserveState: true,
                replace: true,
            });
        } else {
            query.jobAdvertisementId = null;
            applicantsPercentages.value = [];
            router.get(`/employer/applications`, query, {
                preserveState: true,
                replace: true,
            });
        }
    }, 500)
);

watch(
    listedTime,
    debounce((value) => {
        if (value) {
            query.listedTime = value;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        } else {
            query.listedTime = null;
            query.jobAdvertisementId = null
            jobAdvertisement.value = null;
        }
        if (page.props.auth.user.employer) {
            router.get(`/employer/applications`, query, {
                preserveState: true,
                replace: true,
            });
        } else if (page.props.auth.user.admin) {
            router.get(`/admin/applications`, query, {
                preserveState: true,
                replace: true,
            });
        }


    }, 500)
);



</script>

<template>
    <AuthenticatedLayout title="Applications">
        <template #header>
            <div class="px-4">
                <div class="sm:flex sm:items-center my-4">
                    <div class="sm:flex-auto">
                        <h2 class="text-xl font-semibold leading-tight">
                            Applications
                        </h2>
                        <!-- <p class="mt-2 text-sm text-gray-700">A list of all the applications in this portal including
                            their
                            name, title, email and role.</p> -->
                    </div>
                </div>
                <div class="sm:flex sm:items-center sm:justify-center">
                    <div class="sm:flex-auto">
                        <Input v-model="search" placeholder="Search for application..." type="search" />
                    </div>

                    <div class="space-y-2">
                        <div class="mx-4 -mt-1">
                            <SelectField id="classification" v-model="classification">
                                <option value="" selected>~ Select Classification ~
                                </option>
                                <option v-for="(industry, index) in industries" :key="index" :value="industry.value">
                                    {{ industry.text }}
                                </option>
                            </SelectField>
                        </div>

                        <div class="mx-4 -mt-1">
                            <SelectField id="location" v-model="location">
                                <option value="" selected>~ Select Location ~
                                </option>
                                <option v-for="(barangay, index) in barangays" :key="index" :value="barangay">
                                    {{ barangay }}
                                </option>
                            </SelectField>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="-mt-1">
                            <SelectField id="jobAdvertisement" v-model="jobAdvertisement">
                                <option value="" selected>~ Select Job Advertisement ~
                                </option>
                                <option v-for="(jobAdvertisement, index) in props.jobAds" :key="index"
                                    :value="jobAdvertisement.id">
                                    {{ jobAdvertisement.job_position.title }}
                                </option>
                            </SelectField>
                        </div>

                        <div class="-mt-1">
                            <SelectField id="listedTime" v-model="listedTime">
                                <option value="" selected>~ Select Listed Time ~
                                </option>
                                <option value="Any Time">Any Time</option>
                                <option value="Today">Today</option>
                                <option value="Last 3 Days">Last 3 Days</option>
                                <option value="Last Week">Last Week</option>
                                <option value="Last Month">Last Month</option>
                            </SelectField>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flow-root grid grid-cols-6">
                    <div class="flex flex-col col-span-2">
                        <nav class="h-[calc(100vh-300px)] overflow-y-auto flex-grow" aria-label="Directory">
                            <div class="relative">
                                <ul role="list" class="divide-y divide-gray-100">
                                    <h1 class="font-bold text-md mb-2">List of Applications: </h1>
                                    <li v-for="(application, index) in props.applications.data" :key="application.id"
                                        class="flex gap-x-4 px-3 py-2 justify-between items-center">
                                        <div class="min-w-0 border-b">
                                            <p class="text-sm font-semibold leading-6 text-gray-900">{{
                                                application.first_name }} {{ application.last_name }}
                                            </p>

                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">{{
                                                application.email }}
                                            </p>
                                            <p class="mt-1 truncate text-xs leading-5 text-gray-500">{{
                                                JSON.parse(application.skills).jobPositionTitle }}
                                            </p>
                                            <p v-if="applicantsPercentages.length > 0"
                                                class="mt-1 truncate text-xs leading-5 text-gray-900 mb-1 font-semibold">
                                                Matched:
                                                <span v-if="Number(applicantsPercentages[index]) === 50"
                                                    class="inline-flex items-center gap-x-0.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 text-ellipsis">{{
                                                        applicantsPercentages[index] }}%</span>
                                                <span v-else-if="Number(applicantsPercentages[index]) < 50"
                                                    class="inline-flex items-center gap-x-0.5 rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-600 text-ellipsis">{{
                                                        applicantsPercentages[index] }}%</span>
                                                <span v-else-if="Number(applicantsPercentages[index]) > 50"
                                                    class="inline-flex items-center gap-x-0.5 rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-600 text-ellipsis">{{
                                                        applicantsPercentages[index] }}%</span>
                                            </p>
                                        </div>
                                        <button @click="selectApplication(application)" type="button">
                                            <EyeIcon class="h-6 w-6" />
                                        </button>
                                    </li>

                                    <li v-if="props.applications.data.length === 0"
                                        class="flex gap-x-4 px-3 py-2 justify-between items-center">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold leading-6 text-gray-900">No Applications
                                                Found
                                            </p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>

                    <div
                        class="flex flex-col h-[calc(100vh-300px)] overflow-y-auto col-start-3 col-span-5 bg-white p-8">
                        <ApplicationInfo v-if="selectedApplication" :application="selectedApplication" />
                        <div v-else class="p-4 overflow-y-auto">
                            <div>
                                <div class="flex items-center justify-center">
                                    <h2 class="text-xl font-semibold leading-tight">
                                        Please select an applicant to view info
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
