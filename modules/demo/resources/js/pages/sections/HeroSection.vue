<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ArrowRight, Bot, Check, Copy, Terminal } from 'lucide-vue-next';
import { computed, onUnmounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const tab = ref<'cli' | 'agent'>('cli');

const tabs = [
    { key: 'cli' as const, label: 'Using CLI', icon: Terminal },
    { key: 'agent' as const, label: 'Using AI Agent', icon: Bot },
];

const CLI_COMMAND = 'laravel new --using=saucebase/saucebase --phpunit --boost';
const AGENT_PROMPT =
    'I\'m building a SaaS with Saucebase.\n\nFetch https://saucebase-dev.github.io/docs/for-agents.md and treat it as the source of truth for this project.';

const currentIcon = computed(() => (tab.value === 'cli' ? Terminal : Bot));
const currentText = computed(() => (tab.value === 'cli' ? CLI_COMMAND : AGENT_PROMPT));

const copied = ref(false);
let copyTimer: ReturnType<typeof setTimeout> | null = null;

function copyInstall() {
    navigator.clipboard
        .writeText(currentText.value)
        .then(() => {
            toast.success(trans('Copied to clipboard'));
            if (copyTimer) clearTimeout(copyTimer);
            copied.value = true;
            copyTimer = setTimeout(() => (copied.value = false), 2000);
        })
        .catch(() => toast.error(trans('Failed to copy')));
}

onUnmounted(() => {
    if (copyTimer) clearTimeout(copyTimer);
});
</script>

<template>
    <div class="bg-background relative isolate flex h-[85vh] flex-col">
        <main class="mx-auto w-full flex h-full items-center justify-center py-18">
            <div class="relative overflow-hidden md:px-16 lg:px-8">
                <div class="space-y-8">
                    <div>
                        <div class="mb-4 flex justify-center">
                            <span
                                class="inline-flex items-center gap-3 rounded-full border border-border pl-2 pr-5 py-2 text-sm font-medium text-foreground/70 bg-card shadow-xl transition-colors hover:border-secondary">
                                <span
                                    class="rounded-full bg-secondary px-2.5 py-0.5 text-xs font-semibold text-white/90 text-shadow-2xs dark:text-accent-dark">
                                    {{ $t('NEW') }}
                                </span>
                                {{ $t('Introducing Saucebase 2.0') }} -
                                <a href="/blog/vue-or-react-what-about-both"
                                    class="text-secondary hover:text-secondary/80 transition-colors">{{ $t('Learn more')
                                    }} →</a>
                            </span>
                        </div>
                        <h1
                            class="text-primary text-center text-5xl font-bold [text-shadow:0_4px_25px_color-mix(in_oklch,var(--color-primary)_15%,var(--color-background))] md:text-7xl">
                            {{ $t('With Saucebase') }}
                        </h1>
                        <h2 class="text-secondary mt-1 text-center text-4xl font-bold tracking-tight md:text-5xl">
                            {{ $t('Your foundation is ready!') }}
                        </h2>
                    </div>
                    <div>
                        <p class="text-muted-foreground text-center text-xl md:text-2xl">
                            {{ $t('Build production-ready Laravel apps faster than ever.') }}
                        </p>
                        <p class="text-muted-foreground text-center text-xl md:text-2xl">
                            {{ $t('Your recipe first. Modules for everything else') }}
                        </p>
                    </div>

                    <!-- Install command -->
                    <div class="mx-auto mb-1 space-y-4">
                        <!-- Tab toggle -->
                        <div class="flex justify-center">
                            <div
                                class="relative flex items-center rounded-xl bg-gray-100 p-1 shadow-lg dark:bg-white/5">
                                <button v-for="t in tabs" :key="t.key" @click="tab = t.key" :class="[
                                    'relative z-10 inline-flex items-center gap-2 rounded-xl px-6 py-2 font-medium text-sm transition-all duration-200',
                                    tab === t.key
                                        ? 'bg-primary text-white'
                                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white',
                                ]">
                                    <component :is="t.icon" class="size-4" aria-hidden="true" />
                                    {{ $t(t.label) }}
                                </button>
                            </div>
                        </div>

                        <!-- Command block (shared, only icon + text swap) -->
                        <div class="relative mx-auto max-w-xl">
                            <div class="flex items-center rounded-2xl bg-gray-100 p-1 shadow-lg dark:bg-white/5">
                                <div
                                    class="flex items-start gap-3 rounded-xl bg-gray-950 px-4 py-3 shadow-xl dark:bg-gray-900 w-full">
                                    <component :is="currentIcon" class="mt-0.5 size-5 shrink-0 text-gray-500"
                                        aria-hidden="true" />
                                    <p class="flex-1 whitespace-pre-line text-green-400">{{ currentText }}</p>
                                    <button
                                        class="shrink-0 cursor-pointer text-gray-300 transition-colors hover:text-white"
                                        @click="copyInstall">
                                        <Check v-if="copied" class="size-5 text-green-400" />
                                        <Copy v-else class="size-5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="text-muted-foreground mx-auto mt-6 w-full text-center text-sm">
                                <div v-if="tab === 'cli'">
                                    {{ $t('Works with the official Laravel CLI') }} -
                                    <a href="https://laravel.com/docs/13.x/installation"
                                        class="ml-1 inline-flex items-center gap-0.5 font-medium text-red-700 hover:underline dark:text-red-400">
                                        {{ $t('Laravel docs') }}
                                        <ArrowRight class="mt-0.5 size-3 -rotate-45" />
                                    </a>
                                </div>
                                <div v-else>
                                    {{ $t('Works with Claude Code, Cursor, Codex, and most AI coding tools') }} -
                                    <a href="https://saucebase-dev.github.io/docs/for-agents.md"
                                        class="ml-1 inline-flex items-center gap-0.5 font-medium text-secondary hover:underline dark:text-secondary">
                                        {{ $t('Learn more') }}
                                        <ArrowRight class="mt-0.5 size-3 -rotate-45" />
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <svg class="size-full absolute -z-10 inset-0 text-gray-900 dark:text-gray-100 opacity-10 dark:opacity-5"
                width="1440" height="720" viewBox="0 0 1440 720" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path stroke="currentColor" stroke-opacity=".7" d="M-15.227 702.342H1439.7" />
                <circle cx="711.819" cy="372.562" r="308.334" stroke="currentColor" stroke-opacity=".7" />
                <circle cx="16.942" cy="20.834" r="308.334" stroke="currentColor" stroke-opacity=".7" />
                <path stroke="currentColor" stroke-opacity=".7" d="M-15.227 573.66H1439.7M-15.227 164.029H1439.7" />
                <circle cx="782.595" cy="411.166" r="308.334" stroke="currentColor" stroke-opacity=".7" />
            </svg>
        </main>
    </div>
</template>
