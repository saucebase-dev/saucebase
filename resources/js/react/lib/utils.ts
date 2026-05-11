import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

declare const __SAUCEBASE_DEV__: boolean;
declare const __SAUCEBASE_FRAMEWORK__: string;

export const resolveModularPageComponent = (name: string) => {
    if (name.includes('::')) {
        const [moduleName, componentPath] = name.split('::', 2);

        const moduleFolderName = moduleName.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        const moduleGlobs = import.meta.glob('/modules/*/resources/js/**/pages/**/*.tsx');

        const moduleComponentPath = __SAUCEBASE_DEV__
            ? `/modules/${moduleFolderName}/resources/js/${__SAUCEBASE_FRAMEWORK__}/pages/${componentPath}.tsx`
            : `/modules/${moduleFolderName}/resources/js/pages/${componentPath}.tsx`;

        return resolvePageComponent(moduleComponentPath, moduleGlobs);
    }

    return resolvePageComponent(
        `../pages/${name}.tsx`,
        import.meta.glob('../pages/**/*.tsx'),
    );
};
