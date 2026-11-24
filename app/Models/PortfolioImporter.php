<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PortfolioImporter
{
    public function parse(string $markdown): array
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", ltrim($markdown, "\xEF\xBB\xBF"));
        preg_match('/^#\s+(.+)$/m', $markdown, $title);
        $sections = [];
        $current = '';
        $metadata = [];
        foreach (explode("\n", $markdown) as $line) {
            if (preg_match('/^\*\*(Стек технологий|Год создания|Срок выполнения):\*\*\s*(.*)$/ui', trim($line), $match)) {
                $metadata[mb_strtolower($match[1])] = trim($match[2]);
                continue;
            }
            if (preg_match('/^##\s+(.+)$/u', $line, $match)) {
                $current = trim(preg_replace('/[^\p{L}\p{N}\s\/()-]/u', '', mb_strtolower($match[1])));
                $sections[$current] = '';
            } elseif ($current !== '') {
                $sections[$current] .= $line . "\n";
            }
        }
        $description = $sections['о проекте'] ?? $sections['общая информация'] ?? '';
        $stack = $metadata['стек технологий'] ?? $sections['стек технологий'] ?? $sections['архитектура и стек'] ?? '';
        $yearText = $metadata['год создания'] ?? $sections['год создания'] ?? $sections['общая информация'] ?? '';
        preg_match('/\b(?:19|20)\d{2}\b/u', $yearText, $year);
        $cleanStack = $this->plainText($stack);
        return [
            'title' => trim(preg_replace('/\s+—\s+Описание проекта для портфолио$/ui', '', $title[1] ?? '')),
            'description' => $this->plainText($description),
            'task' => array_values(array_filter(explode("\n", $this->plainText($sections['задача'] ?? $sections['задачи проекта'] ?? '')))),
            'solution' => $this->plainText($sections['решение'] ?? $sections['архитектура и стек'] ?? ''),
            'result' => $this->plainText($sections['результат'] ?? ''),
            'technologies' => mb_substr(preg_replace('/\s+/u', ' ', $cleanStack), 0, 500),
            'stack' => mb_substr(preg_replace('/\s+/u', ' ', $cleanStack), 0, 500),
            'year' => isset($year[0]) ? (int) $year[0] : null,
            'duration' => mb_substr(rtrim($this->plainText($metadata['срок выполнения'] ?? $sections['срок выполнения'] ?? ''), '.'), 0, 120),
        ];
    }

    private function plainText(string $text): string
    {
        $text = preg_replace('/^```[^\n]*$|^\s*---+\s*$/m', '', $text);
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
        $text = preg_replace('/`([^`]*)`/', '$1', $text);
        $text = preg_replace('/^\s*[-*+]\s+|^\s*\d+[.)]\s+/m', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    public function importNew(): array
    {
        $db = Database::connection();
        $repository = new ContentRepository();
        $imported = [];
        foreach (glob(APP_ROOT . '/public/portfolio/*', GLOB_ONLYDIR) ?: [] as $directory) {
            $folder = basename($directory);
            if (is_link($directory) || !preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $folder)) {
                continue;
            }
            $slug = 'project-' . strtolower(str_replace('_', '-', $folder));
            if ($repository->projectBySlug($slug, false) !== null) {
                continue;
            }
            $descriptionFile = $directory . '/job/description.md';
            if (!is_file($descriptionFile) || is_link($directory . '/job') || is_link($descriptionFile)) {
                continue;
            }
            $markdown = file_get_contents($descriptionFile);
            if ($markdown === false) {
                throw new \RuntimeException('Не удалось прочитать описание: ' . $folder);
            }
            $data = $this->parse($markdown);
            if ($data['title'] === '' || $data['description'] === '') {
                throw new \RuntimeException('В описании отсутствует название или раздел «О проекте»: ' . $folder);
            }
            $screenshots = [];
            $uploadDirectory = APP_ROOT . '/public/uploads/images/portfolio';
            foreach (glob($directory . '/job/screenshots/*') ?: [] as $source) {
                if (!is_file($source) || is_link($source) || is_link(dirname($source))) {
                    continue;
                }
                $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
                $image = @getimagesize($source);
                $types = ['png' => IMAGETYPE_PNG, 'jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG];
                if (!isset($types[$extension]) || !$image || $image[2] !== $types[$extension]) {
                    continue;
                }
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
                    throw new \RuntimeException('Не удалось создать каталог изображений портфолио');
                }
                $name = $slug . '-' . hash_file('sha256', $source) . '.' . $extension;
                $target = $uploadDirectory . '/' . $name;
                if (!is_file($target) && !copy($source, $target)) {
                    throw new \RuntimeException('Не удалось скопировать скриншот: ' . $source);
                }
                $screenshots[] = 'uploads/images/portfolio/' . $name;
            }
            $infoItems = [];
            if (is_file($directory . '/index.html')) {
                $infoItems[] = ['kind' => 'link', 'name' => 'Демонстрация проекта', 'value' => '/portfolio/' . $folder . '/index.html'];
            }
            foreach (['pdf', 'html', 'docx', 'doc'] as $extension) {
                $specification = $directory . '/job/TZ-template.' . $extension;
                if (is_file($specification)) {
                    $infoItems[] = ['kind' => 'link', 'name' => 'Техническое задание', 'value' => '/portfolio/' . $folder . '/job/TZ-template.' . $extension];
                    break;
                }
            }
            $project = array_merge($data, [
                'id' => md5('portfolio-folder:' . $folder),
                'slug' => $slug,
                'icon' => 'bi-briefcase',
                'client' => '',
                'results' => [],
                'screenshots' => $screenshots,
                'gallery' => array_map(static fn ($path) => ['path' => $path, 'description' => ''], $screenshots),
                'infoItems' => $infoItems,
                'active' => true,
            ]);
            $db->beginTransaction();
            try {
                $repository->saveProject($project);
                $db->commit();
            } catch (\Throwable $error) {
                $db->rollBack();
                throw $error;
            }
            $imported[] = $slug;
        }
        return $imported;
    }
}
