<?php

// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/rules/index.rst
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        // '@PhpCsFixer'=> true,
        'array_indentation' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'method_argument_space' => [
            'on_multiline' => 'ignore',
            // 'on_multiline' => 'ensure_single_line',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'method_chaining_indentation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_to_comment' => false, // prevent /** formatted to just /*
        'single_line_throw' => false,
        'yoda_style' => [
            // enforce non yoda style
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setLineEnding("\n"); // standardize line endings to linux style.
