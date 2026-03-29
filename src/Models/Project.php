<?php

namespace App\Models;

use App\Core\Database;

class Project
{
    public static function all(?string $category = null): array
    {
        $pdo = Database::getInstance();

        if ($category) {
            $stmt = $pdo->prepare(
                'SELECT * FROM projects WHERE category = :category AND published = 1 ORDER BY sort_order ASC, created_at DESC'
            );
            $stmt->execute([':category' => $category]);
        } else {
            $stmt = $pdo->query(
                'SELECT * FROM projects WHERE published = 1 ORDER BY sort_order ASC, created_at DESC'
            );
        }

        return $stmt->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = :slug AND published = 1 LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function featured(int $limit = 6): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'SELECT * FROM projects WHERE published = 1 AND featured = 1 ORDER BY sort_order ASC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function categories(): array
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->query('SELECT DISTINCT category FROM projects WHERE published = 1 ORDER BY category');
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
