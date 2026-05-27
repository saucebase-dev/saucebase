import { Button } from '@/components/ui/button';
import { useT } from '@/i18n';
import { Head, Link } from '@inertiajs/react';

const titles: Record<number, string> = {
    503: '503: Service Unavailable',
    500: '500: Server Error',
    404: '404: Page Not Found',
    403: '403: Forbidden',
};

const descriptions: Record<number, string> = {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
};

interface ErrorProps {
    status: number;
}

export default function Error({ status }: ErrorProps) {
    const t = useT();
    const title = t(titles[status] ?? 'Error');
    const description = t(descriptions[status] ?? 'An error occurred.');

    return (
        <>
            <Head title={`${status} - ${title}`} />
            <div className="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-900">
                <div className="w-full max-w-md space-y-8 text-center">
                    <div>
                        <h1 className="text-6xl font-bold text-gray-900 dark:text-gray-100">
                            {status}
                        </h1>
                        <h2 className="mt-4 text-2xl font-semibold text-gray-700 dark:text-gray-300">
                            {title}
                        </h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-400">
                            {description}
                        </p>
                    </div>
                    <div className="space-y-4">
                        <Button asChild>
                            <Link href={route('index')}>{t('Go to Home')}</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
