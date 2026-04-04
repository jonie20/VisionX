<?php
/**
 * VisionX Admin — Gallery API
 * File: admin/api/gallery.php
 *
 * Routes via GET parameter:  ?action=list | save | delete | toggle
 * POST body is multipart/form-data (for save) or JSON (for delete/toggle)
 *
 * The frontend calls vxApi('gallery/list.php') etc., so four thin
 * shim files re-include this file with $_GET['action'] pre-set.
 * See: list.php / save.php / delete.php / status.php  (in same folder)
 */
require_once __DIR__ . '/db.php';
guard();

// ══════════════════════════════════════════════════════════
//  PATH HELPERS
//
//  File tree:
//    VisionX/                        ← project root
//      admin/
//        api/
//          gallery.php               ← __DIR__
//      assets/
//        images/
//          gallery/                  ← upload destination
//
//  dirname(__DIR__, 2) = VisionX/    ← project root ✓
// ══════════════════════════════════════════════════════════

function projectRoot(): string
{
    return dirname(__DIR__, 2);
    // C:\xampp\htdocs\VisionX
}

function galleryUploadDir(): string
{
    return projectRoot()
        . DIRECTORY_SEPARATOR . 'assets'
        . DIRECTORY_SEPARATOR . 'images'
        . DIRECTORY_SEPARATOR . 'gallery'
        . DIRECTORY_SEPARATOR;
    // C:\xampp\htdocs\VisionX\assets\images\gallery\
}

// ══════════════════════════════════════════════════════════
//  IMAGE UPLOAD HELPER
// ══════════════════════════════════════════════════════════

function saveGalleryImage(string $fileKey, string $prefix): string
{
    if (empty($_FILES[$fileKey])) {
        throw new RuntimeException("No \$_FILES entry for key: $fileKey");
    }

    $file = $_FILES[$fileKey];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $codes = [
            UPLOAD_ERR_INI_SIZE   => 'Exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE  => 'Exceeds MAX_FILE_SIZE in form',
            UPLOAD_ERR_PARTIAL    => 'Only partially uploaded',
            UPLOAD_ERR_NO_FILE    => 'No file sent',
            UPLOAD_ERR_NO_TMP_DIR => 'PHP temp directory missing',
            UPLOAD_ERR_CANT_WRITE => 'PHP cannot write to temp dir',
            UPLOAD_ERR_EXTENSION  => 'Blocked by PHP extension',
        ];
        throw new RuntimeException($codes[$file['error']] ?? 'Upload error code ' . $file['error']);
    }

    // Validate real MIME — never trust $_FILES['type']
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime    = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException("File type not allowed: $mime. Use JPG, PNG, or WebP.");
    }

    // 2 MB cap
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Image exceeds 2 MB. Resize before uploading.');
    }

    // Ensure directory exists
    $uploadDir = galleryUploadDir();
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
        throw new RuntimeException('Could not create upload directory: ' . $uploadDir);
    }
    if (!is_writable($uploadDir)) {
        throw new RuntimeException('Upload directory not writable: ' . $uploadDir);
    }

    // SEO-friendly, collision-safe filename
    // e.g. samsung-fridge-westlands-4a9f2c.jpg
    $ext      = $allowed[$mime];
    $slug     = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $prefix)), '-');
    $filename = $slug . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException(
            'move_uploaded_file() failed. dest: ' . $destPath
        );
    }

    // Always return forward-slash path for web URLs / DB storage
    return 'assets/images/gallery/' . $filename;
}


// ══════════════════════════════════════════════════════════
//  ROUTE — action comes from GET param (set by shim files)
// ══════════════════════════════════════════════════════════

$action = $_GET['action'] ?? '';

// ── LIST ─────────────────────────────────────────────────
if ($action === 'list') {
    $rows = db()->query(
        'SELECT * FROM gallery ORDER BY sort_order ASC, id DESC'
    )->fetchAll(PDO::FETCH_ASSOC);

    // Map DB names → frontend JS names
    //   DB: appliance / active  →  JS: type / is_active
    $rows = array_map(function ($r) {
        $r['type']      = $r['appliance'];
        $r['is_active'] = (int) $r['active'];
        return $r;
    }, $rows);

    ok(['data' => $rows]);
}

