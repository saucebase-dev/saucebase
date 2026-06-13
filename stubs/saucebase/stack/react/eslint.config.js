import js from '@eslint/js';
import prettier from 'eslint-config-prettier/flat';
import reactHooks from 'eslint-plugin-react-hooks';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    js.configs.recommended,
    ...tseslint.configs.recommended,
    reactHooks.configs.flat['recommended-latest'],
    {
        rules: {
            '@typescript-eslint/no-explicit-any': 'off',
            // v7 rules designed for the React compiler — disable until the project adopts it
            'react-hooks/refs': 'off',
            'react-hooks/static-components': 'off',
            'react-hooks/set-state-in-effect': 'off',
            'react-hooks/purity': 'off',
        },
        languageOptions: {
            parserOptions: {
                tsconfigRootDir: import.meta.dirname,
            },
        },
    },
    {
        ignores: [
            'vendor',
            'node_modules',
            'public',
            'bootstrap/ssr',
            'stubs',
            'tailwind.config.js',
            'resources/js/components/ui/*',
            'modules/*/resources/js/components/ui/*',
            'stubs/saucebase/stack/*/resources/js/**/*',
        ],
    },
    prettier,
);
