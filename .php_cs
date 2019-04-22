<?php
$excluded_folders = [
    'node_modules',
    'storage',
    'vendor'
];
$finder = PhpCsFixer\Finder::create()
    ->exclude($excluded_folders)
    ->in(__DIR__);
return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2' => true,
        'lowercase_constants' => false,
        'method_argument_space' => false,
        'concat_space' => ['spacing' => "one"],
        'align_equals' => true
    ))
    ->setFinder($finder);

