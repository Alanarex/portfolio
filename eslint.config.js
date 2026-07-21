import eslint from '@eslint/js';
import globals from 'globals';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['public/build/**', 'resources/js/portfolio/routeTree.gen.ts'] },
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    {
        files: ['**/*.{js,ts,tsx}'],
        languageOptions: {
            ecmaVersion: 2022,
            globals: { ...globals.browser, ...globals.node },
        },
        plugins: {
            'react-hooks': reactHooks,
            'react-refresh': reactRefresh,
        },
        rules: {
            ...reactHooks.configs.flat.recommended.rules,
            ...reactRefresh.configs.vite.rules,
        },
    },
    {
        files: ['resources/js/portfolio/routes/**/*.tsx'],
        rules: {
            'react-refresh/only-export-components': 'off',
        },
    },
    {
        files: [
            'resources/js/portfolio/hooks/use-hydrated.ts',
            'resources/js/portfolio/hooks/use-preferences.tsx',
        ],
        rules: {
            'react-hooks/set-state-in-effect': 'off',
            'react-refresh/only-export-components': 'off',
        },
    },
);
