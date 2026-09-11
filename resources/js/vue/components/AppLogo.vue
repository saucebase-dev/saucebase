<script setup lang="ts">
import { useSettings } from '@/composables/useSettings';
import { computed } from 'vue';

/**
 * The application's brand, as artwork.
 *
 * Two variants, because two shapes of slot exist: the wide lockup, which carries the
 * name inside the artwork, and the square mark, for holes too narrow to read a name in —
 * a collapsed sidebar, a workspace row.
 *
 * Nothing here renders text. The name is in the image.
 */
const brand = computed(() => useSettings().value.general);

const props = withDefaults(
    defineProps<{
        size?: Size;
        variant?: 'logo' | 'icon';
    }>(),
    { size: 'md', variant: 'logo' },
);

type Size = 'sm' | 'md' | 'lg' | 'xl' | 'xxl';

const heights: Record<Size, string> = {
    sm: 'h-8',
    md: 'h-12',
    lg: 'h-16',
    xl: 'h-20',
    xxl: 'h-30',
};

const squares: Record<Size, string> = {
    sm: 'w-8',
    md: 'w-12',
    lg: 'w-16',
    xl: 'w-20',
    xxl: 'w-30',
};

const isIcon = computed(() => props.variant === 'icon');

// Wide artwork keeps its height and lets the width follow; a mark is square.
const classes = computed(() =>
    isIcon.value
        ? `${heights[props.size]} ${squares[props.size]} object-contain`
        : `${heights[props.size]} w-auto max-w-full object-contain`,
);

const onLight = computed(() =>
    isIcon.value
        ? brand.value.site_icon_on_light
        : brand.value.site_logo_on_light,
);
const onDark = computed(() =>
    isIcon.value
        ? brand.value.site_icon_on_dark
        : brand.value.site_logo_on_dark,
);
</script>

<template>
    <!--
        Both variants render and CSS picks. Resolving the theme in JavaScript would flash
        on hydration: `appearance` may be `system`, which the server cannot answer, so SSR
        would embed the light artwork and swap it on the client. The inline script in
        app.blade.php sets `.dark` before first paint, so CSS is right from frame one.
    -->
    <img
        :src="onLight"
        :alt="brand.site_name"
        :class="[classes, 'dark:hidden']"
    />
    <img
        :src="onDark"
        :alt="brand.site_name"
        :class="[classes, 'not-dark:hidden']"
    />
</template>
