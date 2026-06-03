<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <Separator
        data-slot="sidebar-separator"
        data-sidebar="separator"
        :class="cn('bg-sidebar-border mx-2 w-auto', props.class)"
    >
        <slot />
    </Separator>
</template>
