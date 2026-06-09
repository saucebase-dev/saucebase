import { Button } from '@/components/ui/button';
import { Field, FieldError } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useT } from '@/i18n';
import { Link, useForm, usePage } from '@inertiajs/react';
import AuthCardLayout from '../layouts/AuthCardLayout';

export default function ForgotPassword() {
    const t = useT();
    const page = usePage();
    const email = page.props.email ? String(page.props.email) : '';

    const { data, setData, post, processing, errors } = useForm({ email });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.email'), { preserveScroll: true });
    };

    return (
        <AuthCardLayout
            title={t('Forgot Password')}
            description={t(
                'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.',
            )}
        >
            <form
                onSubmit={handleSubmit}
                className="w-full space-y-3"
                data-testid="forgot-password-form"
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
                        data-testid="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    {errors.email && (
                        <FieldError data-testid="email-error">
                            {errors.email}
                        </FieldError>
                    )}
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
                        data-testid="reset-button"
                    >
                        {t('Email Password Reset Link')}
                    </Button>
                </div>
            </form>
        </AuthCardLayout>
    );
}
