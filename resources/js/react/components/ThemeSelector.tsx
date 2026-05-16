import { ButtonGroup } from '@/components/ui/button-group';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useT } from '@/i18n';
import { type Theme, useTheme } from '@/hooks/useTheme';
import { Moon, Sun, MonitorCheck } from 'lucide-react';
import { useRef, type ReactNode } from 'react';

const themes = [
    { code: 'light' as Theme, name: 'Light', Icon: Sun },
    { code: 'dark' as Theme, name: 'Dark', Icon: Moon },
    { code: 'auto' as Theme, name: 'Device', Icon: MonitorCheck },
] as const;

interface ThemeSelectorProps {
    mode?: 'standalone' | 'submenu';
    triggerClass?: string;
    disableAnimation?: boolean;
    inline?: boolean;
    hideDevice?: boolean;
    fullWidth?: boolean;
    children?: ReactNode;
}

export default function ThemeSelector({
    mode = 'standalone',
    triggerClass = 'flex items-center rounded-lg p-2 text-muted-foreground transition-colors duration-200 hover:bg-accent hover:text-accent-foreground',
    disableAnimation = false,
    inline = false,
    hideDevice = false,
    fullWidth = false,
}: ThemeSelectorProps) {
    const t = useT();
    const { theme, setTheme } = useTheme();
    const triggerRef = useRef<HTMLButtonElement>(null);

    const visibleThemes = hideDevice ? themes.filter((th) => th.code !== 'auto') : [...themes];
    const currentTheme = themes.find((th) => th.code === theme) ?? themes[0];
    const CurrentIcon = currentTheme.Icon;

    function switchTheme(code: Theme, el?: HTMLElement) {
        setTheme(code, disableAnimation ? undefined : el ?? triggerRef.current ?? undefined);
    }

    if (inline) {
        return (
            <ButtonGroup className={fullWidth ? 'w-full' : ''}>
                {visibleThemes.map(({ code, name, Icon }) => (
                    <Button
                        key={code}
                        variant={theme === code ? 'default' : 'outline'}
                        size="sm"
                        className={`${fullWidth ? 'flex-1' : ''} ${theme === code ? 'font-semibold' : ''}`}
                        data-testid={`color-mode-${code}`}
                        aria-label={t(name)}
                        onClick={(e) => switchTheme(code, e.currentTarget)}
                    >
                        <Icon className="size-4" />
                        {t(name)}
                    </Button>
                ))}
            </ButtonGroup>
        );
    }

    if (mode === 'standalone') {
        return (
            <DropdownMenu modal={false}>
                <DropdownMenuTrigger asChild>
                    <button ref={triggerRef} className={triggerClass} aria-label={t('Toggle theme')}>
                        <CurrentIcon className="size-5" />
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="min-w-40">
                    {visibleThemes.map(({ code, name, Icon }) => (
                        <DropdownMenuItem
                            key={code}
                            onClick={(e) => switchTheme(code, e.currentTarget as HTMLElement)}
                            className={theme === code ? 'bg-accent text-accent-foreground' : ''}
                        >
                            <Icon className="size-4" />
                            {t(name)}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }

    return (
        <DropdownMenuSub>
            <DropdownMenuSubTrigger data-testid="theme-selector-trigger" className="[&>svg]:text-muted-foreground [&>svg]:mr-2">
                <CurrentIcon className="size-4" />
                {t('Theme')}
            </DropdownMenuSubTrigger>
            <DropdownMenuSubContent>
                {visibleThemes.map(({ code, name, Icon }) => (
                    <DropdownMenuItem
                        key={code}
                        onClick={(e) => switchTheme(code, e.currentTarget as HTMLElement)}
                        className={theme === code ? 'bg-accent' : ''}
                    >
                        <Icon className="size-4" />
                        {t(name)}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuSubContent>
        </DropdownMenuSub>
    );
}
