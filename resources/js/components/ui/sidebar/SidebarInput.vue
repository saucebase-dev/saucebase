<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <Input
        data-slot="sidebar-input"
        data-sidebar="input"
        :class="cn('bg-background h-8 w-full shadow-none', props.class)"
    >
        <slot />
    </Input>
</template>
