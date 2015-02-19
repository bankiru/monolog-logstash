<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('*.xml')
    ->in(__DIR__)
;

if (file_exists(__DIR__ . '/.gitignore')) {
    foreach (file(__DIR__ . '/.gitignore') as $ignore) {
        $ignore = trim($ignore);
        if (is_dir(__DIR__ . '/' . trim($ignore, '/'))) {
            $finder->exclude(trim($ignore, '/'));
        } else {
            $finder->notName(trim($ignore, '/'));
        }
    }
}

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    ->fixers(\Symfony\CS\FixerInterface::PSR2_LEVEL)
;
