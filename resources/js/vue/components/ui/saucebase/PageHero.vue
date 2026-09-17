<script setup lang="ts">
import { computed, type Component } from 'vue';

/**
 * The banner at the top of a public page: an icon, a title, a short
 * description, and optional actions on the right.
 *
 * The gradient is built from opacities of the primary token rather than a
 * numbered shade, because the theme only defines the base colours.
 */
const props = defineProps<{
    title: string;
    description?: string;
    icon?: Component;
    testId?: string;
    /** Match the width of the content below it. */
    width?: '3xl' | '5xl' | '6xl';
}>();

// Written out rather than built from the prop: Tailwind only keeps classes it
// can read in the source.
const WIDTHS = {
    '3xl': 'max-w-3xl',
    '5xl': 'max-w-5xl',
    '6xl': 'max-w-6xl',
} as const;

const widthClass = computed(() => WIDTHS[props.width ?? '6xl']);
</script>

<template>
    <section
        :data-testid="testId ?? 'page-hero'"
        class="from-primary/25 dark:from-primary-900/70 text-foreground bg-linear-to-b to-transparent pt-8"
    >
        <div
            :class="[
                'mx-auto flex w-full flex-col items-start gap-6 px-6 pt-20 pb-6 sm:flex-row sm:items-center sm:justify-between',
                widthClass,
            ]"
        >
            <div class="flex items-center gap-5">
                <div
                    v-if="icon"
                    class="bg-primary dark:bg-foreground/5 dark:text-foreground rounded-full p-7 text-white backdrop-blur-sm"
                >
                    <component :is="icon" class="size-14" />
                </div>

                <div>
                    <h1
                        class="text-primary dark:text-foreground text-4xl font-bold tracking-tight"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-muted-foreground mt-2 max-w-2xl"
                    >
                        {{ description }}
                    </p>
                </div>
            </div>

            <div v-if="$slots.actions" class="w-full shrink-0 sm:w-auto">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
