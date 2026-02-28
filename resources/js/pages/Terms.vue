<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>{{ $t('Terms of Service') }}</title>
        <meta
            name="description"
            :content="$t('Terms and conditions for using our service.')"
        />
    </Head>
    <div class="relative isolate flex min-h-screen flex-col overflow-x-hidden">
        <Header />

        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-16">
            <h1 class="mb-2 text-4xl font-bold text-gray-900 dark:text-white">
                {{ $t('Terms of Service') }}
            </h1>
            <p class="mb-10 text-sm text-gray-500 dark:text-gray-400">
                {{ $t('Last updated: February 2026') }}
            </p>

            <section class="space-y-8 text-gray-700 dark:text-gray-300">
                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('1. Acceptance of Terms') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'By accessing or using our service, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('2. Use of Service') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'You agree to use the service only for lawful purposes and in accordance with these terms. You must not use the service to transmit harmful, offensive, or illegal content, or to violate the rights of others.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('3. Accounts') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'You are responsible for maintaining the confidentiality of your account credentials and for all activity that occurs under your account. You must notify us immediately of any unauthorized use of your account. We reserve the right to terminate accounts at our discretion.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('4. Intellectual Property') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'The service and its original content, features, and functionality are owned by us and are protected by applicable intellectual property laws. You may not copy, modify, or distribute our content without prior written consent.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('5. Payments and Billing') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'Paid plans are billed in advance on a recurring basis. You authorize us to charge your payment method for all fees associated with your plan. Refunds are issued at our sole discretion. You may cancel your subscription at any time, and access will continue until the end of the current billing period.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('6. Disclaimers') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'The service is provided "as is" without warranties of any kind, express or implied. We do not warrant that the service will be uninterrupted, error-free, or free of viruses. Your use of the service is at your own risk.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('7. Limitation of Liability') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'To the fullest extent permitted by law, we shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the service, even if we have been advised of the possibility of such damages.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('8. Changes to Terms') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'We reserve the right to modify these terms at any time. We will provide notice of significant changes by updating the date at the top of this page. Continued use of the service after changes constitutes acceptance of the new terms.',
                            )
                        }}
                    </p>
                </div>

                <div>
                    <h2
                        class="mb-3 text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        {{ $t('9. Contact Us') }}
                    </h2>
                    <p>
                        {{
                            $t(
                                'If you have any questions about these Terms of Service, please contact us at legal@saucebase.dev.',
                            )
                        }}
                    </p>
                </div>
            </section>
        </main>

        <Footer />
    </div>
</template>
