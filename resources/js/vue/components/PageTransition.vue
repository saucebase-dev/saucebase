<template>
    <Transition name="page" mode="out-in" appear>
        <div :key="$page.url" class="page-transition-wrapper">
            <slot />
        </div>
    </Transition>
</template>

<style scoped>
.page-transition-wrapper {
    width: 100%;
    height: 100%;
}

.page-enter-active,
.page-leave-active {
    transition: opacity 0.3s ease-in-out;
}

.page-enter-from {
    opacity: 0;
}

.page-leave-to {
    opacity: 0;
}

/* Respect reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
    .page-enter-active,
    .page-leave-active {
        transition: none;
    }
}
</style>
