<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$version = trim(file_get_contents($root . '/.version'));
$changelogPath = $root . '/CHANGELOG.md';

$remote = trim((string) shell_exec('git -C ' . escapeshellarg($root) . ' config --get remote.origin.url'));
$repository = 'https://github.com/superT0f/desarrimage';
$remote = preg_replace('/\.git$/', '', $remote);
if (preg_match('~github\.com[:/](.+)$~', $remote, $matches) === 1) {
    $repository = 'https://github.com/' . $matches[1];
}

$tagOutput = (string) shell_exec('git -C ' . escapeshellarg($root) . ' tag --sort=-version:refname');
$tags = array_values(array_filter(explode("\n", trim($tagOutput))));
$previousTag = $tags[0] ?? null;
if ($previousTag === 'v' . $version) {
    $previousTag = $tags[1] ?? null;
}
$range = $previousTag !== null ? escapeshellarg($previousTag) . '..HEAD' : 'HEAD';
$log = (string) shell_exec(
    'git -C ' . escapeshellarg($root) . ' log --no-merges --reverse --pretty=format:%H%x1f%h%x1f%s ' . $range
);

$sections = [
    'feat' => ['🚀 Ajouté', []],
    'fix' => ['🐛 Corrigé', []],
    'perf' => ['⚡ Performance', []],
    'refactor' => ['♻️ Refactorisation', []],
    'docs' => ['📚 Documentation', []],
    'test' => ['✅ Tests', []],
    'ci' => ['👷 Intégration continue', []],
    'build' => ['📦 Build', []],
    'style' => ['🎨 Style', []],
    'chore' => ['🧹 Maintenance', []],
    'revert' => ['⏪ Annulation', []],
    'other' => ['🧩 Autres', []],
];
$breaking = [];

foreach (array_filter(explode("\n", trim($log))) as $line) {
    [$hash, $shortHash, $subject] = array_pad(explode("\x1f", $line), 3, '');
    if (preg_match('/^docs(?:\([^)]+\))?: .*changelog/i', $subject) === 1) {
        continue;
    }
    $type = 'other';
    $scope = null;
    $description = $subject;
    $isBreaking = false;

    if (preg_match('/^(feat|fix|perf|refactor|docs|test|ci|build|style|chore|revert)(?:\(([^)]+)\))?(!)?:\s*(.+)$/', $subject, $matches) === 1) {
        $type = $matches[1];
        $scope = $matches[2] !== '' ? $matches[2] : null;
        $isBreaking = $matches[3] === '!';
        $description = $matches[4];
    }

    $label = ($scope !== null ? '**' . $scope . '** : ' : '') . $description;
    $item = '- ' . $label . ' ([' . $shortHash . '](' . $repository . '/commit/' . $hash . '))';
    $sections[$type][1][] = $item;
    if ($isBreaking) {
        $breaking[] = $item;
    }
}

$markdown = '## [' . $version . '](' . $repository . '/tree/v' . $version . ') — ' . date('Y-m-d') . "\n\n";
if ($breaking !== []) {
    $markdown .= "### 💥 Changements incompatibles\n\n" . implode("\n", $breaking) . "\n\n";
}
$hasChanges = false;
foreach ($sections as [$title, $items]) {
    if ($items === []) {
        continue;
    }
    $hasChanges = true;
    $markdown .= '### ' . $title . "\n\n" . implode("\n", $items) . "\n\n";
}
if (!$hasChanges) {
    $markdown .= "### 🌱 Initialisation\n\n- Première publication de Désarrimage.\n\n";
}

$header = <<<MARKDOWN
# Changelog

Toutes les modifications notables de Désarrimage sont consignées ici, avec un peu de lecture agréable et beaucoup de commits utiles. ⚓

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le projet utilise [Semantic Versioning](https://semver.org/lang/fr/). Ce fichier est généré avec `make changelog` à partir des [Conventional Commits](https://www.conventionalcommits.org/fr/v1.0.0/).

<!-- releases -->
MARKDOWN;

$older = '';
if (is_file($changelogPath)) {
    $existing = file_get_contents($changelogPath);
    $markerPosition = strpos($existing, '<!-- releases -->');
    if ($markerPosition !== false) {
        $previousContent = substr($existing, $markerPosition + strlen('<!-- releases -->'));
        $previousSections = preg_split('/(?=^## \[)/m', $previousContent, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($previousSections as $previousSection) {
            if (!str_starts_with($previousSection, '## [' . $version . ']')) {
                $older .= $previousSection;
            }
        }
    }
}

file_put_contents($changelogPath, $header . "\n\n" . $markdown . $older);
echo "CHANGELOG.md généré pour la version {$version}.\n";
