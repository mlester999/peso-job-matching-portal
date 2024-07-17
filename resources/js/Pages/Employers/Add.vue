<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue'
import Button from '@/Components/Button.vue'
import { GithubIcon } from '@/Components/Icons/brands'

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
</script>

<template>
    <AuthenticatedLayout title="Add Employer">
        <template #header>
            <form>
                <div class="space-y-12 sm:space-y-16 px-4">
                    <div>
                        <h2 class="text-xl font-semibold leading-tight">
                            Add Employer
                        </h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-600">Use a permanent address where you can
                            receive mail.</p>

                        <div
                            class="mt-10 space-y-8 border-b border-gray-900/10 pb-12 sm:space-y-0 sm:divide-y sm:divide-gray-900/10 sm:border-t sm:pb-0">
                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="first-name"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">
                                    Name</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <input type="text" name="first-name" id="first-name" autocomplete="given-name"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-xs sm:text-sm sm:leading-6" />
                                </div>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="email"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">Email
                                    address</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <input id="email" name="email" type="email" autocomplete="email"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-md sm:text-sm sm:leading-6" />
                                </div>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="province"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">Province</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <select id="province" name="province" autocomplete="province-name" disabled
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                        <option>Laguna</option>
                                    </select>
                                </div>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="city"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">City</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <select id="city" name="city" autocomplete="city-name" disabled
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                        <option>Cabuyao</option>
                                    </select>
                                </div>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="barangay"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">Barangay</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <select id="barangay" name="barangay" autocomplete="barangay-name"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                        <option v-for="(barangay, index) in barangays" :key="index">
                                            {{ barangay }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="street-address"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">Street
                                    address</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <input type="text" name="street-address" id="street-address"
                                        autocomplete="street-address"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-xl sm:text-sm sm:leading-6" />
                                </div>
                            </div>

                            <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:py-6">
                                <label for="postal-code"
                                    class="block text-sm font-medium leading-6 text-gray-900 sm:pt-1.5">ZIP / Postal
                                    code</label>
                                <div class="mt-2 sm:col-span-2 sm:mt-0">
                                    <input type="text" name="postal-code" id="postal-code" autocomplete="postal-code"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:max-w-xs sm:text-sm sm:leading-6" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <button type="button" class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                    <button type="submit"
                        class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Save</button>
                </div>
            </form>
        </template>
    </AuthenticatedLayout>
</template>
