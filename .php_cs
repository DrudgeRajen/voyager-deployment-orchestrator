<?php

$finder = PhpCsFixer\Finder::create()->in(['src']);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'lowercase_constants' => false,
        'method_argument_space' => false,
        'concat_space' => ['spacing' => 'one'],
        'binary_operator_spaces' => [
            'align_equals' => true,
            'align_double_arrow' => true,
        ],
    ])
    ->setFinder($finder);
