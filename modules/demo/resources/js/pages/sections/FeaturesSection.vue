<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import {
    BookOpen,
    Braces,
    Component,
    FlaskConical,
    Languages,
    LayoutDashboard,
    Paintbrush,
    Terminal,
} from '@lucide/vue';

const features = [
    {
        name: () => trans('One-command setup'),
        description: () => trans("One command. You're running."),
        icon: Terminal,
    },
    {
        name: () => trans('Full TypeScript'),
        description: () => trans('PHP types generate TypeScript automatically.'),
        icon: Braces,
    },
    {
        name: () => trans('Theming system'),
        description: () => trans('15 themes and a live editor.'),
        icon: Paintbrush,
    },
    {
        name: () => trans('Filament admin'),
        description: () => trans('Admin panel ready on day one.'),
        icon: LayoutDashboard,
    },
    {
        name: () => trans('Testing suite'),
        description: () => trans('PHPUnit and Playwright E2E, pre-configured per module.'),
        icon: FlaskConical,
    },
    {
        name: () => trans('Multi-language'),
        description: () => trans('From day one.'),
        icon: Languages,
    },
    {
        name: () => trans('shadcn UI'),
        description: () => trans('Beautiful components with dark mode built in.'),
        icon: Component,
    },
    {
        name: () => trans('Complete documentation'),
        description: () => trans('Module guides, architecture docs, and AI context files for every module.'),
        icon: BookOpen,
    },
];
</script>

<template>
    <div id="features" class="border-border bg-background/80 relative isolate overflow-hidden border-t py-24 sm:py-32 bg-gray-300/40 dark:bg-gray-900/40">
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <svg aria-hidden="true"
                class="absolute top-0 left-[max(50%,25rem)] h-256 w-512 -translate-x-1/2 mask-[radial-gradient(64rem_64rem_at_top,white,transparent)] stroke-gray-300/70 dark:stroke-gray-700/50">
                <defs>
                    <pattern id="features-pattern" width="200" height="200" x="50%" y="-1"
                        patternUnits="userSpaceOnUse">
                        <path d="M100 200V.5M.5 .5H200" fill="none" />
                    </pattern>
                </defs>
                <svg x="50%" y="-1" class="fill-primary/5 dark:fill-foreground/5 overflow-visible">
                    <path
                        d="M-100.5 0h201v201h-201Z M699.5 0h201v201h-201Z M499.5 400h201v201h-201Z M-300.5 600h201v201h-201Z"
                        stroke-width="0" />
                </svg>
                <rect width="100%" height="100%" fill="url(#features-pattern)" stroke-width="0" />
            </svg>
        </div>
        <div class="mx-auto max-w-7xl px-6 lg:px-2">
            <div class="mx-auto max-w-2xl">

                <div class="lg:max-w-none text-center">
                    <h2 class="text-primary text-2xl font-semibold">
                        {{ $t('Built for developers') }}
                    </h2>
                    <p class="text-foreground mt-2 text-4xl font-semibold tracking-tight text-pretty sm:text-5xl">
                        {{ $t('Everything configured. Nothing missing.') }}
                    </p>
                    <p class="text-muted-foreground mt-6 text-lg/8">
                        {{
                            $t(
                                `A production-ready foundation with the architecture,
                        tooling, and philosophy to support your product long-term.`,
                            )
                        }}
                    </p>
                    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 text-left">
                        <div v-for="feature in features" :key="feature.name()" class="flex items-start gap-3">
                            <div
                                class="bg-primary/10 text-primary mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg">
                                <component :is="feature.icon" class="size-4.5" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-foreground text-base font-semibold">
                                    {{ feature.name() }}
                                </p>
                                <p class="text-muted-foreground mt-0.5 text-sm">
                                    {{ feature.description() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
