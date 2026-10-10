import { setCookie } from '@/lib/utils';
import { registerAction, registerIcon } from '@saucebase/vue/lib/navigation';
import { useColorMode } from '@vueuse/core';
import IconHelpCircle from '~icons/lucide/help-circle';
import IconShieldCheck from '~icons/lucide/shield-check';
import IconSquareTerminal from '~icons/lucide/square-terminal';
import IconDiscord from '~icons/simple-icons/discord';
import IconGithub from '~icons/simple-icons/github';

/**
 * The app's own navigation actions and icons. Called once from the app entry, before
 * the modules register theirs.
 */
export function registerNavigationDefaults(): void {
    registerAction('ui.theme.toggle', (event: MouseEvent) => {
        event.preventDefault();

        const colorMode = useColorMode({ storageKey: 'appearance' });
        const next = colorMode.value === 'dark' ? 'light' : 'dark';
        colorMode.value = next;
        setCookie('appearance', next);
    });

    // TODO: Implement language selection logic
    registerAction('ui.language.select', (event: MouseEvent) => {
        event.preventDefault();
        console.warn('Language selection not yet implemented');
    });

    registerIcon('dashboard', IconSquareTerminal);
    registerIcon('github', IconGithub);
    registerIcon('discord', IconDiscord);
    registerIcon('admin', IconShieldCheck);
    registerIcon('documentation', IconHelpCircle);
}
