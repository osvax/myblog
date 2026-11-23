import js from '@eslint/js';

export default [
    {
        ignores: ['public/build/**', 'node_modules/**'],
    },
    js.configs.recommended,
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                localStorage: 'readonly',
                FormData: 'readonly',
                URLSearchParams: 'readonly',
                fetch: 'readonly',
                console: 'readonly',
                setTimeout: 'readonly',
                clearTimeout: 'readonly',
                Event: 'readonly',
                CustomEvent: 'readonly',
                HTMLElement: 'readonly',
                Node: 'readonly',
                Admin: 'readonly',
                CSS: 'readonly',
                Image: 'readonly',
                bootstrap: 'readonly',
                location: 'readonly',
                navigator: 'readonly',
                performance: 'readonly',
                requestAnimationFrame: 'readonly',
                IntersectionObserver: 'readonly',
                MutationObserver: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': 'off',
        },
    },
];
