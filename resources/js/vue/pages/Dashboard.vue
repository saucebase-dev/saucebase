<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Card from '@/components/ui/card/Card.vue';
import CardDescription from '@/components/ui/card/CardDescription.vue';
import CardFooter from '@/components/ui/card/CardFooter.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import IconRocket from '~icons/heroicons/rocket-launch';

const title = 'Welcome to Saucebase';
</script>

<template>
    <AppLayout :title="$t(title)" :breadcrumbs="[{ title: $t(title) }]">
        <div class="flex flex-1 flex-col gap-6 p-6 pt-2">
            <!-- Header Section -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ $t(title) }}
                    </h1>
                    <p class="text-muted-foreground mt-1">
                        {{
                            $t(
                                'Your foundation is ready to build something great!',
                            )
                        }}
                    </p>
                </div>
            </div>

            <!-- Call to Action Section -->
            <Card
                class="bg-primary text-primary-foreground border-primary relative overflow-hidden"
            >
                <!-- Background Rocket Icon -->
                <IconRocket
                    class="pointer-events-none absolute -right-8 -bottom-12 size-72 text-white opacity-10"
                />

                <CardHeader class="relative z-10">
                    <CardTitle class="text-xl">
                        {{ $t('Get Started') }}
                    </CardTitle>
                    <CardDescription class="text-primary-foreground/80">
                        {{ $t('Ready to build something amazing?') }}
                    </CardDescription>
                </CardHeader>
                <CardFooter class="relative z-10 flex gap-2">
                    <a
                        href="https://saucebase-dev.github.io/docs/"
                        target="_blank"
                        rel="noopener"
                    >
                        <Button variant="secondary">
                            <IconRocket class="size-4" />
                            {{ $t('View Documentation') }}
                        </Button>
                    </a>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
