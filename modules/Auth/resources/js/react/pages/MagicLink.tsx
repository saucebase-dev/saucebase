import { Button } from '@/components/ui/button';
import { Field, FieldError } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useT } from '@/i18n';
import { Link, useForm } from '@inertiajs/react';
import AuthCardLayout from '../layouts/AuthCardLayout';

export default function MagicLink() {
    const t = useT();
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('magic-link.store'), { preserveScroll: true });
    };

    return (
        <AuthCardLayout
            title={t('Magic Link Login')}
            description={t(
                'Enter your email to receive a secure, one-time login link.',
            )}
        >
            <form
                onSubmit={handleSubmit}
                className="w-full space-y-3"
                data-testid="magic-link-form"
            >
                <Field>
                    <Label htmlFor="email">{t('Email')}</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        placeholder={t('Enter your email')}
                        autoComplete="email"
                        required
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        data-testid="magic-link-email"
                    />
                    {errors.email && <FieldError>{errors.email}</FieldError>}
                </Field>

                <div className="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={route('login')}
                        className="mt-4 text-center text-sm text-gray-600 hover:text-gray-900 sm:mt-0 sm:text-left dark:text-gray-400 dark:hover:text-gray-100"
                        data-testid="back-to-login-link"
                    >
                        {t('Back to login')}
                    </Link>
                    <Button
                        type="submit"
                        disabled={processing}
                        data-testid="magic-link-submit"
                    >
                        {t('Send Magic Link')}
                    </Button>
                </div>
            </form>
        </AuthCardLayout>
    );
}
