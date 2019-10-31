<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__);

$config = PhpCsFixer\Config::create()
    ->setUsingCache(false) // Disable creation of .php_cs.cache
    ->setRules([
        '@PSR2' => true,
        'linebreak_after_opening_tag' => true,
        'lowercase_constants' => false,
        'method_argument_space' => false,
        'single_quote' => true,
        'concat_space' => ['spacing' => 'one'],
        'list_syntax' => ['syntax' => 'short'],
        'no_extra_blank_lines' => ['extra'],
        'ordered_imports' => [
          'sort_algorithm' => 'length'
        ],
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline_array' => true,
        'single_blank_line_at_eof' => true,
        'single_blank_line_before_namespace' => true,
        'binary_operator_spaces' => [
            'align_equals' => true,
            'align_double_arrow' => true,
        ],
        'method_argument_space' => [
          'ensure_fully_multiline' => true
        ],
        'braces' => [
          'position_after_anonymous_constructs' => 'next',
        ]
    ])
    ->setFinder($finder);

return $config;
