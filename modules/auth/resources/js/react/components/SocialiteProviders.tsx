import { Button } from '@/components/ui/button';
import { useT } from '@/i18n';
import { usePage } from '@inertiajs/react';
import IconGithub from '~icons/simple-icons/github';
import IconGoogle from '~icons/simple-icons/google';

type Provider = {
    name: string;
    icon: React.ComponentType<{ className?: string }>;
};

const providers: Provider[] = [
    { name: 'google', icon: IconGoogle },
    { name: 'github', icon: IconGithub },
];

export default function SocialiteProviders() {
    const t = useT();
    const page = usePage();
    const lastUsed = (page.props.auth as any)?.last_social_provider as
        | string
        | undefined;

    if (!route().has('auth.socialite.redirect') || !providers.length) {
        return null;
    }

    return (
        <div className="mb-2 space-y-3">
            {providers.map(({ name, icon: Icon }) => (
                <div key={name} className="relative">
                    <Button variant="outline" className="w-full" asChild>
                        <a
                            href={route('auth.socialite.redirect', {
                                provider: name,
                            })}
                        >
                            <Icon className="h-5 w-5" />
                            <span>
                                {t('Connect with :Provider', {
                                    Provider: name,
                                })}
                            </span>
                        </a>
                    </Button>
                    {lastUsed === name && (
                        <span
                            data-testid={`last-used-badge-${name}`}
                            className="bg-muted/80 text-muted-foreground absolute -top-2 -right-2 rounded-xl border px-2 py-0.5 text-xs drop-shadow-lg"
                        >
                            {t('Last used')}
                        </span>
                    )}
                </div>
            ))}
            <div className="after:border-border relative text-center text-sm after:absolute after:inset-0 after:top-1/2 after:z-0 after:flex after:items-center after:border-t">
                <span className="bg-card text-muted-foreground relative z-10 px-2">
                    {t('Or continue with email')}
                </span>
            </div>
        </div>
    );
}
