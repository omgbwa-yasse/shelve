module.exports = {
    env: {
        node: true,
        es2021: true,
        jest: true
    },
    extends: [
        'eslint:recommended'
    ],
    parserOptions: {
        ecmaVersion: 12,
        sourceType: 'module'
    },
    rules: {
        'no-console': 'warn',
        'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        'no-var': 'error',
        'prefer-const': 'error',
        'eqeqeq': 'error',
        'curly': 'error',
        'brace-style': ['error', '1tbs'],
        'comma-dangle': ['error', 'never'],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always'],
        'indent': ['error', 4],
        'no-trailing-spaces': 'error',
        'eol-last': 'error'
    },
    ignorePatterns: [
        'node_modules/',
        'logs/',
        'docs/generated/',
        '*.min.js'
    ]
};
