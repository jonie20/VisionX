<?php
/**
 * VisionX Admin — FAQs API
 * File: admin/api/faqs.php
 *
 * GET  ?action=list       → all faqs ordered by sort_order
 * POST action=saveall     → { faqs: [{id?,question,answer},...] }  replaces all
 * POST action=delete      → { id }
 */
require_once __DIR__ . '/db.php';
guard();

$action = $_GET['action'] ?? body()['action'] ?? '';

// ── LIST ─────────────────────────────────────────────
if ($action === 'list') {
    $rows = db()->query('SELECT * FROM faqs WHERE active=1 ORDER BY sort_order ASC')->fetchAll();
    ok(['data' => $rows]);
}

// ── SAVE ALL (replace strategy — simple for small tables) ──
if ($action === 'saveall') {
    $b    = body();
    $faqs = $b['faqs'] ?? [];
    if (!is_array($faqs)) fail('faqs must be an array.');

    try {
        $pdo = db();
        $pdo->beginTransaction();

        // Delete all existing
        $pdo->exec('DELETE FROM faqs');

        // Re-insert in new order
        $stmt = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order, active) VALUES (?,?,?,1)');
        $i = 0;
        foreach ($faqs as $faq) {
            $q = trim($faq['question'] ?? '');
            $a = trim($faq['answer']   ?? '');
            if (!$q || !$a) continue;   // skip blank rows
            $stmt->execute([substr($q,0,400), $a, $i]);
            $i++;
        }

        $pdo->commit();
        ok(['saved' => $i]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        fail('Database error.', 500);
    }
}

// ── DELETE SINGLE ─────────────────────────────────────
if ($action === 'delete') {
    $b = body(); $id = (int)($b['id'] ?? 0);
    if (!$id) fail('ID required.');
    try { db()->prepare('DELETE FROM faqs WHERE id=?')->execute([$id]); ok(); }
    catch (PDOException $e) { fail('Database error.', 500); }
}

fail('Unknown action.');