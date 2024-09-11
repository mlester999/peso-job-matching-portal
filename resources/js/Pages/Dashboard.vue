<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
import { Bar } from 'vue-chartjs'
import BarChart from '@/Components/BarChart.vue';
import LineChart from '@/Components/LineChart.vue';

const props = defineProps({
    userCount: String,
    employerCount: String,
    applicantCount: String,
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
                <h1 class="font-semibold">Total Employers</h1>
                <p>{{ employerCount }}</p>
            </div>

            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Applicants</h1>
                <p>{{ applicantCount }}</p>
            </div>

            <div v-if="$page.props.auth.user.admin" class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Users</h1>
                <p>{{ userCount }}</p>
            </div>
        </div>

        <div class="p-4 grid grid-cols-2 gap-8">
            <div class="p-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between my-4">
                    <h2 class="text-xl font-semibold leading-tight">
                        Bar Chart
                    </h2>
                </div>
                <BarChart />
            </div>

            <div class="p-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between my-4">
                    <h2 class="text-xl font-semibold leading-tight">
                        Line Chart
                    </h2>
                </div>
                <LineChart />
            </div>
        </div>



    </AuthenticatedLayout>
</template>
