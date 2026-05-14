import { Button } from '@/components/ui/button';
import { NavGroup } from '@/components/ui/navigation/NavGroup';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { useSidebar } from '@/components/ui/sidebar';
import { useT } from '@/i18n';
import type { Navigation } from '@/types/navigation';
import { usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';

export default function SettingsMobileMenu() {
    const t = useT();
    const { isMobile } = useSidebar();
    const page = usePage();
    const items = ((page.props.navigation as Navigation | undefined)?.settings) ?? [];

    if (!isMobile) return null;

    return (
        <Sheet>
            <SheetTrigger asChild>
                <Button variant="outline" size="icon" className="mr-3 align-middle lg:hidden" aria-label={t('Open settings menu')}>
                    <Menu className="size-5" />
                </Button>
            </SheetTrigger>
            <SheetContent side="left" className="w-64">
                <SheetHeader>
                    <SheetTitle>{t('Settings')}</SheetTitle>
                </SheetHeader>
                <div className="mt-2">
                    <NavGroup items={items} />
                </div>
            </SheetContent>
        </Sheet>
    );
}
