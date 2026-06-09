import { confirm } from '@/hooks/useDialog';
import { registerGlobalComponent } from '@/lib/globalComponents';
import { registerAction, registerIcon } from '@/lib/navigation';
import { router } from '@inertiajs/react';
import '@modules/auth/resources/css/style.css';
import { LogOut } from 'lucide-react';
import IconLogOut from '~icons/lucide/log-out';
import ImpersonationAlert from './components/ImpersonationAlert';

export function setup() {
    registerIcon('logout', IconLogOut);
    registerAuthActions();
    registerGlobalComponent('top', ImpersonationAlert);
}

function registerAuthActions() {
    registerAction('logout', async (event: MouseEvent) => {
        event.preventDefault();

        const confirmed = await confirm({
            title: 'Log out',
            description:
                'Are you sure you want to log out? You will need to sign in again.',
            confirmLabel: 'Log out',
            cancelLabel: 'Cancel',
            variant: 'destructive',
            icon: LogOut,
            align: 'left',
        });

        if (confirmed) {
            router.post(route('logout'));
        }
    });
}

export function afterMount() {
    console.debug('Auth module after mount logic executed');
}
