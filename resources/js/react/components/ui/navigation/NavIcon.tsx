import { resolveIcon } from '@/lib/navigation';
import { cn } from '@/lib/utils';
import { HelpCircle } from 'lucide-react';

interface NavIconProps {
    icon?: string | null;
    className?: string;
}

export default function NavIcon({ icon, className }: NavIconProps) {
    if (!icon) return null;

    const Icon = resolveIcon(icon);
    if (!Icon) return <HelpCircle className={cn('size-4', className)} />;

    return <Icon className={cn('size-4', className)} />;
}
