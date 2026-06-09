import AlertMessage from '@/components/AlertMessage';
import AppLogo from '@/components/AppLogo';
import Footer from '@/components/Footer';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

interface AuthCardLayoutProps {
    title?: string;
    description?: string;
    cardClass?: string;
    children: ReactNode;
    outside?: ReactNode;
}

export default function AuthCardLayout({
    title,
    description,
    cardClass,
    children,
    outside,
}: AuthCardLayoutProps) {
    const page = usePage();
    const status = page.props.status as string | undefined;
    const error = page.props.error as string | undefined;

    return (
        <div className="flex min-h-dvh flex-col items-center gap-6">
            <div className="mt-6">
                <Head title={title} />
                <Link href={route('index')} className="mt-6 font-medium">
                    <AppLogo size="md" showText={true} />
                </Link>
            </div>

            <div className="flex w-full grow flex-col items-center">
                <div className="w-full px-4 min-[450px]:w-auto min-[450px]:min-w-md min-[450px]:px-0">
                    <Card className={cardClass}>
                        <CardHeader className="px-8 text-center">
                            <CardTitle className="text-2xl">{title}</CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-8">
                            {status || error ? (
                                <div data-testid="alert">
                                    <AlertMessage
                                        message={status || error}
                                        variant={status ? 'success' : 'error'}
                                    />
                                </div>
                            ) : null}
                            {children}
                        </CardContent>
                    </Card>
                </div>
                {outside}
            </div>
            <Footer />
        </div>
    );
}
