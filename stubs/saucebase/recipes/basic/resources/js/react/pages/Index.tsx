import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useT } from '@/i18n';
import AppLayout from '@/layouts/AppLayout';
import {
    ArrowRight,
    BarChart2,
    CreditCard,
    Info,
    Rocket,
    Sparkles,
    TrendingDown,
    TrendingUp,
    Users,
} from 'lucide-react';
import { useState } from 'react';

const title = '{Module}';

type Stat = {
    label: string;
    value: string;
    icon: React.ElementType;
    change: string;
    changeLabel: string;
    positive: boolean;
};

type Feature = {
    title: string;
    description: string;
    icon: React.ElementType;
    status: string;
};

type Activity = {
    id: number;
    action: string;
    time: string;
    user: string;
};

const initialStats: Stat[] = [
    { label: 'Total Revenue', value: '$45,231.89', icon: CreditCard, change: '+20.1%', changeLabel: 'from last month', positive: true },
    { label: 'Subscriptions', value: '+2,350', icon: Users, change: '+180.1%', changeLabel: 'from last month', positive: true },
    { label: 'Sales', value: '+12,234', icon: BarChart2, change: '+19%', changeLabel: 'from last month', positive: true },
    { label: 'Active Now', value: '+573', icon: Sparkles, change: '+201', changeLabel: 'since last hour', positive: true },
];

const initialFeatures: Feature[] = [
    { title: 'Feature One', description: 'Powerful feature that helps you accomplish tasks efficiently', icon: Sparkles, status: 'active' },
    { title: 'Feature Two', description: 'Another amazing capability to enhance your workflow', icon: Rocket, status: 'beta' },
    { title: 'Feature Three', description: 'Coming soon to make your experience even better', icon: BarChart2, status: 'planned' },
];

const initialActivity: Activity[] = [
    { id: 1, action: 'Item created', time: '2 minutes ago', user: 'John Doe' },
    { id: 2, action: 'Settings updated', time: '1 hour ago', user: 'Jane Smith' },
    { id: 3, action: 'New user registered', time: '3 hours ago', user: 'Renan Roble' },
    { id: 4, action: 'Item updated', time: '30 seconds ago', user: 'Alice Johnson' },
];

export default function Index() {
    const t = useT();
    const [stats] = useState<Stat[]>(initialStats);
    const [features] = useState<Feature[]>(initialFeatures);
    const [recentActivity] = useState<Activity[]>(initialActivity);

    return (
        <AppLayout title={title} breadcrumbs={[{ title }]}>
            <div className="flex flex-1 flex-col gap-6 p-6 pt-2">
                {/* Header Section */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                        <p className="text-muted-foreground mt-1">{t('Manage and monitor your module activities')}</p>
                    </div>
                    <div className="flex gap-4">
                        <Button>
                            <Sparkles className="h-4 w-4" />
                            {t('New')}
                        </Button>
                    </div>
                </div>

                {/* Info Alert */}
                <Alert className="border-accent-foreground text-white">
                    <Info />
                    <AlertTitle className="text-lg">{t('Welcome to your new Module!')}</AlertTitle>
                    <AlertDescription>{t('This is a sample page with fake data. Customize it to fit your needs.')}</AlertDescription>
                </Alert>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                    {stats.map((stat) => {
                        const Icon = stat.icon;
                        const TrendIcon = stat.positive ? TrendingUp : TrendingDown;
                        return (
                            <Card key={stat.label}>
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-base font-medium">{t(stat.label)}</CardTitle>
                                    <Icon className="text-muted-foreground size-6" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-3xl font-bold">{stat.value}</div>
                                    <p className="text-muted-foreground flex items-center gap-1 text-xs">
                                        <TrendIcon className={`size-5 ${stat.positive ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500'}`} />
                                        <span className={stat.positive ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500'}>
                                            {stat.change}
                                        </span>
                                        {t(stat.changeLabel)}
                                    </p>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Call to Action Section */}
                <Card className="bg-primary text-primary-foreground border-primary relative overflow-hidden">
                    <Rocket className="pointer-events-none absolute -right-8 -bottom-12 size-72 text-white opacity-10" />
                    <CardHeader className="relative z-10">
                        <CardTitle className="text-xl">{t('Get Started')}</CardTitle>
                        <CardDescription className="text-primary-foreground/80">
                            {t('Ready to build something amazing? Start by customizing this template.')}
                        </CardDescription>
                    </CardHeader>
                    <CardFooter className="relative z-10 flex gap-2">
                        <a href="https://saucebase-dev.github.io/docs/" target="_blank" rel="noopener">
                            <Button variant="secondary">
                                <Rocket className="size-4" />
                                {t('View Documentation')}
                            </Button>
                        </a>
                    </CardFooter>
                </Card>

                {/* Main Content Grid */}
                <div className="grid gap-6 lg:grid-cols-7">
                    {/* Features Section */}
                    <div className="lg:col-span-4">
                        <Card className="h-full">
                            <CardHeader>
                                <CardTitle>{t('Features')}</CardTitle>
                                <CardDescription>{t('Explore the capabilities of this module')}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {features.map((feature) => {
                                    const Icon = feature.icon;
                                    return (
                                        <div key={feature.title} className="hover:bg-accent flex items-start gap-4 rounded-lg border p-4 transition-colors">
                                            <div className="bg-primary/10 text-primary rounded-xl p-4">
                                                <Icon className="size-8" />
                                            </div>
                                            <div className="flex-1 space-y-1">
                                                <div className="flex items-center gap-2">
                                                    <h3 className="font-semibold">{t(feature.title)}</h3>
                                                    <Badge variant={feature.status === 'active' ? 'default' : 'secondary'}>
                                                        {t(feature.status)}
                                                    </Badge>
                                                </div>
                                                <p className="text-muted-foreground text-sm">{t(feature.description)}</p>
                                            </div>
                                            <Button variant="ghost" size="icon">
                                                <ArrowRight className="h-4 w-4" />
                                                <span className="sr-only">{t('View')} {t(feature.title)}</span>
                                            </Button>
                                        </div>
                                    );
                                })}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Recent Activity Sidebar */}
                    <div className="lg:col-span-3">
                        <Card className="h-full">
                            <CardHeader>
                                <CardTitle>{t('Recent Activity')}</CardTitle>
                                <CardDescription>{t('Latest updates and changes')}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {recentActivity.map((activity, index) => (
                                        <div key={activity.id}>
                                            <div className="flex items-start gap-4">
                                                <div className="bg-primary/10 flex size-10 items-center justify-center rounded-xl">
                                                    <div className="bg-primary size-2 rounded-xl" />
                                                </div>
                                                <div className="flex-1 space-y-1">
                                                    <p className="text-sm font-medium">{t(activity.action)}</p>
                                                    <p className="text-muted-foreground text-xs">
                                                        {activity.user} • {activity.time}
                                                    </p>
                                                </div>
                                            </div>
                                            {index < recentActivity.length - 1 && <Separator className="my-4" />}
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                            <CardFooter>
                                <Button variant="outline" className="w-full">
                                    {t('View All Activity')}
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
