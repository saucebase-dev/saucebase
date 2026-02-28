<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>{{ $t('Privacy Policy') }}</title>
        <meta
            name="description"
            :content="$t('How we collect, use, and protect your data.')"
        />
    </Head>
    <div class="relative isolate flex min-h-screen flex-col overflow-x-hidden">
        <Header />

        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-16">
            <h1 class="mb-2 text-4xl font-bold text-gray-900 dark:text-white">
                {{ $t('Privacy Policy') }}
            </h1>
            <p class="mb-10 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Last updated: February 2026') }}
            </p>

            <section class="space-y-8 text-gray-700 dark:text-gray-300">
                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('1. Information We Collect') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We collect information you provide directly when you create an account, such as your name, email address, and payment information. We also automatically collect certain usage data when you interact with our service, including log files, IP addresses, browser type, and pages visited.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('2. How We Use Your Information') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We use the information we collect to provide, maintain, and improve our services, process transactions, send transactional and promotional communications, and comply with legal obligations. We do not sell your personal information to third parties.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('3. Cookies') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We use cookies and similar tracking technologies to maintain your session, remember your preferences, and understand how you use our service. You can control cookie settings through your browser, though disabling cookies may affect certain features.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('4. Third-Party Services') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We may share your information with trusted third-party service providers who assist us in operating our platform, such as payment processors and email delivery services. These providers are contractually obligated to keep your information confidential and use it only as directed by us.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('5. Data Retention') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We retain your personal information for as long as your account is active or as needed to provide services. You may request deletion of your account and associated data at any time by contacting us.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('6. Security') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We implement industry-standard security measures to protect your data, including encryption in transit and at rest. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('7. Contact Us') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'If you have any questions about this Privacy Policy or how we handle your data, please contact us at privacy@saucebase.dev.',
                            )
                        }}
                    </p>
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>
