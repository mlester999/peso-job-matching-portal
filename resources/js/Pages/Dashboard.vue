<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Bar } from 'vue-chartjs'
import BarChart from '@/Components/BarChart.vue';
import LineChart from '@/Components/LineChart.vue';
import { ref } from 'vue';
import { format, eachMonthOfInterval } from 'date-fns';

const props = defineProps({
    userCount: String,
    employerCount: String,
    applicantCount: String,
    jobAdvertisementCount: String,
    applicationCount: String,
    qualifiedApplicants: Array,
    disqualifiedApplicants: Array,
    jobAdvertisements: Array,
})

// Generate all months using date-fns
const months = eachMonthOfInterval({
    start: new Date(2024, 0, 1), // Start from January of the current year
    end: new Date(2024, 11, 31)  // End at December of the current year
}).map((date) => format(date, 'MMMM')); // Format each month as full month name

const barChartData = ref({
    labels: months,
    datasets: [

        {
            label: 'Deployed Applicants',
            backgroundColor: '#0066ff',
            data: props.qualifiedApplicants
        },

        {
            label: 'Disqualified Applicants',
            backgroundColor: '#f87979',
            data: props.disqualifiedApplicants
        },
    ]
})

const barChartOptions = ref({
    responsive: true,
    scales: {
        y: {
            ticks: {
                stepSize: 1,
                beginAtZero: true,
            },
        }
    }
})

const lineChartData = ref({
    labels: months,
    datasets: [
        {
            label: 'Job Advertisements',
            backgroundColor: '#0066ff',
            data: props.jobAdvertisements
        }
    ]
})

const lineChartOptions = ref({
    responsive: true,
    scales: {
        y: {
            ticks: {
                stepSize: 1,
                beginAtZero: true,
            },
        }
    }
})

</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-4 my-4">
                <h2 class="text-xl font-semibold leading-tight">
                    Dashboard
                </h2>
            </div>
        </template>

        <div class="grid grid-cols-3 gap-8 px-4">
            <div v-if="$page.props.auth.user.admin" class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <p class="font-semibold text-sm lg:text-lg">Total Employers</p>
                <p>{{ employerCount }}</p>
            </div>

            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <p class="font-semibold text-sm lg:text-lg">Total Applicants</p>
                <p>{{ applicantCount }}</p>
            </div>

            <div v-if="$page.props.auth.user.employer" class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <p class="font-semibold text-sm lg:text-lg">Total Applications</p>
                <p>{{ applicationCount }}</p>
            </div>

            <div v-if="$page.props.auth.user.employer" class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <p class="font-semibold text-sm lg:text-lg">Total Job Advertisements</p>
                <p>{{ jobAdvertisementCount }}</p>
            </div>

            <div v-if="$page.props.auth.user.admin" class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <p class="font-semibold text-sm lg:text-lg">Total Users</p>
                <p>{{ userCount }}</p>
            </div>
        </div>

        <div v-if="$page.props.auth.user.employer" class="p-4 grid grid-cols-2 gap-8">
            <div class="p-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between my-4">
                    <h2 class="text-sm lg:text-xl font-semibold leading-tight">
                        Applications Chart
                    </h2>
                </div>
                <BarChart :chartData="barChartData" :chartOptions="barChartOptions" />
            </div>

            <div class="p-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between my-4">
                    <h2 class="text-sm lg:text-xl font-semibold leading-tight">
                        Job Advertisements Chart
                    </h2>
                </div>
                <LineChart :chartData="lineChartData" :chartOptions="lineChartOptions" />
            </div>
        </div>



    </AuthenticatedLayout>
</template>
