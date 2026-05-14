import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroupLabel,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useT } from '@/i18n';
import { handleAction } from '@/lib/navigation';
import type { MenuBadge, MenuItem } from '@/types/navigation';
import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { Badge } from '../badge';
import { Separator } from '../separator';
import NavIcon from './NavIcon';

interface NavItemProps {
    item: MenuItem;
}

function getBadgeConfig(item: MenuItem): MenuBadge | null {
    if (!item.badge) return null;
    return item.badge === true ? { content: undefined } : (item.badge as MenuBadge);
}

function isItemActive(item: MenuItem): boolean {
    if (item.active !== undefined) return item.active;
    if (!item.route) return false;
    try {
        return route().current(item.route);
    } catch {
        return false;
    }
}

function NavBadge({ badge }: { badge: MenuBadge | null }) {
    if (!badge) return null;
    return (
        <Badge variant={badge.variant} className={`ml-auto ${badge.class ?? ''}`}>
            {badge.content ?? <span className="size-1.5 rounded-xl bg-current" />}
        </Badge>
    );
}

function NavLinkContent({ item }: { item: MenuItem }) {
    const badge = getBadgeConfig(item);
    return (
        <>
            <NavIcon icon={item.icon} />
            <span>{useT()(item.title)}</span>
            <NavBadge badge={badge} />
        </>
    );
}

export default function NavItem({ item }: NavItemProps) {
    const t = useT();

    if (item.type === 'separator') return <Separator />;

    if (item.type === 'label') {
        return <SidebarGroupLabel>{t(item.title)}</SidebarGroupLabel>;
    }

    if (item.children?.length) {
        const isActive = isItemActive(item);
        return (
            <Collapsible asChild defaultOpen={isActive} className="group/collapsible">
                <SidebarMenuItem>
                    <CollapsibleTrigger asChild>
                        <SidebarMenuButton tooltip={t(item.title)} className={item.class}>
                            <NavLinkContent item={item} />
                            <ChevronRight className="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            {item.children.map((child) => (
                                <SidebarMenuSubItem key={child.id ?? child.title}>
                                    <SidebarMenuSubButton
                                        asChild
                                        isActive={
                                            child.active !== undefined
                                                ? child.active
                                                : !!(child.route && route().current(child.route))
                                        }
                                    >
                                        {child.external ? (
                                            <a
                                                href={child.url ?? '#'}
                                                target={child.newPage ? '_blank' : undefined}
                                                rel={child.newPage ? 'noopener noreferrer' : undefined}
                                                className={child.class}
                                            >
                                                <NavLinkContent item={child} />
                                            </a>
                                        ) : (
                                            <Link
                                                href={child.url ?? '#'}
                                                target={child.newPage ? '_blank' : undefined}
                                                className={child.class}
                                            >
                                                <NavLinkContent item={child} />
                                            </Link>
                                        )}
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            ))}
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        );
    }

    if (item.action) {
        return (
            <SidebarMenuItem>
                <SidebarMenuButton
                    tooltip={t(item.title)}
                    className={item.class}
                    onClick={(e) => handleAction(item.action!, e as unknown as MouseEvent)}
                >
                    <NavLinkContent item={item} />
                </SidebarMenuButton>
            </SidebarMenuItem>
        );
    }

    const isActive = isItemActive(item);

    return (
        <SidebarMenuItem>
            <SidebarMenuButton asChild isActive={isActive} tooltip={t(item.title)}>
                {item.external ? (
                    <a
                        href={item.url ?? '#'}
                        target={item.newPage ? '_blank' : undefined}
                        rel={item.newPage ? 'noopener noreferrer' : undefined}
                        className={item.class}
                    >
                        <NavLinkContent item={item} />
                    </a>
                ) : (
                    <Link
                        href={item.url ?? '#'}
                        target={item.newPage ? '_blank' : undefined}
                        className={item.class}
                    >
                        <NavLinkContent item={item} />
                    </Link>
                )}
            </SidebarMenuButton>
        </SidebarMenuItem>
    );
}
