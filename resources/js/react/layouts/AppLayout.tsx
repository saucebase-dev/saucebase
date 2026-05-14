import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { useSidebarState } from '@/hooks/useSidebarState';
import { useT } from '@/i18n';
import type { Breadcrumb as BreadcrumbType } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppSidebar from './AppSidebar';

interface AppLayoutProps {
    title?: string;
    breadcrumbs?: BreadcrumbType[];
    children: ReactNode;
}

export default function AppLayout({ title, breadcrumbs, children }: AppLayoutProps) {
    const t = useT();
    const { isOpen, setIsOpen } = useSidebarState();
    const page = usePage();

    const displayBreadcrumbs =
        breadcrumbs?.length ? breadcrumbs : (page.props.breadcrumbs as BreadcrumbType[] ?? []);

    return (
        <SidebarProvider open={isOpen} onOpenChange={setIsOpen}>
            <Head title={title} />
            <AppSidebar />
            <SidebarInset>
                <header className="flex h-14 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-14">
                    <div className="flex items-center gap-2 px-4">
                        <SidebarTrigger className="-ml-1" />
                        <Separator
                            orientation="vertical"
                            className="mr-2 data-[orientation=vertical]:h-4"
                        />
                        {(title || displayBreadcrumbs.length > 0) && (
                            <Breadcrumb>
                                <BreadcrumbList>
                                    {displayBreadcrumbs.length > 0
                                        ? displayBreadcrumbs.map((crumb, index) => (
                                              <>
                                                  <BreadcrumbItem key={index}>
                                                      {crumb.url ? (
                                                          <BreadcrumbLink href={crumb.url}>
                                                              {t(
                                                                  crumb.attributes?.label ??
                                                                      crumb.title,
                                                              )}
                                                          </BreadcrumbLink>
                                                      ) : (
                                                          <BreadcrumbPage>
                                                              {t(
                                                                  crumb.attributes?.label ??
                                                                      crumb.title,
                                                              )}
                                                          </BreadcrumbPage>
                                                      )}
                                                  </BreadcrumbItem>
                                                  {index < displayBreadcrumbs.length - 1 && (
                                                      <BreadcrumbSeparator key={`sep-${index}`} />
                                                  )}
                                              </>
                                          ))
                                        : title && (
                                              <BreadcrumbItem>
                                                  <BreadcrumbPage>{t(title)}</BreadcrumbPage>
                                              </BreadcrumbItem>
                                          )}
                                </BreadcrumbList>
                            </Breadcrumb>
                        )}
                    </div>
                </header>

                <main className="flex-1">{children}</main>
            </SidebarInset>
        </SidebarProvider>
    );
}
