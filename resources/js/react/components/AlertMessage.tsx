import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    AlertCircle,
    AlertTriangle,
    CheckCircle,
    Info,
    type LucideIcon,
} from 'lucide-react';

type Variant = 'success' | 'info' | 'warning' | 'error' | 'default';
type Appearance = 'filled' | 'bordered' | 'outlined';

interface AlertMessageProps {
    message: string | unknown;
    variant?: Variant;
    appearance?: Appearance;
    icon?: LucideIcon;
    hideIcon?: boolean;
}

const defaultIcons: Record<Variant, LucideIcon> = {
    success: CheckCircle,
    info: Info,
    warning: AlertTriangle,
    error: AlertCircle,
    default: Info,
};

const variantStyles: Record<Variant, Record<Appearance, string>> = {
    success: {
        filled: 'border-green-700 bg-green-700 text-white [&>svg]:text-white *:data-[slot=alert-description]:text-white',
        bordered:
            'bg-green-50 text-green-800 border-green-200 dark:bg-green-950 dark:text-green-200 [&>svg]:text-green-800 dark:[&>svg]:text-green-200',
        outlined:
            'bg-transparent border-2 border-green-700 text-green-600 dark:text-green-400 [&>svg]:text-green-600 dark:[&>svg]:text-green-400',
    },
    info: {
        filled: 'border-blue-500 bg-blue-500 text-white [&>svg]:text-white *:data-[slot=alert-description]:text-white',
        bordered:
            'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950 dark:text-blue-200 [&>svg]:text-blue-800 dark:[&>svg]:text-blue-200',
        outlined:
            'bg-transparent border-2 border-blue-500 text-blue-600 dark:text-blue-400 [&>svg]:text-blue-600 dark:[&>svg]:text-blue-400',
    },
    warning: {
        filled: 'border-yellow-500 bg-yellow-500 text-black [&>svg]:text-black *:data-[slot=alert-description]:text-black',
        bordered:
            'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-950 dark:text-yellow-200 [&>svg]:text-yellow-800 dark:[&>svg]:text-yellow-200',
        outlined:
            'bg-transparent border-2 border-yellow-500 text-yellow-600 dark:text-yellow-400 [&>svg]:text-yellow-600 dark:[&>svg]:text-yellow-400',
    },
    error: {
        filled: 'border-red-500 bg-red-500 text-white [&>svg]:text-white *:data-[slot=alert-description]:text-white',
        bordered:
            'bg-red-50 text-red-800 border-red-200 dark:bg-red-950 dark:text-red-200 [&>svg]:text-red-800 dark:[&>svg]:text-red-200',
        outlined:
            'bg-transparent border-2 border-red-500 text-red-600 dark:text-red-400 [&>svg]:text-red-600 dark:[&>svg]:text-red-400',
    },
    default: {
        filled: '',
        bordered: '',
        outlined: 'bg-transparent border-2',
    },
};

export default function AlertMessage({
    message,
    variant = 'error',
    appearance = 'filled',
    icon: IconProp,
    hideIcon = false,
}: AlertMessageProps) {
    if (!message) return null;

    const baseVariant = variant === 'error' ? 'destructive' : 'default';
    const customClass = variantStyles[variant][appearance];
    const Icon = hideIcon ? null : (IconProp ?? defaultIcons[variant]);

    return (
        <Alert variant={baseVariant} className={`mb-4 ${customClass}`}>
            {Icon && <Icon className="h-4 w-4" />}
            <AlertDescription>{message as string}</AlertDescription>
        </Alert>
    );
}
