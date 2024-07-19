<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'

const props = defineProps({
    userCount: String,
    employerCount: String,
    applicantCount: String,
})

</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-4">
                <h2 class="text-xl font-semibold leading-tight">
                    Dashboard
                </h2>
            </div>
        </template>

        <div class="grid grid-cols-3 gap-8 px-4">
            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Employers</h1>
                <p>{{ employerCount }}</p>
            </div>

            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Applicants</h1>
                <p>{{ applicantCount }}</p>
            </div>

            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Users</h1>
                <p>{{ userCount }}</p>
            </div>
        </div>



    </AuthenticatedLayout>
</template>
