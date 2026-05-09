<?php

declare(strict_types=1);

final class EventRepository
{
    /**
     * @return list<array<string,mixed>>
     */
    public function upcoming(int $limit): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id, slug, title, summary, starts_at, image
             FROM events
             WHERE starts_at >= NOW()
             ORDER BY starts_at ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
