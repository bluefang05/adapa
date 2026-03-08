<?php

require_once __DIR__ . '/../config.php';

$resources = app_useful_resources_catalog();
$categoryLabels = app_useful_resource_category_labels();

echo "Useful Resources Audit\n";
echo "======================\n\n";

echo 'Total resources: ' . count($resources) . "\n\n";

$categoryCounts = [];
$languageCounts = [];
$sourceCounts = [];
$titleCounts = [];
$urlCounts = [];
$issues = [];

foreach ($resources as $resource) {
    $category = $resource['category'] ?? 'apoyo';
    $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;

    foreach (($resource['languages'] ?? []) as $language) {
        $languageCounts[$language] = ($languageCounts[$language] ?? 0) + 1;
    }

    $missing = [];
    foreach (['title', 'url', 'description', 'badge', 'best_for', 'cta_label'] as $field) {
        if (empty($resource[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        $issues[] = [
            'id' => $resource['id'] ?? '(sin id)',
            'problem' => 'Missing fields: ' . implode(', ', $missing),
        ];
    }

    if (!app_is_absolute_url($resource['url'] ?? '')) {
        $issues[] = [
            'id' => $resource['id'] ?? '(sin id)',
            'problem' => 'URL is not absolute',
        ];
    }

    $sourceLabel = app_url_host_label($resource['url'] ?? '');
    $sourceCounts[$sourceLabel] = ($sourceCounts[$sourceLabel] ?? 0) + 1;

    $titleKey = trim((string) ($resource['title'] ?? ''));
    if ($titleKey !== '') {
        $titleCounts[$titleKey] = ($titleCounts[$titleKey] ?? 0) + 1;
    }

    $urlKey = trim((string) ($resource['url'] ?? ''));
    if ($urlKey !== '') {
        $urlCounts[$urlKey] = ($urlCounts[$urlKey] ?? 0) + 1;
    }
}

echo "By category\n";
echo "-----------\n";
foreach ($categoryCounts as $category => $count) {
    $label = $categoryLabels[$category] ?? ucfirst($category);
    echo '- ' . $label . ': ' . $count . "\n";
}

echo "\nBy language\n";
echo "-----------\n";
foreach ($languageCounts as $language => $count) {
    echo '- ' . app_language_label($language, ucfirst($language)) . ': ' . $count . "\n";
}

echo "\nSource hosts\n";
echo "------------\n";
foreach ($sourceCounts as $sourceLabel => $count) {
    echo '- ' . $sourceLabel . ': ' . $count . "\n";
}

echo "\nDuplicates\n";
echo "----------\n";
$duplicateTitles = array_filter($titleCounts, static function ($count) {
    return $count > 1;
});
$duplicateUrls = array_filter($urlCounts, static function ($count) {
    return $count > 1;
});
if (empty($duplicateTitles) && empty($duplicateUrls)) {
    echo "No duplicates found.\n";
} else {
    foreach ($duplicateTitles as $title => $count) {
        echo '- title "' . $title . '": ' . $count . " veces\n";
    }
    foreach ($duplicateUrls as $url => $count) {
        echo '- url "' . $url . '": ' . $count . " veces\n";
    }
}

echo "\nIssues\n";
echo "------\n";
if (empty($issues)) {
    echo "No obvious issues found.\n";
} else {
    foreach ($issues as $issue) {
        echo '- ' . $issue['id'] . ': ' . $issue['problem'] . "\n";
    }
}
