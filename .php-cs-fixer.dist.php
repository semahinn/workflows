<?php

$finder = (new PhpCsFixer\Finder())
  ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config())
  ->setUsingCache(false)
//  ->setRiskyAllowed(true)
  ->setRules([
    '@PSR12' => true,
    '@Symfony' => true,
    'array_indentation' => true,
    'array_syntax' => ['syntax' => 'short'],
    'braces' => [
      'allow_single_line_closure' => true,
      'position_after_control_structures' => 'same',
      'position_after_functions_and_oop_constructs' => 'same',
    ],
    'class_definition' => [
      'multi_line_extends_each_single_line' => true,
    ],
    'comment_to_phpdoc' => false,
    'cast_spaces' => false,
    'concat_space' => ['spacing' => 'one'],
    'constant_case' => ['case' => 'upper'],
    'no_superfluous_phpdoc_tags' => false,
    'no_whitespace_in_blank_line' => false,
    'no_extra_blank_lines' => [
      'tokens' => [
        'break',
        'case',
        'continue',
        // 'curly_brace_block',
        'default',
        'extra',
        'parenthesis_brace_block',
        'return',
        'square_brace_block',
        'switch',
        'throw',
        'use',
        'useTrait',
        'use_trait'
      ]
    ],
    'no_blank_lines_after_class_opening' => false,
    'ordered_imports' => false,
    'phpdoc_align' => false,
    'phpdoc_summary' => false,
    'single_line_comment_style' => false,
    'single_line_throw' => false,
    'single_quote' => false,
    'trailing_comma_in_multiline' => false,
    'ternary_to_null_coalescing' => true,
    'yoda_style' => false,
  ])
  ->setIndent("  ")
  ->setFinder($finder);
