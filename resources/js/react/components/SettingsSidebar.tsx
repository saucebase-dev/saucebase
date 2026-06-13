import NavGroup from '@/components/ui/navigation/NavGroup';
import { Sidebar, SidebarContent, useSidebar } from '@/components/ui/sidebar';
import { useT } from '@/i18n';
import type { Navigation } from '@/types/navigation';
import { usePage } from '@inertiajs/react';
import { Settings } from 'lucide-react';

export default function SettingsSidebar() {
    const t = useT();
    const { isMobile } = useSidebar();
    const page = usePage();
    const items =
        (page.props.navigation as Navigation | undefined)?.settings ?? [];

    if (isMobile) return null;

    return (
        <Sidebar
            collapsible="none"
            variant="inset"
            data-sidebar="settings-sidebar"
        >
            <div className="flex items-center px-3 pt-4 pb-4 text-lg font-semibold">
                <Settings className="mr-2 inline-block size-5" />
                {t('Settings')}
            </div>
            <SidebarContent data-sidebar="content" className="mb-2">
                <NavGroup items={items} />
            </SidebarContent>
        </Sidebar>
    );
}
