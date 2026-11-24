<?php

namespace Tests\Unit;

use App\DTO\ArticleDTO;
use PHPUnit\Framework\TestCase;

class ArticleDTOTest extends TestCase
{
    public function testFromDatabase(): void
    {
        $row = [
            'id' => 1,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'excerpt' => 'Short excerpt',
            'content' => '<p>Full content</p>',
            'image' => '/uploads/test.jpg',
            'category_id' => 5,
            'category_slug' => 'news',
            'category_title' => 'Новости',
            'published_at' => '2025-11-20 10:00:00',
            'views' => 100,
            'is_featured' => 1,
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'meta_keywords' => 'test, article',
            'author_id' => 1,
            'author_name' => 'Admin',
            'created_at' => '2025-11-20 09:00:00',
            'updated_at' => '2025-11-20 10:00:00',
        ];

        $dto = ArticleDTO::fromDatabase($row);

        $this->assertEquals(1, $dto->id);
        $this->assertEquals('Test Article', $dto->title);
        $this->assertEquals('test-article', $dto->slug);
        $this->assertEquals('Short excerpt', $dto->excerpt);
        $this->assertEquals('<p>Full content</p>', $dto->content);
        $this->assertEquals('/uploads/test.jpg', $dto->image);
        $this->assertEquals(5, $dto->categoryId);
        $this->assertEquals('news', $dto->categorySlug);
        $this->assertEquals('Новости', $dto->categoryTitle);
        $this->assertEquals('2025-11-20 10:00:00', $dto->publishedAt);
        $this->assertEquals(100, $dto->views);
        $this->assertTrue($dto->isFeatured);
        $this->assertEquals('SEO Title', $dto->metaTitle);
        $this->assertEquals('SEO Description', $dto->metaDescription);
        $this->assertEquals('test, article', $dto->metaKeywords);
    }

    public function testFromRequestValidatesRequiredFields(): void
    {
        $data = [
            'title' => '',
            'slug' => '',
            'content' => '',
            'category_id' => 0,
        ];

        $dto = ArticleDTO::fromRequest($data);
        $errors = $dto->getErrors();

        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('slug', $errors);
        $this->assertArrayHasKey('content', $errors);
        $this->assertArrayHasKey('categoryId', $errors);
    }

    public function testFromRequestAcceptsValidData(): void
    {
        $data = [
            'title' => 'Valid Article',
            'slug' => 'valid-article',
            'excerpt' => 'Excerpt',
            'content' => '<p>Content</p>',
            'category_id' => 1,
            'published_at' => '2025-12-01 12:00:00',
            'is_featured' => true,
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'tag1, tag2',
        ];

        $dto = ArticleDTO::fromRequest($data);
        $errors = $dto->getErrors();

        $this->assertEmpty($errors);
        $this->assertEquals('Valid Article', $dto->title);
        $this->assertEquals('valid-article', $dto->slug);
        $this->assertTrue($dto->isFeatured);
    }

    public function testToArray(): void
    {
        $dto = new ArticleDTO();
        $dto->title = 'Test';
        $dto->slug = 'test';
        $dto->content = '<p>Content</p>';
        $dto->categoryId = 1;

        $array = $dto->toArray();

        $this->assertEquals('Test', $array['title']);
        $this->assertEquals('test', $array['slug']);
        $this->assertEquals('<p>Content</p>', $array['content']);
        $this->assertEquals(1, $array['category_id']);
    }
}