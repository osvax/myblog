<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'vendor',
        'storage',
        'public',
        'database/migrations',
        '.github',
        'app/Core/admin-functions.php',
        'app/Core/auth.php',
        'app/Core/custom-fields.php',
        'app/Core/slug.php',
        'app/Core/assets.php',
        'app/Core/calculator.php',
        'app/Core/menu.php',
        'app/Core/static-pages.php',
        'app/Views',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'declare_strict_types' => true,
    'strict_param' => true,
    'final_class' => true,
    'final_internal_class' => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_unused_imports' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'single_quote' => [
        'strings_containing_single_quote_chars' => false,
    ],
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
        'imports_order' => ['class', 'function', 'const'],
    ],
    'php_unit_method_casing' => [
        'case' => 'camel_case',
    ],
    'php_unit_test_class_requires_covers' => false,
    'php_unit_strict' => true,
])->setFinder($finder)->setRiskyAllowed(true);