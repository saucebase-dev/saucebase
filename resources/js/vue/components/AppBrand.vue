<script setup lang="ts">
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

/**
 * The application's own mark at the top of the sidebar.
 *
 * What fills the `sidebar-brand` slot when no module claims it. A module with something
 * better to put there registers over it, so core never needs to know whether any
 * particular module is installed.
 *
 * The wide logo already carries the name; the square icon takes over when the sidebar
 * collapses to icons.
 */
</script>

<template>
    <SidebarMenu data-testid="app-brand">
        <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
                <Link href="/dashboard">
                    <span
                        class="flex min-w-0 group-data-[collapsible=icon]:hidden"
                    >
                        <AppLogo size="sm" />
                    </span>
                    <span class="hidden group-data-[collapsible=icon]:flex">
                        <AppLogo size="sm" variant="icon" />
                    </span>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
