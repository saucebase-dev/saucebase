export interface Navigation {
    main: MenuItem[];
    secondary: MenuItem[];
    settings: MenuItem[];
    user: MenuItem[];
    landing: MenuItem[];
}

export interface MenuBadge {
    content?: string | number;
    variant?: 'default' | 'secondary' | 'destructive' | 'outline';
    class?: string;
}

export interface MenuItem {
    id?: string;
    title: string;
    route?: string;
    url?: string;
    slug?: string;
    icon?: string | null;
    permission?: string;
    action?: string;
    type?: 'label' | 'separator';
    active?: boolean;
    external?: boolean;
    newPage?: boolean;
    badge?: MenuBadge | boolean;
    children?: MenuItem[];
    class?: string;
}
