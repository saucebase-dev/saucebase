<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import PageTransition from '@/components/PageTransition.vue';
import ThemeSelector from '@/components/ThemeSelector.vue';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { useSidebarState } from '@/composables/useSidebarState';
import { Breadcrumb as BreadcrumbType } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    title?: string;
    breadcrumbs?: BreadcrumbType[];
}>();

// Persist sidebar state across Inertia navigation
const { isOpen } = useSidebarState();

// Get auto-generated breadcrumbs from Inertia props
const page = usePage();

// Use manual breadcrumbs if provided, otherwise use auto-generated ones
const displayBreadcrumbs = computed(() => {
    if (props.breadcrumbs?.length) return props.breadcrumbs;
    return page.props.breadcrumbs || [];
});
</script>

<template>
    <SidebarProvider v-model:open="isOpen">
        <Head :title="title" />
        <AppSidebar />
        <SidebarInset>
            <header
                class="flex h-14 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14"
            >
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator
                        orientation="vertical"
                        class="mr-2 data-[orientation=vertical]:h-4"
                    />
                    <Breadcrumb v-if="props.title || displayBreadcrumbs.length">
                        <BreadcrumbList>
                            <template v-if="displayBreadcrumbs.length">
                                <template
                                    v-for="(
                                        breadcrumb, index
                                    ) in displayBreadcrumbs"
                                    :key="index"
                                >
                                    <BreadcrumbItem>
                                        <BreadcrumbLink
                                            v-if="breadcrumb.url"
                                            as-child
                                        >
                                            <Link :href="breadcrumb.url">
                                                {{
                                                    $t(
                                                        breadcrumb.attributes
                                                            ?.label ||
                                                            breadcrumb.title,
                                                    )
                                                }}
                                            </Link>
                                        </BreadcrumbLink>
                                        <BreadcrumbPage v-else>
                                            {{
                                                $t(
                                                    breadcrumb.attributes
                                                        ?.label ||
                                                        breadcrumb.title,
                                                )
                                            }}
                                        </BreadcrumbPage>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator
                                        v-if="
                                            index <
                                            displayBreadcrumbs.length - 1
                                        "
                                    />
                                </template>
                            </template>
                            <BreadcrumbItem v-else-if="props.title">
                                <BreadcrumbPage>
                                    {{ $t(props.title) }}
                                </BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>
                </div>
                <div class="ml-auto pr-4">
                    <ThemeSelector mode="standalone" />
                </div>
            </header>

            <!-- Page Heading -->
            <header
                v-if="$slots.header"
                class="flex h-16 shrink-0 items-center gap-2 dark:bg-gray-800"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <PageTransition>
                    <slot />
                </PageTransition>
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>
