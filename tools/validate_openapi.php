<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

$specPath = __DIR__ . '/../openapi.yaml';

if (!file_exists($specPath)) {
    fwrite(STDERR, "openapi.yaml not found at: {$specPath}\n");
    exit(1);
}

try {
    $spec = Yaml::parseFile($specPath);
} catch (ParseException $e) {
    fwrite(STDERR, "YAML parse error: " . $e->getMessage() . "\n");
    exit(1);
}

$schemas = $spec['components']['schemas'] ?? [];
$responses = $spec['components']['responses'] ?? [];

$refs = [];
$walk = function (mixed $node) use (&$walk, &$refs): void {
    if (is_array($node)) {
        foreach ($node as $k => $v) {
            if ($k === '$ref' && is_string($v)) {
                $refs[] = $v;
            } else {
                $walk($v);
            }
        }
        return;
    }
};

$walk($spec);

$missing = [];
foreach (array_unique($refs) as $ref) {
    if (!str_starts_with($ref, '#/components/')) {
        continue;
    }

    if (preg_match('#^\#/components/schemas/([^/]+)$#', $ref, $m)) {
        $name = $m[1];
        if (!array_key_exists($name, $schemas)) {
            $missing[] = $ref;
        }
        continue;
    }

    if (preg_match('#^\#/components/responses/([^/]+)$#', $ref, $m)) {
        $name = $m[1];
        if (!array_key_exists($name, $responses)) {
            $missing[] = $ref;
        }
        continue;
    }
}

if ($missing) {
    fwrite(STDERR, "Missing refs:\n" . implode("\n", $missing) . "\n");
    exit(1);
}

echo "OK: YAML parsed and all internal \$ref targets exist. Found refs: " . count(array_unique($refs)) . "\n";
