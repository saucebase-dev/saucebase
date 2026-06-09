import { Button } from '@/components/ui/button';
import { Field, FieldError } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useT } from '@/i18n';
import { Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import SocialiteProviders from '../components/SocialiteProviders';
import AuthCardLayout from '../layouts/AuthCardLayout';

export default function Register() {
    const t = useT();
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('register'), { preserveScroll: true });
    };

    return (
        <AuthCardLayout
            title={t('Create your account')}
            description={t('Sign up for Saucebase to start building your SaaS')}
        >
            <SocialiteProviders />

            <form
                onSubmit={handleSubmit}
                className="space-y-3"
                data-testid="register-form"
            >
                <Field>
                    <Label htmlFor="name">{t('Name')}</Label>
                    <Input
                        id="name"
                        name="name"
                        type="text"
                        placeholder={t('Enter your full name')}
                        autoComplete="name"
                        data-testid="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {errors.name && (
                        <FieldError data-testid="name-error">
                            {errors.name}
                        </FieldError>
                    )}
                </Field>

                <Field>
                    <Label htmlFor="email">{t('Email')}</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        placeholder={t('Enter your email')}
                        autoComplete="email"
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

                <Field>
                    <Label htmlFor="password">{t('Password')}</Label>
                    <div className="relative">
                        <Input
                            id="password"
                            name="password"
                            type={showPassword ? 'text' : 'password'}
                            placeholder={t('Enter your password')}
                            autoComplete="new-password"
                            required
                            data-testid="password"
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                        />
                        <button
                            type="button"
                            data-testid="password-toggle"
                            aria-label={
                                showPassword
                                    ? t('Hide password')
                                    : t('Show password')
                            }
                            onClick={() => setShowPassword((v) => !v)}
                            className="text-muted-foreground hover:text-foreground absolute top-1/2 right-3 -translate-y-1/2"
                        >
                            {showPassword ? (
                                <EyeOff className="size-4" />
                            ) : (
                                <Eye className="size-4" />
                            )}
                        </button>
                    </div>
                    {errors.password && (
                        <FieldError data-testid="password-error">
                            {errors.password}
                        </FieldError>
                    )}
                </Field>

                <Button
                    type="submit"
                    className="mt-3 w-full"
                    disabled={processing}
                    data-testid="register-button"
                >
                    {t('Register')}
                </Button>

                <p className="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                    {t('Already registered?')}{' '}
                    <Link
                        href={route('login')}
                        className="text-primary/70 font-medium underline-offset-4 hover:underline"
                        data-testid="login-link"
                    >
                        {t('Log in')}
                    </Link>
                </p>
            </form>
        </AuthCardLayout>
    );
}
