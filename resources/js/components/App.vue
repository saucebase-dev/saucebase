<script setup lang="ts">
import DynamicDialog from '@/components/DynamicDialog.vue';
import { Toaster } from '@/components/ui/sonner';
</script>
<template>
    <slot />
    <Toaster />
    <DynamicDialog />
    <!-- Global components can be added here -->
</template>
