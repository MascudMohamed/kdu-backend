<?php

declare(strict_types=1);

/**
 * Data access for programs — all queries use prepared statements.
 */
final class ProgramRepository
{
    /**
     * @return array{items: list<array<string,mixed>>, total:int}
     */
    public function listFiltered(string $q, ?string $level, int $limit, int $offset): array
    {
        $pdo = Database::pdo();

        $where = ['is_active = 1'];
        $params = [];

        if ($level !== null && $level !== '' && $level !== 'all') {
            $where[] = 'level = :level';
            $params['level'] = $level;
        }

        if ($q !== '') {
            // Use LIKE for portability; FULLTEXT is optional (see schema).
            $safe = escape_like($q);
            $where[] = '(title LIKE :q OR description LIKE :q)';
            $params['q'] = '%' . $safe . '%';
        }

        $sqlWhere = implode(' AND ', $where);

        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE $sqlWhere");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $sql = "SELECT id, slug, title, description AS `desc`, level, duration, campus, image, tags
                FROM programs WHERE $sqlWhere
                ORDER BY title ASC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            if (isset($row['tags']) && is_string($row['tags'])) {
                $decoded = json_decode($row['tags'], true);
                $row['tags'] = is_array($decoded) ? $decoded : [];
            }
        }
        unset($row);

        return ['items' => $rows, 'total' => $total];
    }
}
