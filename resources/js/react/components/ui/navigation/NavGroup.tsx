import { SidebarGroup, SidebarMenu } from '@/components/ui/sidebar';
import type { MenuItem } from '@/types/navigation';
import NavItem from './NavItem';

interface NavGroupProps {
    items: MenuItem[];
    className?: string;
}

export default function NavGroup({ items, className }: NavGroupProps) {
    return (
        <SidebarGroup className={className}>
            <SidebarMenu>
                {items.map((item) => (
                    <NavItem key={item.id ?? item.title} item={item} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
