import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useT } from '@/i18n';
import { cn } from '@/lib/utils';
import type { User } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { Drama, History, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

interface Impersonation {
    user: User;
    route: string;
    label: string;
    recent: User[];
}

function getUserInitials(name: string): string {
    return name
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
}

function getRoleBadgeClasses(role: string): string {
    return cn(
        'shrink-0 rounded-xl px-1 py-0.5 text-[9px] text-white uppercase',
        role === 'admin' ? 'bg-red-800' : 'bg-cyan-700',
    );
}

export default function ImpersonationAlert() {
    const t = useT();
    const page = usePage();
    const impersonation = (page.props?.impersonation as Impersonation) || null;
    const [isExpanded, setIsExpanded] = useState(false);
    const alertRef = useRef<HTMLDivElement>(null);

    const collapse = useCallback(() => setIsExpanded(false), []);

    useEffect(() => {
        if (!isExpanded) return;

        function handleClickOutside(e: MouseEvent) {
            if (
                alertRef.current &&
                !alertRef.current.contains(e.target as Node)
            ) {
                collapse();
            }
        }

        function handleKeyDown(e: KeyboardEvent) {
            if (e.key === 'Escape') collapse();
        }

        document.addEventListener('mousedown', handleClickOutside);
        document.addEventListener('keydown', handleKeyDown);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [isExpanded, collapse]);

    const reimpersonate = (userId: number) => {
        router.post(
            route('auth.impersonate.reimpersonate', { userId }),
            {},
            { preserveScroll: true, onSuccess: collapse },
        );
    };

    if (!impersonation) return null;

    return (
        <div
            ref={alertRef}
            className="fixed right-3 bottom-3 z-50"
            data-testid="impersonation-alert"
        >
            {!isExpanded ? (
                <button
                    onClick={() => setIsExpanded(true)}
                    title={`Impersonating ${impersonation.user.name}`}
                    aria-label={t('Show impersonation details')}
                    aria-expanded={false}
                    className="animate-in fade-in zoom-in-95 relative cursor-pointer rounded-xl shadow-lg ring-2 ring-orange-500 transition-all duration-300 hover:shadow-xl hover:ring-orange-600"
                >
                    <Avatar className="size-10 bg-gray-100">
                        <AvatarImage
                            src={impersonation.user.avatar}
                            alt={impersonation.user.name}
                        />
                        <AvatarFallback className="bg-yellow-600 text-sm text-white">
                            {getUserInitials(impersonation.user.name)}
                        </AvatarFallback>
                    </Avatar>
                    <div className="absolute -top-3 -left-3 flex size-7 items-center justify-center rounded-xl bg-orange-500 shadow-lg">
                        <Drama className="size-5 text-white" />
                    </div>
                </button>
            ) : (
                <div
                    role="region"
                    aria-label={t('Impersonation alert')}
                    className="bg-foreground animate-in fade-in slide-in-from-right-5 relative flex w-80 flex-col gap-3 rounded-xl p-3 shadow-2xl duration-300"
                >
                    <button
                        onClick={collapse}
                        aria-label={t('Close')}
                        className="text-background/60 hover:bg-background/10 hover:text-background absolute top-3 right-3 rounded-xl p-1 transition-colors"
                    >
                        <X className="size-5" />
                    </button>
                    <div className="flex items-center gap-3">
                        <Avatar className="size-11 border-2 border-amber-500">
                            <AvatarImage
                                src={impersonation.user.avatar}
                                alt={impersonation.user.name}
                            />
                            <AvatarFallback className="bg-yellow-600 text-sm text-white">
                                {getUserInitials(impersonation.user.name)}
                            </AvatarFallback>
                        </Avatar>
                        <div className="min-w-0 flex-1">
                            <div className="flex items-center gap-2">
                                <p className="text-background truncate text-sm font-semibold">
                                    {impersonation.user.name}
                                </p>
                                {impersonation.user.role && (
                                    <span
                                        className={getRoleBadgeClasses(
                                            impersonation.user.role,
                                        )}
                                    >
                                        {impersonation.user.role}
                                    </span>
                                )}
                            </div>
                            <p className="text-background/80 truncate text-xs">
                                {impersonation.user.email}
                            </p>
                        </div>
                    </div>
                    <a
                        href={impersonation.route}
                        className="bg-background text-foreground hover:bg-background/90 w-full rounded-xl px-3 py-2 text-center text-sm font-medium transition-colors"
                    >
                        {impersonation.label}
                    </a>

                    {impersonation.recent?.length > 0 && (
                        <div className="border-background/10 mt-1 border-t pt-2">
                            <p className="text-background/50 mb-3 text-center text-sm font-medium tracking-wide">
                                <History className="inline-block size-4" />
                                {t('Recent impersonated users')}
                            </p>
                            <div className="space-y-0">
                                {impersonation.recent.map((user) => (
                                    <button
                                        key={user.id}
                                        onClick={() => reimpersonate(user.id)}
                                        className="hover:bg-background/20 flex w-full items-center rounded-xl text-left transition-colors"
                                    >
                                        <Avatar className="border-background/20 m-1 size-10 border-2">
                                            <AvatarImage
                                                src={user.avatar}
                                                alt={user.name}
                                            />
                                            <AvatarFallback className="bg-yellow-600/80 text-xs text-white">
                                                {getUserInitials(user.name)}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div className="min-w-0 flex-1 p-2 pl-1">
                                            <div className="flex items-center gap-1.5">
                                                <p className="text-background truncate text-xs font-medium">
                                                    {user.name}
                                                </p>
                                                {user.role && (
                                                    <span
                                                        className={getRoleBadgeClasses(
                                                            user.role,
                                                        )}
                                                    >
                                                        {user.role}
                                                    </span>
                                                )}
                                            </div>
                                            <p className="text-background/70 truncate text-xs">
                                                {user.email}
                                            </p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
