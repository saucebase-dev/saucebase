import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

export const resolveModularPageComponent = (name: string) => {
    if (name.includes('::')) {
        const [moduleName, componentPath] = name.split('::', 2);

        const moduleFolderName = moduleName.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        const moduleComponentPath = `/modules/${moduleFolderName}/resources/js/pages/${componentPath}.tsx`;

        const moduleGlobs = import.meta.glob('/modules/*/resources/js/**/*.tsx');

        return resolvePageComponent(moduleComponentPath, moduleGlobs);
    }

    return resolvePageComponent(
        `../pages/${name}.tsx`,
        import.meta.glob('../pages/**/*.tsx'),
    );
};
