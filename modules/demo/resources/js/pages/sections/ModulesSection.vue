<script setup lang="ts">
import type { Module } from '@/components/ui/saucebase';
import { ModuleCard, ModuleModal, modules } from '@/components/ui/saucebase';
import { BookOpen } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const selectedFramework = ref<'vue' | 'react'>('vue');
const selectedMod = ref<Module | null>(null);
const modulesVisible = ref(false);
const sectionRef = ref<HTMLElement | null>(null);
const gridSentinelRef = ref<HTMLElement | null>(null);

let observer: IntersectionObserver | null = null;

onMounted(() => {
    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                modulesVisible.value = true;
                observer?.disconnect();
            }
        },
        { threshold: 0.1 },
    );
    if (gridSentinelRef.value) observer.observe(gridSentinelRef.value);
});

onUnmounted(() => observer?.disconnect());

function toggleFramework() {
    selectedFramework.value =
        selectedFramework.value === 'vue' ? 'react' : 'vue';
}

const displayModules = computed(() =>
    modules.map((m) => {
        const supported = (m.frameworks as readonly string[]).includes(
            selectedFramework.value,
        );
        return {
            ...m,
            _supported: supported,
            color: supported ? m.color : '--color-gray-500',
            badge: supported ? m.badge : null,
        } as Module & { _supported: boolean };
    }),
);
</script>

