<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { computed, type Component } from 'vue';
import IconAlertCircle from '~icons/lucide/alert-circle';
import IconAlertTriangle from '~icons/lucide/alert-triangle';
import IconCheckCircle from '~icons/lucide/check-circle';
import IconInfo from '~icons/lucide/info';

interface Props {
    message: string | unknown;
    variant?: 'success' | 'info' | 'warning' | 'error' | 'default';
    appearance?: 'filled' | 'bordered' | 'outlined';
    icon?: Component;
    hideIcon?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'error',
    appearance: 'filled',
    hideIcon: false,
});

const defaultIcons: Record<string, Component> = {
    success: IconCheckCircle,
    info: IconInfo,
    warning: IconAlertTriangle,
    error: IconAlertCircle,
    default: IconInfo,
};

const currentIcon = computed(() => {
    if (props.hideIcon) return null;
    if (props.icon) return props.icon;
    return defaultIcons[props.variant];
});

const baseVariant = computed(() => {
    // Map our variants to shadcn variants
    if (props.variant === 'error') return 'destructive';
    return 'default';
});

const variantStyles = {
    success: {
        filled: 'border-green-700 bg-green-700 text-white [&>svg]:text-white *:data-[slot=alert-description]:text-white',
        bordered:
            'bg-green-50 text-green-800 border-green-200 dark:bg-green-950 dark:text-green-200 [&>svg]:text-green-800 dark:[&>svg]:text-green-200',
        outlined:
            'bg-transparent border-2 border-green-700 text-green-600 dark:text-green-400 [&>svg]:text-green-600 dark:[&>svg]:text-green-400',
    },
    info: {
        filled: 'border-blue-500 bg-blue-500 text-white [&>svg]:text-white *:data-[slot=alert-description]:text-white',
        bordered:
            'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950 dark:text-blue-200 [&>svg]:text-blue-800 dark:[&>svg]:text-blue-200',
        outlined:
            'bg-transparent border-2 border-blue-500 text-blue-600 dark:text-blue-400 [&>svg]:text-blue-600 dark:[&>svg]:text-blue-400',
    },
    warning: {
        filled: 'border-yellow-500 bg-yellow-500 text-black [&>svg]:text-black *:data-[slot=alert-description]:text-black',
        bordered:
            'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-950 dark:text-yellow-200 [&>svg]:text-yellow-800 dark:[&>svg]:text-yellow-200',
        outlined:
            'bg-transparent border-2 border-yellow-500 text-yellow-600 dark:text-yellow-400 [&>svg]:text-yellow-600 dark:[&>svg]:text-yellow-400',
    },
    error: {
        filled: 'border-red-500 bg-red-500 text-white [&>svg]:text-white *:data-[slot=alert-description]:text-white',
        bordered:
            'bg-red-50 text-red-800 border-red-200 dark:bg-red-950 dark:text-red-200 [&>svg]:text-red-800 dark:[&>svg]:text-red-200',
        outlined:
            'bg-transparent border-2 border-red-500 text-red-600 dark:text-red-400 [&>svg]:text-red-600 dark:[&>svg]:text-red-400',
    },
    default: {
        filled: '',
        bordered: '',
        outlined: 'bg-transparent border-2',
    },
};

const customClasses = computed(() => {
    return variantStyles[props.variant]?.[props.appearance] || '';
});
</script>

<template>
    <Alert
        v-if="message"
        :variant="baseVariant"
        :class="customClasses"
        class="mb-4"
    >
        <component :is="currentIcon" v-if="currentIcon" class="h-4 w-4" />
        <AlertDescription>
            {{ message }}
        </AlertDescription>
    </Alert>
</template>
