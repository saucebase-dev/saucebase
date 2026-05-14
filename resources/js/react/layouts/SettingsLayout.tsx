import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLayout from './AppLayout';

interface SettingsLayoutProps {
    title: string;
    children: ReactNode;
}

export default function SettingsLayout({ title, children }: SettingsLayoutProps) {
    return (
        <>
            <Head title={title} />
            <AppLayout title={title}>
                <div className="flex h-full flex-1 flex-col p-4">
                    <div className="mx-auto w-full max-w-7xl">{children}</div>
                </div>
            </AppLayout>
        </>
    );
}
