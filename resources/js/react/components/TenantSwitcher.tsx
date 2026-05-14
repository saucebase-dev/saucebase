import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuShortcut,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { Link } from '@inertiajs/react';
import { ChevronsUpDown, Plus } from 'lucide-react';
import { useState } from 'react';
import AppLogo from './AppLogo';

const tenants = [
    { name: 'Saucebase', plan: 'SaaS' },
];

export default function TenantSwitcher() {
    const { isMobile } = useSidebar();
    const [activeTenant, setActiveTenant] = useState(tenants[0]);

    if (tenants.length > 1) {
        return (
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu modal={false}>
                        <DropdownMenuTrigger asChild>
                            <SidebarMenuButton size="lg" className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                                <div className="text-sidebar-primary-foreground flex size-8 items-center justify-center rounded-lg p-0">
                                    <AppLogo size="sm" />
                                </div>
                                <div className="grid flex-1 text-left text-sm leading-tight">
                                    <span className="truncate font-medium">{activeTenant.name}</span>
                                    <span className="truncate text-xs">{activeTenant.plan}</span>
                                </div>
                                <ChevronsUpDown className="ml-auto" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            className="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                            align="start"
                            side={isMobile ? 'bottom' : 'right'}
                            sideOffset={4}
                        >
                            <DropdownMenuLabel className="text-muted-foreground text-xs">Teams</DropdownMenuLabel>
                            {tenants.map((team, index) => (
                                <DropdownMenuItem key={team.name} className="gap-2 p-2" onClick={() => setActiveTenant(team)}>
                                    <div className="flex size-6 items-center justify-center rounded-sm border">
                                        <AppLogo size="sm" />
                                    </div>
                                    {team.name}
                                    <DropdownMenuShortcut>⇧⌘{index + 1}</DropdownMenuShortcut>
                                </DropdownMenuItem>
                            ))}
                            <DropdownMenuSeparator />
                            <DropdownMenuItem className="gap-2 p-2">
                                <div className="flex size-6 items-center justify-center rounded-md border bg-transparent">
                                    <Plus className="size-4" />
                                </div>
                                <div className="text-muted-foreground font-medium">Add tenant</div>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        );
    }

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    <Link href="/dashboard">
                        <div className="text-sidebar-primary-foreground flex size-8 items-center justify-center rounded-lg p-0">
                            <AppLogo size="sm" />
                        </div>
                        <div className="grid flex-1 text-left text-sm leading-tight">
                            <span className="truncate font-medium">{activeTenant.name}</span>
                            <span className="truncate text-xs opacity-40">{activeTenant.plan}</span>
                        </div>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
