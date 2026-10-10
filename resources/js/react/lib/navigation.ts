import { registerAction, registerIcon } from '@saucebase/react/lib/navigation';
import {
    BookOpen,
    CircleHelp,
    LayoutDashboard,
    ShieldCheck,
    SquareTerminal,
} from 'lucide-react';
import IconDiscord from '~icons/simple-icons/discord';
import IconGithub from '~icons/simple-icons/github';

/**
 * The app's own navigation actions and icons. Called once from the app entry, before
 * the modules register theirs.
 */
export function registerNavigationDefaults(): void {
    registerAction('ui.theme.toggle', (event: MouseEvent) => {
        event.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        document.documentElement.classList.toggle('dark', !isDark);
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
    });

    registerAction('ui.language.select', (event: MouseEvent) => {
        event.preventDefault();
        console.warn('Language selection not yet implemented');
    });

    registerIcon('dashboard', SquareTerminal);
    registerIcon('github', IconGithub);
    registerIcon('discord', IconDiscord);
    registerIcon('admin', ShieldCheck);
    registerIcon('documentation', CircleHelp);
    registerIcon('docs', BookOpen);
    registerIcon('home', LayoutDashboard);
}