<template>
    <section ref="sectionRef" class="relative mt-16 overflow-hidden bg-slate-900/10">
        <!-- Split-screen framework picker -->
        <div
            class="relative z-0 min-h-120 w-full overflow-hidden mask-[linear-gradient(to_bottom,black_60%,transparent)]">
            <!-- Background: two colored halves with gradient toward center -->
            <div class="absolute inset-0 grid grid-cols-2">
                <div class="bg-linear-to-r from-transparent to-(--vue-color)/95 bg-blend-darken" />
                <div class="bg-linear-to-l from-transparent to-(--react-color)/95 bg-blend-darken" />
            </div>

            <!-- Corner text: constrained to container, aligned to outer edges, anchored to bottom -->
            <div class="relative mx-auto grid min-h-120 max-w-6xl grid-cols-2 items-end px-6 pb-40 sm:px-10 lg:px-8">
                <!-- Vue side -->
                <div class="flex flex-col items-center p-10 text-center text-white lg:p-16">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="shadow-vue size-32 opacity-90" aria-hidden="true">
                        <path d="m12 12.765 5.592-9.437h-3.276L12 7.33v.002L9.688 3.328h-3.28z" />
                        <path d="M18.461 3.332 12 14.235 5.539 3.332H1.992L12 20.672l10.008-17.34z" />
                    </svg>
                    <h3 class="mt-2 text-3xl font-bold shadow-vue">Vue</h3>
                    <p class="mt-1 max-w-xs text-xl leading-relaxed shadow-vue">
                        {{ $t('The progressive framework') }}
                    </p>
                </div>

                <!-- React side -->
                <div class="flex flex-col items-center p-10 text-center text-white lg:p-16">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="shadow-react size-32 opacity-90" aria-hidden="true">
                        <path
                            d="M12 9.46a1.785 1.785 0 1 0 0 3.57 1.785 1.785 0 1 0 0-3.57M7.002 14.794l-.395-.101c-2.934-.741-4.617-2.001-4.617-3.452S3.674 8.53 6.607 7.789l.395-.1.111.391a19.5 19.5 0 0 0 1.136 2.983l.085.178-.085.178c-.46.963-.841 1.961-1.136 2.985zm-.577-6.095c-2.229.628-3.598 1.586-3.598 2.542 0 .954 1.368 1.913 3.598 2.54q.41-1.304.985-2.54a20 20 0 0 1-.985-2.542m10.572 6.095-.11-.392a19.6 19.6 0 0 0-1.137-2.984l-.085-.177.085-.179c.46-.961.839-1.96 1.137-2.984l.11-.39.395.1c2.935.741 4.617 2 4.617 3.453s-1.683 2.711-4.617 3.452zm-.41-3.553c.4.866.733 1.718.987 2.54 2.23-.627 3.599-1.586 3.599-2.54 0-.956-1.368-1.913-3.599-2.542a21 21 0 0 1-.987 2.542" />
                        <path
                            d="m6.419 8.695-.11-.39c-.826-2.908-.576-4.991.687-5.717 1.235-.715 3.222.13 5.303 2.265l.284.292-.284.291a20 20 0 0 0-2.02 2.474l-.113.162-.196.016a19.7 19.7 0 0 0-3.157.509zm1.582-5.529q-.337 0-.589.145c-.828.477-.974 2.138-.404 4.38q1.337-.297 2.696-.417a21 21 0 0 1 1.713-2.123c-1.303-1.267-2.533-1.985-3.416-1.985m7.997 16.984c-1.188 0-2.714-.896-4.298-2.522l-.283-.291.283-.29a20 20 0 0 0 2.021-2.477l.112-.16.194-.019a19.5 19.5 0 0 0 3.158-.507l.395-.1.111.391c.822 2.906.573 4.992-.688 5.718a2 2 0 0 1-1.005.257m-3.415-2.82c1.302 1.267 2.533 1.986 3.415 1.986q.339-.001.589-.145c.829-.478.976-2.142.404-4.384q-1.335.299-2.698.419a20.5 20.5 0 0 1-1.71 2.124" />
                        <path
                            d="m17.58 8.695-.395-.099a19.5 19.5 0 0 0-3.158-.509l-.194-.017-.112-.162A19.6 19.6 0 0 0 11.7 5.434l-.283-.291.283-.29c2.08-2.134 4.066-2.979 5.303-2.265 1.262.727 1.513 2.81.688 5.717zm-3.287-1.421c.954.085 1.858.228 2.698.417.571-2.242.425-3.903-.404-4.381-.824-.477-2.375.253-4.004 1.841q.926 1.005 1.71 2.123M8.001 20.15a2 2 0 0 1-1.005-.257c-1.263-.726-1.513-2.811-.688-5.718l.108-.391.395.1c.964.243 2.026.414 3.158.507l.194.019.113.16c.604.878 1.28 1.707 2.02 2.477l.284.29-.284.291c-1.583 1.627-3.109 2.522-4.295 2.522m-.993-5.362c-.57 2.242-.424 3.906.404 4.384.825.47 2.371-.255 4.005-1.842a21 21 0 0 1-1.713-2.123 21 21 0 0 1-2.696-.419" />
                        <path
                            d="M12 15.313c-.687 0-1.392-.029-2.1-.088l-.196-.017-.113-.162a26 26 0 0 1-1.126-1.769 26 26 0 0 1-.971-1.859l-.084-.177.084-.179q.448-.948.971-1.858c.347-.596.726-1.192 1.126-1.77l.113-.16.196-.018a25 25 0 0 1 4.198 0l.194.019.113.16a25 25 0 0 1 2.1 3.628l.083.179-.083.177a25 25 0 0 1-2.1 3.628l-.113.162-.194.017c-.706.057-1.412.087-2.098.087m-1.834-.904c1.235.093 2.433.093 3.667 0a24.5 24.5 0 0 0 1.832-3.168 24 24 0 0 0-1.832-3.168 24 24 0 0 0-3.667 0 24 24 0 0 0-1.832 3.168 25 25 0 0 0 1.832 3.168" />
                    </svg>
                    <h3 class="mt-2 text-3xl font-bold shadow-react">React</h3>
                    <p class="mt-1 max-w-xs text-xl leading-relaxed shadow-react">
                        {{ $t('The industry standard') }}
                    </p>
                </div>
            </div>

            <!-- Top tab: "Pick your side" -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2">
                <p
                    class="pick-tab bg-background text-foreground relative rounded-b-2xl px-8 py-3 text-sm font-medium tracking-widest uppercase shadow-2xl">
                    {{ $t('Pick your side') }}
                </p>
            </div>

            <!-- Toggle: centered inside picker so it's above the picker but below modules via pointer-events trick -->
            <div class="pointer-events-none absolute inset-0 z-10 flex flex-col items-center justify-center">
                <button type="button"
                    class="pointer-events-auto relative flex h-14 w-28 cursor-pointer items-center rounded-full p-1.5 shadow-[inset_0_2px_6px_rgba(0,0,0,0.35)] ring-5 ring-white/80 backdrop-blur-sm transition-colors duration-300"
                    :class="selectedFramework === 'vue'
                            ? 'bg-[color-mix(in_oklch,var(--vue-color)_90%,transparent)]'
                            : 'bg-[color-mix(in_oklch,var(--react-color)_90%,transparent)]'
                        " @click="toggleFramework">
                    <div class="size-11 rounded-full bg-gray-100 shadow-[inset_0_3px_10px_rgba(255,255,255,1),inset_0_-2px_6px_rgba(0,0,0,0.15),0_4px_8px_rgba(0,0,0,0.3)] transition-transform duration-300"
                        :class="{
                            'translate-x-14': selectedFramework === 'react',
                        }" />
                </button>
            </div>
        </div>

        <!-- Module grid -->
        <div ref="gridSentinelRef"
            class="pointer-events-none relative -mt-140 min-h-200 overflow-hidden px-6 pt-100 pb-40 sm:px-10 lg:px-8">
            <!-- Pattern container: mask lives here so it aligns with the visible box, not the offset top -->
            <div
                class="absolute inset-0 -z-1 overflow-hidden mask-t-from-45% mask-b-from-75% md:mask-r-from-95% md:mask-l-from-95%">
                <div class="pattern-light absolute -inset-10 top-80 md:rotate-[-5deg] md:skew-x-10 dark:hidden" />
                <div class="pattern-dark absolute -inset-10 top-80 hidden md:rotate-[-5deg] md:skew-x-10 dark:block" />
            </div>

            <div v-if="modulesVisible"
                class="pointer-events-auto relative z-10 mx-auto grid max-w-6xl grid-cols-1 gap-8 gap-y-2 pt-8 pb-16 font-mono has-[[data-card]:hover]:*:data-card:opacity-40 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <ModuleCard v-for="(mod, index) in displayModules" :key="mod.id" :module="mod" :index="index"
                    module-class="rotate-[-5deg] skew-x-10" :class="{ 'opacity-35': !mod._supported }" @select="
                        mod._supported ? (selectedMod = $event) : undefined
                        " />
            </div>
        </div>

        <!-- Docs button (always below grid) -->
        <div class="relative z-10 my-8 -mt-36 mb-36 flex justify-center">
            <div class="relative inline-flex">
                <div class="stripe absolute inset-0 translate-y-3 rounded-full [--mod-color:var(--foreground)]" />
                <a href="https://saucebase-dev.github.io/docs/"
                    class="hover:bg-foreground/80 text-background bg-foreground/90 relative flex items-center gap-2 rounded-full px-8 py-4 text-base font-semibold shadow-[0_5px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_9px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)]">
                    <BookOpen class="size-5" aria-hidden="true" />
                    {{ $t('Read the Documentation') }}
                </a>
            </div>
        </div>

        <ModuleModal :selected-mod="selectedMod" @close="selectedMod = null" />
    </section>
</template>

<style scoped>
.shadow-vue {
    filter: drop-shadow(0 4px 8px color-mix(in oklch, var(--vue-color) 85%, black));
}

.shadow-react {
    filter: drop-shadow(0 4px 8px color-mix(in oklch, var(--react-color) 85%, black));
}

.pattern-light {
    background-size: 28px;
    background-position: top left;
    background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.4%22 fill=%22%23011E32%22 fill-opacity=%22.24%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E');
}

.pattern-dark {
    background-size: 28px;
    background-position: top left;
    background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.5%22 fill=%22%23ffffff%22 fill-opacity=%22.15%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E');
}

.pick-tab::before,
.pick-tab::after {
    content: '';
    position: absolute;
    top: 0;
    width: 1rem;
    height: 1rem;
}

.pick-tab::before {
    left: calc(-1rem + 1px);
    background: radial-gradient(circle at 0% 100%, transparent 1rem, var(--color-background) calc(1rem - 2px));
}

.pick-tab::after {
    right: calc(-1rem + 1px);
    background: radial-gradient(circle at 100% 100%, transparent 1rem, var(--color-background) calc(1rem - 2px));
}
</style>
