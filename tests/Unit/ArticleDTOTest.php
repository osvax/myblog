<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\ArticleDTO;
use PHPUnit\Framework\TestCase;

final class ArticleDTOTest extends TestCase
{
    public function testFromDatabase(): void
    {
        $row = [
            'id' => '1',
            'title' => 'Test Article',
            'slug' => 'test-article',
            'description' => 'Short excerpt',
            'content' => '<p>Full content</p>',
            'cover' => '/uploads/test.jpg',
            'category_id' => 5,
            'category_slug' => 'news',
            'published_at' => '2025-11-20 10:00:00',
            'views' => 100,
            'is_published' => 1,
            'author' => 'Admin',
            'date' => '2025-11-20',
            'tags' => '["tag1", "tag2"]',
            'icon' => 'bi-file-text',
        ];

        $dto = ArticleDTO::fromDatabase($row);

        $this->assertSame('1', $dto->id);
        $this->assertSame('Test Article', $dto->title);
        $this->assertSame('test-article', $dto->slug);
        $this->assertSame('Short excerpt', $dto->description);
        $this->assertSame('<p>Full content</p>', $dto->content);
        $this->assertSame('/uploads/test.jpg', $dto->cover);
        $this->assertSame(5, $dto->category_id);
        $this->assertSame('news', $dto->category);
        $this->assertSame('2025-11-20 10:00:00', $dto->published_at);
        $this->assertSame(100, $dto->views);
        $this->assertTrue($dto->active);
        $this->assertSame('Admin', $dto->author);
    }

    public function testFromRequestValidatesRequiredFields(): void
    {
        $data = [
            'title' => '',
            'slug' => '',
            'content' => '',
            'category' => '',
        ];

        $dto = ArticleDTO::fromRequest($data);
        $dto->validate();
        $errors = $dto->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('slug', $errors);
        $this->assertArrayHasKey('content', $errors);
        $this->assertArrayHasKey('category', $errors);
    }

    public function testFromRequestAcceptsValidData(): void
    {
        $data = [
            'title' => 'Valid Article',
            'slug' => 'valid-article',
            'description' => 'Excerpt',
            'content' => '<p>Content</p>',
            'category' => 'news',
            'category_id' => 1,
            'published_at' => '2025-12-01 12:00:00',
            'active' => true,
            'author' => 'Admin',
        ];

        $dto = ArticleDTO::fromRequest($data);
        $errors = $dto->getErrors();

        $this->assertEmpty($errors);
        $this->assertSame('Valid Article', $dto->title);
        $this->assertSame('valid-article', $dto->slug);
        $this->assertTrue($dto->active);
    }

    public function testToArray(): void
    {
        $dto = new ArticleDTO();
        $dto->title = 'Test';
        $dto->slug = 'test';
        $dto->content = '<p>Content</p>';
        $dto->category_id = 1;
        $dto->description = 'Description';
        $dto->author = 'Author';

        $array = $dto->toArray();

        $this->assertSame('Test', $array['title']);
        $this->assertSame('test', $array['slug']);
        $this->assertSame('<p>Content</p>', $array['content']);
        $this->assertSame(1, $array['category_id']);
    }
}
