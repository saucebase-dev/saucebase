import { Button } from '@/components/ui/button';
import { useT } from '@/i18n';
import { Link, useForm } from '@inertiajs/react';
import AuthCardLayout from '../layouts/AuthCardLayout';

export default function VerifyEmail() {
    const t = useT();
    const { post, processing } = useForm({});

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <AuthCardLayout
            title={t('Email Verification')}
            description={t(
                "Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.",
            )}
        >
            <form
                onSubmit={handleSubmit}
                className="min-w-sm space-y-3"
                data-testid="verify-email-form"
            >
                <Button type="submit" className="w-full" disabled={processing}>
                    {t('Resend Verification Email')}
                </Button>

                <p className="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="text-primary/70 cursor-pointer font-medium underline-offset-4 hover:underline"
                        data-testid="logout-link"
                    >
                        {t('Log Out')}
                    </Link>
                </p>
            </form>
        </AuthCardLayout>
    );
}
