import Footer from '@/components/Footer';
import Header from '@/components/Header';
import { Head, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

interface SiteLayoutProps {
    title?: string;
    description?: string;
    image?: string;
    canonical?: string;
    type?: 'website' | 'article';
    children?: ReactNode;
}

export default function SiteLayout({
    title,
    description,
    image,
    canonical,
    type = 'website',
    children,
}: SiteLayoutProps) {
    const page = usePage();
    const appName =
        ((page.props as Record<string, unknown>).appName as string) ??
        'Saucebase';

    return (
        <>
            <Head title={title}>
                {description && (
                    <meta name="description" content={description} />
                )}
                {canonical && <link rel="canonical" href={canonical} />}
                <meta property="og:type" content={type} />
                {title && <meta property="og:title" content={title} />}
                {description && (
                    <meta property="og:description" content={description} />
                )}
                {image && <meta property="og:image" content={image} />}
                {canonical && <meta property="og:url" content={canonical} />}
                <meta property="og:site_name" content={appName} />
                <meta
                    name="twitter:card"
                    content={image ? 'summary_large_image' : 'summary'}
                />
                {title && <meta name="twitter:title" content={title} />}
                {description && (
                    <meta name="twitter:description" content={description} />
                )}
                {image && <meta name="twitter:image" content={image} />}
            </Head>
            <div className="bg-background relative isolate flex min-h-screen flex-col">
                <Header />
                {children}
                <Footer />
            </div>
        </>
    );
}