// ── SAVE ─────────────────────────────────────────────────
if ($action === 'save') {

    $id     = (int)   ($_POST['id']                                    ?? 0);
    $title  = trim(    $_POST['title']                                  ?? '');
    $brand  = trim(    $_POST['brand']                                  ?? '');
    $area   = trim(    $_POST['area']                                   ?? '');
    $appl   = trim(    $_POST['type']       ?? ($_POST['appliance']     ?? 'fridge'));
    $status = trim(    $_POST['status']                                 ?? 'after');
    $fault  = trim(    $_POST['fault']                                  ?? '');
    $desc   = trim(    $_POST['description'] ?? ($_POST['desc']         ?? ''));
    $alt    = trim(    $_POST['alt']                                    ?? '');
    $sort   = (int)   ($_POST['sort_order']  ?? ($_POST['sort']         ?? 0));
    $active = (int)   ($_POST['is_active']   ?? ($_POST['active']       ?? 1));

    if (!$title) fail('Title is required.');
    if (!$brand) fail('Brand is required.');
    if (!$area)  fail('Area is required.');

    if ($alt === '') {
        $alt = $brand . ' ' . ucwords(str_replace('-', ' ', $appl))
             . ' repair ' . $area . ' Nairobi VisionX Repairs';
    }

    // Image upload
    $imgPath = null;
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        try {
            $imgPath = saveGalleryImage('photo', "$brand-$appl-$area");
        } catch (RuntimeException $e) {
            error_log('[gallery/save] ' . $e->getMessage());
            fail('Image upload failed: ' . $e->getMessage());
        }
    } elseif (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        fail('Upload error (code ' . $_FILES['photo']['error'] . ').');
    }

    try {
        $pdo = db();

        if ($id) {
            if ($imgPath) {
                // Remove old image file
                $old = $pdo->prepare('SELECT img_path FROM gallery WHERE id = ?');
                $old->execute([$id]);
                $oldPath = $old->fetchColumn();
                if ($oldPath) {
                    $abs = projectRoot() . DIRECTORY_SEPARATOR
                         . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $oldPath), DIRECTORY_SEPARATOR);
                    if (file_exists($abs)) unlink($abs);
                }

                $pdo->prepare(
                    'UPDATE gallery
                        SET title=?, brand=?, area=?, appliance=?, status=?,
                            fault=?, description=?, img_path=?, img_alt=?,
                            sort_order=?, active=?
                      WHERE id=?'
                )->execute([$title,$brand,$area,$appl,$status,$fault,$desc,$imgPath,$alt,$sort,$active,$id]);

            } else {
                $pdo->prepare(
                    'UPDATE gallery
                        SET title=?, brand=?, area=?, appliance=?, status=?,
                            fault=?, description=?, img_alt=?,
                            sort_order=?, active=?
                      WHERE id=?'
                )->execute([$title,$brand,$area,$appl,$status,$fault,$desc,$alt,$sort,$active,$id]);
            }

            ok(['success' => true, 'id' => $id, 'img_path' => $imgPath]);

        } else {
            $pdo->prepare(
                'INSERT INTO gallery
                    (title, brand, area, appliance, status, fault, description,
                     img_path, img_alt, sort_order, active)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $title, $brand, $area, $appl, $status, $fault, $desc,
                $imgPath ?? '', $alt, $sort, $active,
            ]);

            ok(['success' => true, 'id' => $pdo->lastInsertId(), 'img_path' => $imgPath]);
        }

    } catch (PDOException $e) {
        error_log('[gallery/save] DB: ' . $e->getMessage());
        fail('Database error.', 500);
    }
}

// ── DELETE ────────────────────────────────────────────────
if ($action === 'delete') {
    $b  = body();
    $id = (int) ($b['id'] ?? 0);
    if (!$id) fail('ID required.');

    try {
        $pdo  = db();
        $stmt = $pdo->prepare('SELECT img_path FROM gallery WHERE id = ?');
        $stmt->execute([$id]);
        $img  = $stmt->fetchColumn();

        if ($img) {
            $abs = projectRoot() . DIRECTORY_SEPARATOR
                 . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $img), DIRECTORY_SEPARATOR);
            if (file_exists($abs)) unlink($abs);
        }

        $pdo->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
        ok(['success' => true, 'deleted' => $id]);

    } catch (PDOException $e) {
        error_log('[gallery/delete] ' . $e->getMessage());
        fail('Database error.', 500);
    }
}

// ── TOGGLE ACTIVE ─────────────────────────────────────────
if ($action === 'toggle') {
    $b      = body();
    $id     = (int) ($b['id']     ?? 0);
    $active = (int) ($b['active'] ?? 0);
    if (!$id) fail('ID required.');

    try {
        db()->prepare('UPDATE gallery SET active = ? WHERE id = ?')->execute([$active, $id]);
        ok(['success' => true]);
    } catch (PDOException $e) {
        error_log('[gallery/toggle] ' . $e->getMessage());
        fail('Database error.', 500);
    }
}

fail('Unknown action.');