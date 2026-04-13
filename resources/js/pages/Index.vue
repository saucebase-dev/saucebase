<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import {
    BarChart3,
    Bell,
    Blocks,
    BookOpen,
    Check,
    Copy,
    CreditCard,
    Lightbulb,
    Lock,
    Map,
    Megaphone,
    Newspaper,
    Palette,
    Settings2,
    Terminal,
    Webhook,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

const BG_GRAY_COLOR = 'bg-gray-500';

const modules = [
    {
        id: 'auth',
        title: 'Auth',
        description:
            'Complete authentication system with login, registration, magic link (passwordless), password reset, email verification, and OAuth integration (Google, GitHub).',
        icon: Lock,
        bg: 'bg-violet-600',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/auth',
        features: [
            'Login & Register',
            'Email Verification',
            'OAuth',
            'Magic Link',
            'Security',
        ],
    },
    {
        id: 'settings',
        title: 'Settings',
        description:
            'Flexible settings management for user preferences and system-wide configuration with validation and caching.',
        icon: Settings2,
        bg: 'bg-sky-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/settings',
        features: ['User Preferences', 'System Config', 'Caching'],
    },
    {
        id: 'billing',
        title: 'Billing',
        description:
            'Subscription management and payment processing via Stripe with checkout sessions, billing portal, invoices, and webhook processing.',
        icon: CreditCard,
        bg: 'bg-green-600',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/billing',
        features: ['Checkout', 'Billing Portal', 'Invoices', 'Webhooks'],
    },
    {
        id: 'roadmap',
        title: 'Roadmap',
        description:
            'Public roadmap with feature requests, voting, moderation, six statuses, and a Filament admin panel.',
        icon: Map,
        bg: 'bg-amber-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/roadmap',
        features: ['Feature Requests', 'Voting', 'Admin Panel'],
    },
    {
        id: 'announcements',
        title: 'Announcements',
        description:
            'Site-wide announcement banners with scheduling, audience targeting, and cookie-based dismissal, managed from the Filament admin panel.',
        icon: Megaphone,
        bg: 'bg-indigo-500',
        badge: 'free',
        href: 'https://saucebase-dev.github.io/docs/modules/announcements',
        features: [
            'Banners',
            'Scheduling',
            'Audience Targeting',
            'Dismissal',
            'Admin Panel',
        ],
    },
    {
        id: 'themes',
        title: 'Themes',
        description:
            'Global visual editor for colors, typography, radius, and shadows. Bake a complete design theme into CSS variables without writing a single line of code.',
        icon: Palette,
        bg: 'bg-rose-500',
        badge: 'new',
        href: 'https://saucebase-dev.github.io/docs/modules/themes',
        features: [
            'Color Palette',
            'Typography',
            'Radius & Shadows',
            'CSS Export',
            'No Code',
        ],
    },
    {
        id: 'custom',
        title: 'Your Module',
        description:
            'Build and install your own modules with a single Artisan command. Full ownership — the scaffolded code lives in your repo and is yours to modify freely.',
        icon: Lightbulb,
        bg: BG_GRAY_COLOR,
        badge: 'custom',
        href: 'https://saucebase-dev.github.io/docs/fundamentals/modules',
        features: [
            'Single Command',
            'Full Ownership',
            'Any Stack',
            'Copy & Own',
        ],
    },
    {
        id: 'blog',
        title: 'Blog',
        description:
            'Full-featured blog with posts, categories, tags, and a Filament admin panel for content management.',
        icon: Newspaper,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: [
            'Posts',
            'Categories & Tags',
            'Admin Panel',
            'SEO Optimized',
        ],
    },
    {
        id: 'webhooks',
        title: 'Webhooks',
        description:
            'Send reliable HTTP callbacks to external services when events occur in your app, with delivery logs and retry handling.',
        icon: Webhook,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['Event Triggers', 'Delivery Logs', 'Retry Handling'],
    },
    {
        id: 'integrations',
        title: 'Integrations',
        description:
            'Connect your app with third-party services like Slack, Zapier, and more through a unified integration layer.',
        icon: Blocks,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['Slack', 'Zapier', 'Unified Layer'],
    },
    {
        id: 'notifications',
        title: 'Notifications',
        description:
            'In-app and email notifications with templates, user preferences, and delivery tracking for every channel.',
        icon: Bell,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['In-App', 'Email', 'Templates', 'Preferences'],
    },
    {
        id: 'analytics',
        title: 'Analytics',
        description:
            'Track pageviews, custom events, and user behavior with a privacy-friendly built-in dashboard — no third-party scripts.',
        icon: BarChart3,
        bg: BG_GRAY_COLOR,
        badge: 'coming-soon',
        href: null,
        features: ['Pageviews', 'Custom Events', 'Dashboard', 'Privacy First'],
    },
];

type Module = (typeof modules)[number];

const selectedMod = ref<Module | null>(null);

// Derive CSS var from bg class: 'bg-violet-600' → { '--mod-color': 'var(--color-violet-600)' }
const modStyle = (mod: { bg: string }) => ({
    '--mod-color': `var(--color-${mod.bg.slice(3)})`,
});

const stripeClasses =
    '[--pattern-fg:var(--mod-color)]/19 bg-[color:var(--mod-color)]/7 bg-[image:repeating-linear-gradient(-45deg,_var(--pattern-fg)_0px,_var(--pattern-fg)_1.5px,_transparent_1.5px,_transparent_5px)] border border-[color:var(--mod-color)]/25';

const copied = ref(false);

function copyCommand() {
    if (!selectedMod.value) return;
    navigator.clipboard
        .writeText(installCommand(selectedMod.value))
        .then(() => {
            toast.success(trans('Copied to clipboard'));
            copied.value = true;
            setTimeout(() => (copied.value = false), 2000);
        })
        .catch(() => toast.error(trans('Failed to copy')));
}

const installCommand = (mod: Module) => {
    if (mod.badge === 'custom')
        return 'php artisan saucebase:recipe ModuleName';
    return `composer require saucebase/${mod.id}`;
};
</script>

<template>
    <Head>
        <title>{{ $t('Saucebase | The best modular Laravel SaaS Starter Kit') }}</title>
        <meta
            name="description"
            :content="
                $t(
                    'Free, open-source Laravel SaaS starter kit. Ships with auth, billing, admin panel, and a modular copy-and-own architecture.',
                )
            "
        />
    </Head>
    <div class="bg-background relative isolate flex min-h-screen flex-col border border-dashed">
        <Header />

        <main class="mx-auto w-full px-6 py-16 lg:px-8 ">
            <div class="py-16 ">
                <h1
                    class="text-primary text-center text-6xl font-bold tracking-tight [text-shadow:0_4px_25px_color-mix(in_oklch,var(--color-primary)_15%,var(--color-background))] sm:text-7xl"
                >
                    {{ $t("Let's get started") }}
                </h1>
                <h2
                    class="text-secondary mt-1 text-center text-3xl font-bold"
                >
                    {{ $t('Your foundation is ready') }}
                </h2>
                <p
                    class="text-muted-foreground mt-3 text-center text-xl leading-7"
                >
                    {{
                        $t(
                            'Focus on what makes your product unique, the modules handle the rest.',
                        )
                    }}
                </p>
            </div>
            <div
                class="relative -mt-25 p-25 font-mono "
                style="
                    mask-image:
                        linear-gradient(to bottom, #000 90%, transparent 100%),
                        linear-gradient(to right, #000 90%, transparent 100%),
                        linear-gradient(to top, #000 90%, transparent 100%),
                        linear-gradient(to left, #000 90%, transparent 100%);
                    mask-composite: intersect;
                "
            >
                <!-- Module cards — grid is transformed as one unit for correct alignment -->
                <div
                    class="relative z-10 mx-auto grid max-w-6xl rotate-[-5deg] skew-x-10 grid-cols-1 gap-8 gap-y-2 px-10 pb-16 has-[[data-card]:hover]:*:data-card:opacity-40 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="mod in modules"
                        :key="mod.id"
                        data-card
                        class="group/card relative flex cursor-pointer flex-col pt-6 transition-opacity duration-200 hover:opacity-100!"
                        :class="mod.badge === 'coming-soon' ? 'opacity-50' : ''"
                        @click="selectedMod = mod"
                    >
                        <div class="relative flex-1">
                            <!-- Diagonal stripe accent (behind card) -->
                            <div
                                class="absolute inset-x-2 top-3 bottom-0 w-full -translate-x-5 translate-y-2.5 rounded-xl transition-opacity duration-200"
                                :class="[
                                    stripeClasses,
                                    mod.badge !== 'coming-soon'
                                        ? 'opacity-90 group-hover/card:opacity-90'
                                        : 'opacity-80',
                                ]"
                                :style="modStyle(mod)"
                            />

                            <!-- Card body -->
                            <div
                                class="bg-card relative z-10 flex h-full flex-col gap-2 rounded-xl p-4 pt-12 text-left shadow-[-1px_1px_0_0_color-mix(in_oklch,var(--color-white)_80%,black)] transition-all duration-200 group-hover/card:translate-x-1.5 group-hover/card:-translate-y-1.5 group-hover/card:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]/70 dark:shadow-[-2px_2px_0_0_color-mix(in_oklch,var(--color-muted)_90%,black)] group-hover/card:dark:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--color-muted)_90%,black)]"
                                :class="
                                    mod.badge === 'coming-soon'
                                        ? 'border-dashed'
                                        : ''
                                "
                                   :style="modStyle(mod)"
                            >
                                <!-- Soon badge — mirrors icon two-layer structure -->
                                <div
                                    class="absolute top-1.5 -right-1.5 z-10 transition-all group-hover/card:translate-x-1 group-hover/card:-translate-y-0.5"
                                >
                                    <div
                                        v-if="mod.badge === 'coming-soon'"
                                        class="bg-muted text-foreground flex items-center justify-center rounded-full border px-2.5 py-0.5 text-[10px] font-medium shadow-sm"
                                    >
                                        {{ $t('Soon') }}
                                    </div>
                                    <div
                                        v-if="mod.badge === 'new'"
                                        class="flex items-center justify-center rounded-full border border-emerald-500 bg-emerald-100 px-2.5 py-0.5 text-[10px] font-medium text-emerald-600 shadow-sm"
                                    >
                                        {{ $t('New') }}
                                    </div>
                                </div>
                                <!-- Floating icon -->
                                <div
                                    class="absolute -top-4 left-1/2 z-10 -ml-4 flex size-14 shrink-0 items-center justify-center rounded-full shadow-[-2px_2px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 group-hover/card:translate-x-1.5 group-hover/card:-translate-y-1.5 group-hover/card:shadow-[-5px_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                                    :class="mod.bg"
                                    :style="modStyle(mod)"
                                >
                                    <component
                                        :is="mod.icon"
                                        class="absolute size-7 text-black/10 transition-all duration-200 group-hover/card:text-black/30 group-hover/card:blur-[1.5px]"
                                        aria-hidden="true"
                                    />
                                    <component
                                        :is="mod.icon"
                                        class="absolute size-7 translate-x-0.5 -translate-y-0.5 text-white transition-transform duration-200 group-hover/card:translate-x-1 group-hover/card:-translate-y-1.5"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div
                                    class="absolute top-0 left-1/2 -ml-7 flex size-14 shrink-0 items-center justify-center rounded-full"
                                    :class="stripeClasses"
                                    :style="modStyle(mod)"
                                />

                                <div
                                    v-if="
                                        mod.badge === 'coming-soon' ||
                                        mod.badge === 'new'
                                    "
                                    class="absolute top-3 right-0.5 rounded-full px-2.5 py-0.5 text-[10px] font-medium"
                                    :class="stripeClasses"
                                    :style="{
                                        '--mod-color':
                                            'var(--color-foreground)',
                                    }"
                                    aria-hidden="true"
                                >
                                    <span class="invisible">
                                        {{ $t('Soon') }}
                                    </span>
                                </div>
                                <span
                                    class="text-foreground mt-4 text-center text-base leading-tight font-semibold"
                                >
                                    {{ $t(mod.title) }}
                                </span>
                                <p
                                    class="text-muted-foreground line-clamp-2 text-center text-xs leading-snug"
                                >
                                    {{ $t(mod.description) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Light mode pattern -->
                <div
                    class="absolute inset-0 -z-1 -m-5 overflow-hidden dark:hidden rotate-[-5deg] skew-x-10"
                    style="
                        background-size: 24px;
                        background-position: top left;
                        background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.4%22 fill=%22%23011E32%22 fill-opacity=%22.24%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E');
                    "
                />
                <!-- Dark mode pattern -->
                <div
                    class="absolute inset-0 -z-1 -m-5 hidden overflow-hidden dark:block rotate-[-5deg] skew-x-10"
                    style="
                        background-size: 24px;
                        background-position: top left;
                        background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 32 32%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg opacity=%22.5%22 fill=%22%23ffffff%22 fill-opacity=%22.15%22%3E%3Cpath fill-rule=%22evenodd%22 clip-rule=%22evenodd%22 d=%22M0 .5V6h.5V.5H6V0H0v.5ZM.5 32H0v-6h.5v5.5H6v.5H.5ZM32 0v6h-.5V.5H26V0h6Zm0 31.5V26h-.5v5.5H26v.5h6v-.5Z%22/%3E%3Cpath opacity=%22.6%22 d=%22M19 0v.5h-6V0zM19 31.5v.5h-6v-.5zM32 19h-.5v-6h.5zM.5 19H0v-6h.5z%22/%3E%3C/g%3E%3C/svg%3E');
                    "
                />
                <div class="mt-8 flex justify-center">
                    <div class="relative inline-flex">
                        <!-- Stripe layer: same shape, shifted down-right -->
                        <div
                            class="absolute inset-0 translate-y-[12px] rounded-full"
                            :class="stripeClasses"
                            :style="modStyle({ bg: 'bg-foreground' })"
                        />
                        <a
                            href="https://saucebase-dev.github.io/docs/"
                            class="hover:bg-foreground/80 text-background bg-foreground/90 relative flex items-center gap-2 rounded-full px-8 py-4 text-base font-semibold shadow-[0_5px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_9px_0_0_color-mix(in_oklch,var(--color-foreground)_85%,black)]"
                        >
                            <BookOpen class="size-5" aria-hidden="true" />
                            {{ $t('Read the Documentation') }}
                        </a>
                    </div>
                </div>
            </div>
            <!-- CTA -->
        </main>

        <Footer />
    </div>

    <!-- Module detail modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="selectedMod"
                class="bg-background/50 fixed inset-0 z-50 backdrop-blur-sm"
                @click="selectedMod = null"
            />
        </Transition>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="opacity-0 translate-y-6 scale-[0.96]"
            leave-to-class="opacity-0 translate-y-4 scale-[0.97]"
        >
            <div
                v-if="selectedMod"
                class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-6 shadow-lg"
            >
                <div
                    class="pointer-events-auto relative w-full max-w-lg"
                    @click.stop
                >

                    <!-- Modal card body -->
                    <div
                        class="bg-card border-border relative z-10 flex flex-col gap-3 rounded-xl border p-6 shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_85%,black)] dark:shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_20%,black)]"
                        :class="
                            selectedMod.badge === 'coming-soon' ||
                            selectedMod.badge === 'custom'
                                ? 'border-dashed'
                                : ''
                        "
                    >
                        <!-- Close button -->
                        <button
                            class="text-muted-foreground hover:text-foreground hover:border-foreground absolute top-3 right-3 z-20 cursor-pointer rounded-full border p-1.5 transition-colors"
                            @click="selectedMod = null"
                        >
                            <X class="size-4" />
                        </button>

                        <!-- Header: icon + title left, close button right -->
                        <div class="flex items-center gap-3">
                            <!-- Icon -->
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-full"
                                :class="selectedMod.bg"
                            >
                                <component
                                    :is="selectedMod.icon"
                                    class="size-5 text-white"
                                    aria-hidden="true"
                                />
                            </div>
                            <h2
                                class="text-foreground flex-1 text-xl leading-tight font-bold"
                            >
                                {{ $t(selectedMod.title) }}
                            </h2>
                        </div>

                        <!-- Description -->
                        <p class="text-muted-foreground py-2 leading-relaxed">
                            {{ $t(selectedMod.description) }}
                        </p>

                        <!-- Features checklist -->
                        <ul
                            class="grid grid-cols-2 gap-x-4 gap-y-1.5 rounded-sm border p-4"
                        >
                            <li
                                v-for="feature in selectedMod.features"
                                :key="feature"
                                class="text-foreground flex items-center gap-2 text-sm"
                            >
                                <Check
                                    class="size-3.5 shrink-0"
                                    :style="{
                                        color: `var(--color-${selectedMod.bg.slice(3)})`,
                                    }"
                                    aria-hidden="true"
                                />
                                {{ $t(feature) }}
                            </li>
                        </ul>

                        <!-- Install command (free + custom only) -->
                        <div
                            v-if="selectedMod.badge !== 'coming-soon'"
                            class="flex flex-col gap-2 mt-2"
                        >
                            <div
                                class="flex items-center gap-3 rounded-sm bg-gray-950 px-4 py-3 dark:bg-gray-900"
                            >
                                <Terminal
                                    class="size-4 shrink-0 text-gray-500"
                                    aria-hidden="true"
                                />
                                <code class="flex-1 text-sm text-green-400">
                                    {{ installCommand(selectedMod) }}
                                </code>
                                <button
                                    class="cursor-pointer text-gray-300 transition-colors hover:text-gray-300"
                                    @click="copyCommand"
                                >
                                    <Check
                                        v-if="copied"
                                        class="size-4 text-green-400"
                                    />
                                    <Copy v-else class="size-4" />
                                </button>
                            </div>
                            <p
                                v-if="selectedMod.badge !== 'custom'"
                                class="text-muted-foreground text-center text-sm py-2"
                            >
                                {{ $t('This module may require additional steps after installation.') }}
                            </p>
                        </div>

                        <!-- CTA -->
                        <div class="-mx-6 border-t px-6 pt-4">
                            <a
                                v-if="selectedMod.badge !== 'coming-soon'"
                                :href="selectedMod.href ?? undefined"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex w-full items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white shadow-[0_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_7px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                                :class="selectedMod.bg"
                                :style="modStyle(selectedMod)"
                            >
                                <BookOpen class="size-4" aria-hidden="true" />
                                {{ $t('Read the Documentation') }}
                            </a>
                            <span
                                v-else
                                class="bg-muted/90 border border-muted-foreground/20 text-muted-foreground flex w-full items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold"
                            >
                                {{ $t('Coming Soon') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
