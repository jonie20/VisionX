<?php
/**
 * VisionX Admin — Reviews API
 * File: admin/api/reviews.php
 *
 * GET  ?action=list    → all reviews
 * POST action=save     → { id?, author, area, rating, body, status }
 * POST action=delete   → { id }
 * POST action=approve  → { id, status }
 */
require_once __DIR__ . '/db.php';
guard();

$action = $_GET['action'] ?? body()['action'] ?? '';

// ── LIST ─────────────────────────────────────────────
if ($action === 'list') {
    $rows = db()->query('SELECT * FROM reviews ORDER BY created_at DESC')->fetchAll();
    ok(['data' => $rows]);
}

// ── SAVE ─────────────────────────────────────────────
if ($action === 'save') {
    $b      = body();
    $id     = (int)($b['id']     ?? 0);
    $author = trim($b['author']  ?? '');
    $area   = trim($b['area']    ?? '');
    $rating = max(1, min(5, (int)($b['rating'] ?? 5)));
    $text   = trim($b['body']    ?? '');
    $status = in_array($b['status'] ?? '', ['approved','pending','hidden']) ? $b['status'] : 'pending';

    if (!$author || !$text) fail('Author and review text are required.');

    try {
        $pdo = db();
        if ($id) {
            $pdo->prepare('UPDATE reviews SET author=?,area=?,rating=?,body=?,status=? WHERE id=?')
                ->execute([$author,$area,$rating,$text,$status,$id]);
            ok(['id' => $id]);
        } else {
            $pdo->prepare('INSERT INTO reviews (author,area,rating,body,status) VALUES (?,?,?,?,?)')
                ->execute([$author,$area,$rating,$text,$status]);
            ok(['id' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) { error_log($e->getMessage()); fail('Database error.', 500); }
}

// ── APPROVE / REJECT ─────────────────────────────────
if ($action === 'approve') {
    $b      = body();
    $id     = (int)($b['id'] ?? 0);
    $status = in_array($b['status'] ?? '', ['approved','pending','hidden']) ? $b['status'] : 'pending';
    if (!$id) fail('ID required.');
    try {
        db()->prepare('UPDATE reviews SET status=? WHERE id=?')->execute([$status, $id]);
        ok();
    } catch (PDOException $e) { fail('Database error.', 500); }
}

// ── DELETE ───────────────────────────────────────────
if ($action === 'delete') {
    $b  = body(); $id = (int)($b['id'] ?? 0);
    if (!$id) fail('ID required.');
    try { db()->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]); ok(['deleted' => $id]); }
    catch (PDOException $e) { fail('Database error.', 500); }
}

fail('Unknown action.');