import { Button } from '@/components/ui/button';
import { Field, FieldError } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useT } from '@/i18n';
import { useForm } from '@inertiajs/react';
import AuthCardLayout from '../layouts/AuthCardLayout';

interface Props {
    email: string;
    token: string;
}

export default function ResetPassword({ email, token }: Props) {
    const t = useT();
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.store'), { preserveScroll: true });
    };

    return (
        <AuthCardLayout
            title={t('Reset Password')}
            description={t('Enter your new password below')}
        >
            <form
                onSubmit={handleSubmit}
                className="min-w-sm space-y-3"
                data-testid="reset-password-form"
            >
                <input
                    type="hidden"
                    name="token"
                    value={token}
                    data-testid="token"
                />

                <Field>
                    <Label htmlFor="email">{t('Email')}</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        data-testid="email"
                        value={data.email}
                        readOnly
                    />
                    {errors.email && (
                        <FieldError data-testid="email-error">
                            {errors.email}
                        </FieldError>
                    )}
                </Field>

                <Field>
                    <Label htmlFor="password">{t('Password')}</Label>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        placeholder={t('Enter your password')}
                        autoComplete="new-password"
                        required
                        data-testid="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    {errors.password && (
                        <FieldError data-testid="password-error">
                            {errors.password}
                        </FieldError>
                    )}
                </Field>

                <Field>
                    <Label htmlFor="password_confirmation">
                        {t('Confirm Password')}
                    </Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder={t('Confirm your new password')}
                        autoComplete="new-password"
                        required
                        data-testid="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                    />
                    {errors.password_confirmation && (
                        <FieldError data-testid="password_confirmation-error">
                            {errors.password_confirmation}
                        </FieldError>
                    )}
                </Field>

                <Button
                    type="submit"
                    className="mt-3 w-full"
                    disabled={processing}
                >
                    {t('Reset Password')}
                </Button>
            </form>
        </AuthCardLayout>
    );
}
