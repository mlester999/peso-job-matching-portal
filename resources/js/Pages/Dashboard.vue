<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <template #header>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    Dashboard
                </h2>
            </div>
        </template>

        <div class="grid grid-cols-3 gap-8">
            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Admins</h1>
                <p>0</p>
            </div>

            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Employers</h1>
                <p>0</p>
            </div>

            <div class="p-6 overflow-hidden bg-white rounded-md shadow-md">
                <h1 class="font-semibold">Total Applicants</h1>
                <p>0</p>
            </div>
        </div>



    </AuthenticatedLayout>
</template>
