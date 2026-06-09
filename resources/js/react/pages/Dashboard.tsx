import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useT } from '@/i18n';
import AppLayout from '@/layouts/AppLayout';
import { Rocket } from 'lucide-react';

const title = 'Welcome to Saucebase';

export default function Dashboard() {
    const t = useT();

    return (
        <AppLayout title={t(title)} breadcrumbs={[{ title: t(title) }]}>
            <div className="flex flex-1 flex-col gap-6 p-6 pt-2">
                {/* Header Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {t(title)}
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            {t('Your foundation is ready to build something great!')}
                        </p>
                    </div>
                </div>

                {/* Call to Action Section */}
                <Card className="bg-primary text-primary-foreground border-primary relative overflow-hidden">
                    <Rocket className="pointer-events-none absolute -right-8 -bottom-12 size-72 text-white opacity-10" />
                    <CardHeader className="relative z-10">
                        <CardTitle className="text-xl">
                            {t('Get Started')}
                        </CardTitle>
                        <CardDescription className="text-primary-foreground/80">
                            {t('Ready to build something amazing?')}
                        </CardDescription>
                    </CardHeader>
                    <CardFooter className="relative z-10 flex gap-2">
                        <Button variant="secondary" asChild>
                            <a
                                href="https://saucebase-dev.github.io/docs/"
                                target="_blank"
                                rel="noopener"
                            >
                                <Rocket className="size-4" />
                                {t('View Documentation')}
                            </a>
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
