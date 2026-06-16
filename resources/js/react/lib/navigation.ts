import {
    BookOpen,
    CircleHelp,
    LayoutDashboard,
    ShieldCheck,
    SquareTerminal,
    type LucideIcon,
} from 'lucide-react';
import type { ComponentType } from 'react';
import IconDiscord from '~icons/simple-icons/discord';
import IconGithub from '~icons/simple-icons/github';

export type ActionHandler = (event: MouseEvent) => void | Promise<void>;

const actionRegistry: Record<string, ActionHandler> = {
    'ui.theme.toggle': (event: MouseEvent) => {
        event.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        document.documentElement.classList.toggle('dark', !isDark);
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    },
    'ui.language.select': (event: MouseEvent) => {
        event.preventDefault();
        console.warn('Language selection not yet implemented');
    },
};

export function handleAction(actionId: string, event: MouseEvent): void {
    const handler = actionRegistry[actionId];
    if (handler) {
        handler(event);
    } else {
        console.warn(`No handler registered for action: ${actionId}`);
    }
}

export function registerAction(actionId: string, handler: ActionHandler): void {
    if (actionRegistry[actionId]) {
        console.warn(`Action handler already registered for: ${actionId}`);
    }
    actionRegistry[actionId] = handler;
}

type IconComponent = ComponentType<{ className?: string }>;

const iconRegistry: Record<string, IconComponent> = {
    dashboard: SquareTerminal,
    github: IconGithub,
    discord: IconDiscord,
    admin: ShieldCheck,
    documentation: CircleHelp,
    docs: BookOpen,
    home: LayoutDashboard,
};

export function registerIcon(name: string, component: IconComponent): void {
    iconRegistry[name] = component;
}

export function resolveIcon(name: string): IconComponent | undefined {
    return iconRegistry[name];
}

export { type LucideIcon };
