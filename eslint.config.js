import eslint from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['public/build/**'] },
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    {
        files: ['**/*.{js,ts}'],
        languageOptions: {
            ecmaVersion: 2022,
            globals: { ...globals.browser, ...globals.node },
        },
    },
);
