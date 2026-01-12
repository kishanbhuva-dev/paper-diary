import js from '@eslint/js';
import vue from 'eslint-plugin-vue';
import prettier from 'eslint-config-prettier';
import globals from 'globals';

/**
 * STRICT ESLint Configuration for eslint-plugin-vue v10+
 * All rules are set to 'error' to block commits with any violations
 */
export default [
  // Global ignores
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'public/**',
      'storage/**',
      'bootstrap/cache/**',
      'dist/**',
      '*.min.js',
      '_ide_helper.php',
    ],
  },

  // Base JS config
  js.configs.recommended,

  // Vue.js config - strongly recommended (stricter)
  ...vue.configs['flat/strongly-recommended'],

  // Prettier config (must be last)
  prettier,

  // Custom rules for all JS/Vue files
  {
    files: ['**/*.{js,mjs,cjs,vue}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        ...globals.es2021,
        route: 'readonly',
        axios: 'readonly',
      },
    },
    rules: {
      // ═══════════════════════════════════════════════════════════════
      // VUE RULES (eslint-plugin-vue v10+)
      // ═══════════════════════════════════════════════════════════════
      'vue/multi-word-component-names': 'off',
      'vue/no-v-html': 'error',
      'vue/require-default-prop': 'error',
      'vue/require-prop-types': 'error',
      'vue/block-order': [
        'error',
        {
          order: ['template', 'script', 'style'],
        },
      ],
      'vue/define-macros-order': [
        'error',
        {
          order: ['defineProps', 'defineEmits'],
        },
      ],
      'vue/no-unused-refs': 'error',
      'vue/no-useless-v-bind': 'error',
      'vue/padding-line-between-blocks': 'error',
      'vue/html-self-closing': [
        'error',
        {
          html: {
            void: 'always',
            normal: 'never',
            component: 'always',
          },
        },
      ],
      'vue/attributes-order': 'error',
      'vue/no-lone-template': 'error',
      'vue/no-multiple-slot-args': 'error',
      'vue/no-template-shadow': 'error',
      'vue/one-component-per-file': 'error',
      'vue/prop-name-casing': 'error',
      'vue/require-explicit-emits': 'error',
      'vue/v-on-event-hyphenation': 'error',
      'vue/component-name-in-template-casing': ['error', 'PascalCase'],
      'vue/custom-event-name-casing': ['error', 'camelCase'],
      'vue/no-empty-component-block': 'error',
      'vue/prefer-true-attribute-shorthand': 'error',

      // ═══════════════════════════════════════════════════════════════
      // JAVASCRIPT RULES - STRICT
      // ═══════════════════════════════════════════════════════════════
      'no-console': 'error',
      'no-debugger': 'error',
      'no-alert': 'error',
      'no-unused-vars': [
        'error',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
        },
      ],
      'prefer-const': 'error',
      'no-var': 'error',
      eqeqeq: ['error', 'always', { null: 'ignore' }],
      curly: ['error', 'all'],
      'no-nested-ternary': 'error',
      'no-unneeded-ternary': 'error',
      'prefer-template': 'error',
      'object-shorthand': 'error',
      'prefer-arrow-callback': 'error',
      'arrow-body-style': ['error', 'as-needed'],
      'no-duplicate-imports': 'error',
      'no-else-return': 'error',
      'no-lonely-if': 'error',
      'no-useless-return': 'error',
      'no-useless-concat': 'error',
      'no-useless-computed-key': 'error',
      'no-throw-literal': 'error',
      'prefer-destructuring': [
        'error',
        {
          array: false,
          object: true,
        },
      ],
      'prefer-rest-params': 'error',
      'prefer-spread': 'error',
      'require-await': 'error',
      'no-return-await': 'error',
      'no-await-in-loop': 'error',
      'no-promise-executor-return': 'error',
    },
  },

  // Test files - slightly relaxed
  {
    files: ['**/*.test.js', '**/*.spec.js', 'tests/**/*.js'],
    languageOptions: {
      globals: {
        describe: 'readonly',
        it: 'readonly',
        expect: 'readonly',
        beforeEach: 'readonly',
        afterEach: 'readonly',
        vi: 'readonly',
        test: 'readonly',
      },
    },
    rules: {
      'no-console': 'off',
    },
  },
];
