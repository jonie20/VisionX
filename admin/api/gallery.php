<?php
/**
 * VisionX Admin — Gallery API
 * File: admin/api/gallery.php
 *
 * GET  ?action=list               → all gallery rows
 * POST action=save  (multipart)   → insert or update row + optional image upload
 * POST action=delete              → { id }
 * POST action=toggle              → { id, active }
 */
require_once __DIR__ . '/db.php';
guard();

$action = $_GET['action'] ?? ($_POST['action'] ?? body()['action'] ?? '');

// ── LIST ─────────────────────────────────────────────
if ($action === 'list') {
    $rows = db()->query('SELECT * FROM gallery ORDER BY sort_order ASC, id DESC')->fetchAll();
    ok(['data' => $rows]);
}

// ── SAVE (insert or update) ──────────────────────────
if ($action === 'save') {
    $id     = (int)($_POST['id']     ?? 0);
    $title  = trim($_POST['title']   ?? '');
    $brand  = trim($_POST['brand']   ?? '');
    $area   = trim($_POST['area']    ?? '');
    $appl   = $_POST['appliance']    ?? 'fridge';
    $status = $_POST['status']       ?? 'after';
    $fault  = trim($_POST['fault']   ?? '');
    $desc   = trim($_POST['desc']    ?? '');
    $sort   = (int)($_POST['sort']   ?? 0);
    $active = (int)($_POST['active'] ?? 1);

    if (!$title) fail('Title is required.');

    // Auto alt text
    $appl_label = str_replace('-', ' ', $appl);
    $alt = $brand && $area
        ? "$brand $appl_label repair $area Nairobi VisionX Repairs"
        : "$title – VisionX Repairs Nairobi";

    // Handle image upload
    $imgPath = null;
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $prefix  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', "$brand-$appl-$area"));
        $imgPath = saveImage('photo', $prefix);
    }

    try {
        $pdo = db();
        if ($id) {
            // Update — only overwrite img_path if a new file was uploaded
            $sql = 'UPDATE gallery SET title=?,brand=?,area=?,appliance=?,status=?,fault=?,description=?,img_alt=?,sort_order=?,active=?';
            $params = [$title,$brand,$area,$appl,$status,$fault,$desc,$alt,$sort,$active];
            if ($imgPath) { $sql .= ',img_path=?'; $params[] = $imgPath; }
            $sql .= ' WHERE id=?'; $params[] = $id;
            $pdo->prepare($sql)->execute($params);
            ok(['id' => $id]);
        } else {
            $pdo->prepare('INSERT INTO gallery (title,brand,area,appliance,status,fault,description,img_path,img_alt,sort_order,active) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$title,$brand,$area,$appl,$status,$fault,$desc,$imgPath??'',$alt,$sort,$active]);
            ok(['id' => $pdo->lastInsertId()]);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        fail('Database error.', 500);
    }
}

// ── DELETE ───────────────────────────────────────────
if ($action === 'delete') {
    $b  = body();
    $id = (int)($b['id'] ?? 0);
    if (!$id) fail('ID required.');
    try {
        // Remove image file if present
        $row = db()->prepare('SELECT img_path FROM gallery WHERE id=?');
        $row->execute([$id]);
        $img = $row->fetchColumn();
        if ($img) {
            $full = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($img, '/');
            if (file_exists($full)) unlink($full);
        }
        db()->prepare('DELETE FROM gallery WHERE id=?')->execute([$id]);
        ok(['deleted' => $id]);
    } catch (PDOException $e) { fail('Database error.', 500); }
}

// ── TOGGLE ACTIVE ────────────────────────────────────
if ($action === 'toggle') {
    $b = body();
    $id = (int)($b['id'] ?? 0); $active = (int)($b['active'] ?? 0);
    if (!$id) fail('ID required.');
    try {
        db()->prepare('UPDATE gallery SET active=? WHERE id=?')->execute([$active, $id]);
        ok();
    } catch (PDOException $e) { fail('Database error.', 500); }
}

fail('Unknown action.');