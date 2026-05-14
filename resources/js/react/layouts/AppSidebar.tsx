import NavGroup from '@/components/ui/navigation/NavGroup';
import NavUser from '@/components/ui/navigation/NavUser';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import type { User } from '@/types';
import type { Navigation } from '@/types/navigation';
import { usePage } from '@inertiajs/react';

export default function AppSidebar() {
    const page = usePage<{ navigation: Navigation; auth: { user: User } }>();

    const items = page.props.navigation?.main ?? [];
    const secondaryItems = page.props.navigation?.secondary ?? [];
    const userItems = page.props.navigation?.user ?? [];
    const user = page.props.auth?.user;

    return (
        <Sidebar variant="inset" collapsible="icon" className="bg-transparent">
            <SidebarHeader />
            <SidebarContent>
                <NavGroup items={items} />
                <NavGroup items={secondaryItems} className="mt-auto" />
            </SidebarContent>
            <SidebarFooter>
                {user && <NavUser user={user} items={userItems} />}
            </SidebarFooter>
        </Sidebar>
    );
}
