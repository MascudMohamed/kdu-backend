<?php

declare(strict_types=1);

final class NewsRepository
{
    /**
     * @return array{items: list<array<string,mixed>>, total:int}
     */
    public function listFiltered(string $q, int $limit, int $offset): array
    {
        $pdo = Database::pdo();
        $where = 'published_at IS NOT NULL AND published_at <= NOW()';
        $params = [];

        if ($q !== '') {
            $safe = escape_like($q);
            $where .= ' AND (title LIKE :q OR excerpt LIKE :q OR body LIKE :q)';
            $params['q'] = '%' . $safe . '%';
        }

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM news WHERE $where");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $sql = "SELECT id, slug, title, excerpt, image, object_position AS objectPosition,
                       published_at, created_at
                FROM news WHERE $where
                ORDER BY published_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }
}
