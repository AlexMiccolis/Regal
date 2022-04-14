#!/usr/bin/env php
<?php
declare(strict_types = 1);
require($_composer_autoload_path ?? __DIR__ . "/../vendor/autoload.php");

function printHelp() {
    echo <<<TEXT
    Usage: regal [options] [documents]
    Valid Options:
        --templates=[directory] : Specify the directory that the site's
                                  templates are in. (REQUIRED)

        --output=[directory]    : Specify the output directory to save the
                                  compiled site into. (DEFAULT: 'dist/')

        Documents can be either template names or HTML files
    TEXT;
}

$rest = null;
$opts = getopt("", [ "templates:", "output::" ], $rest);

$templates = $opts["templates"] ?? null;
$output = $opts["output"] ?? "dist";
$documents = array_slice($argv, $rest);

if (!$templates || count($documents) == 0) {
    printHelp();
    exit(1);
}

if (!is_dir($templates)) {
    echo "Error: '$templates' is not a directory or doesn't exist." . PHP_EOL;
    exit(1);
}

$engine = new Regal\TemplateEngine($templates, $output, $documents);
foreach ($documents as $doc) {
    $engine->renderDocument($doc);
}

exit(0);
