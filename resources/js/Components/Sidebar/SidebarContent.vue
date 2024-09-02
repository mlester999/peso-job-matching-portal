<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import { DashboardIcon } from '@/Components/Icons/outline'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import { TemplateIcon, UserCircleIcon, UsersIcon, UserGroupIcon, BriefcaseIcon, CalendarIcon, ClipboardCheckIcon, NewspaperIcon, ClipboardListIcon, InboxInIcon } from '@heroicons/vue/outline'
</script>

<template>
    <PerfectScrollbar tagname="nav" aria-label="main" class="relative flex flex-col flex-1 max-h-full gap-4 px-3">
        <SidebarLink title="Dashboard" :href="route('dashboard')" :active="route().current('dashboard')">
            <template #icon>
                <DashboardIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>
        </SidebarLink>

        <template v-if="$page.props.auth.user.admin">
            <SidebarLink title="Users" :href="route('admin.users.index')"
                :active="route().current('admin.users.index')">
                <template #icon>
                    <UserCircleIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Employers" :href="route('admin.employers.index')"
                :active="route().current('admin.employers.index')">
                <template #icon>
                    <UsersIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Applications" :href="route('admin.applications.index')"
                :active="route().current('admin.applications.index')">
                <template #icon>
                    <UserGroupIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Job Positions" :href="route('admin.job-positions.index')"
                :active="route().current('admin.job-positions.index')">
                <template #icon>
                    <BriefcaseIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>
        </template>

        <template v-if="$page.props.auth.user.employer">
            <SidebarLink title="Reports" :href="route('employer.reports.index')"
                :active="route().current('employer.reports.index')">
                <template #icon>
                    <ClipboardListIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Job Ads" :href="route('employer.job-ads.index')"
                :active="route().current('employer.job-ads.index')">
                <template #icon>
                    <CalendarIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Applications" :href="route('employer.applications.index')"
                :active="route().current('employer.applications.index')">
                <template #icon>
                    <UserGroupIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="For Interview" :href="route('employer.for-interview.indexForInterview')"
                :active="route().current('employer.for-interview.indexForInterview')">
                <template #icon>
                    <UsersIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="For Requirements" href="#">
                <template #icon>
                    <NewspaperIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Qualified" href="#">
                <template #icon>
                    <ClipboardCheckIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="For Deployment" href="#">
                <template #icon>
                    <InboxInIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>

            <SidebarLink title="Deployed" href="#">
                <template #icon>
                    <BriefcaseIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
                </template>
            </SidebarLink>
        </template>

        <!-- <SidebarCollapsible title="Components" :active="route().current('components.*')">
            <template #icon>
                <TemplateIcon class="flex-shrink-0 w-6 h-6" aria-hidden="true" />
            </template>

            <SidebarCollapsibleItem :href="route('components.buttons')" title="Buttons"
                :active="route().current('components.buttons')" />
        </SidebarCollapsible> -->

        <!-- Examples -->
        <!--
        => External link example
        <SidebarLink
            title="Github"
            href="https://github.com/kamona-wd/kui-laravel-breeze"
            external
            target="_blank"
        >
        </SidebarLink>

        => Collapsible examples
        <SidebarCollapsible title="Users" :active="$page.url.startsWith('/users')">
            <SidebarCollapsibleItem :href="route('users.index')" title="List" :active="$page.url === '/users/index'" />
            <SidebarCollapsibleItem :href="route('users.create')" title="Create" :active="$page.url === '/users/create'" />
        </SidebarCollapsible>

        <SidebarCollapsible title="Users" :active="usePage().url.value.startsWith('/users')">
            <template #icon>
                <UserIcon
                    class="flex-shrink-0 w-6 h-6"
                    aria-hidden="true"
                />
            </template>

            <SidebarCollapsibleItem :href="route('users.index')" title="List" :active="route().current('users.index')" />
            <SidebarCollapsibleItem :href="route('users.create')" title="Create" :active="route().current('users.create')" />
        </SidebarCollapsible>-->
    </PerfectScrollbar>
</template>
