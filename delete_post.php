<?php
// simple_blog/delete_post.php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => '不正なリクエストです。(CSRFトークンエラー)'];
        header('Location: admin.php');
        exit;
    }

    // ★修正点1: IDの取得方法を 'id' に統一★
    $id = isset($_POST['id']) ? sanitize_input($_POST['id']) : '';

    if (empty($id)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => '削除する投稿IDが指定されていません。'];
        header('Location: admin.php');
        exit;
    }

    if (delete_post_by_id($id)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => '投稿が削除されました。'];
    } else {
        // delete_post_by_id がすでにメッセージをセットしている可能性を考慮
        if (!isset($_SESSION['message'])) { 
            $_SESSION['message'] = ['type' => 'error', 'text' => '投稿の削除に失敗しました。'];
        }
    }

    header('Location: index.php');
    exit;
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 不正なアクセスです。'];
    header('Location: admin.php');
    exit;
}