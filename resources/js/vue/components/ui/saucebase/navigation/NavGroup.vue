<script setup lang="ts">
import { SidebarGroup, SidebarMenu } from '@/components/ui/sidebar';
import type { MenuItem } from '@/types/navigation';
import NavItem from './NavItem.vue';

defineProps<{
    items: MenuItem[];
}>();
</script>

<template>
    <SidebarGroup>
        <SidebarMenu>
            <NavItem
                v-for="item in items"
                :key="item.id || item.title"
                :item="item"
            />
        </SidebarMenu>
    </SidebarGroup>
</template>
