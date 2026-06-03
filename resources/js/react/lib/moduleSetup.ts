export interface ModuleSetup {
    setup?: () => void | Promise<void>;
    afterMount?: () => void | Promise<void>;
}

export function discoverModuleSetups() {
    return import.meta.glob<ModuleSetup>('/modules/*/resources/js/app.ts', {
        eager: true,
    });
}

export async function executeModuleSetups(
    moduleSetups: Record<string, ModuleSetup>,
): Promise<void> {
    for (const module of Object.values(moduleSetups)) {
        if (module.setup) {
            await module.setup();
        }
    }
}

export async function executeAfterMountCallbacks(
    moduleSetups: Record<string, ModuleSetup>,
): Promise<void> {
    for (const module of Object.values(moduleSetups)) {
        if (module.afterMount) {
            await module.afterMount();
        }
    }
}
