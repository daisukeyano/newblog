<?php
// simple_blog/update_post.php (再修正版)
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => '不正なリクエストです。(CSRFトークンエラー)'];
        header('Location: ' . (isset($_POST['post_id']) ? 'edit_post.php?id=' . urlencode($_POST['post_id']) : 'index.php'));
        exit;
    }

    // ★修正点1: フォームからの本文の変数名を変更★
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content_markdown = isset($_POST['content_markdown']) ? trim($_POST['content_markdown']) : ''; 
    // ★修正点2: フォームからの埋め込みHTMLの変数名を変更 (クライアントでBase64エンコード済み)★
    $embed_html_base64 = isset($_POST['embed_html_base64']) ? $_POST['embed_html_base64'] : null;

    // 現在の投稿データを取得
    $existing_post_data = get_post_by_id($post_id);
    if (!$existing_post_data) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 更新対象の投稿が見つかりません。'];
        header('Location: index.php');
        exit;
    }

    $current_thumbnail_path_in_db = $existing_post_data['thumbnail'] ?? null;
    $thumbnail_url_input = isset($_POST['thumbnail_url']) ? trim($_POST['thumbnail_url']) : null;
    $delete_thumbnail_checked = isset($_POST['delete_thumbnail']) && $_POST['delete_thumbnail'] == '1';

    $final_thumbnail_path = $current_thumbnail_path_in_db;

    // 1. サムネイル削除が指定されている場合
    if ($delete_thumbnail_checked) {
        $final_thumbnail_path = null;
        if ($current_thumbnail_path_in_db && strpos($current_thumbnail_path_in_db, 'data/uploads/') === 0) {
            $file_to_delete = UPLOADS_DIR . basename($current_thumbnail_path_in_db);
            if (file_exists($file_to_delete) && is_writable($file_to_delete)) {
                unlink($file_to_delete);
            }
        }
    } else {
        // 2. 新しいファイルがアップロードされた場合 (最優先)
        $uploaded_file_path = handle_thumbnail_upload('thumbnail_file');
        if ($uploaded_file_path) {
            $final_thumbnail_path = $uploaded_file_path;
            if ($current_thumbnail_path_in_db && strpos($current_thumbnail_path_in_db, 'data/uploads/') === 0) {
                $file_to_delete = UPLOADS_DIR . basename($current_thumbnail_path_in_db);
                if (file_exists($file_to_delete) && is_writable($file_to_delete)) {
                    unlink($file_to_delete);
                }
            }
        } 
        // 3. ファイルアップロードがなく、サムネイルURLが指定された場合
        else if (!empty($thumbnail_url_input)) {
            if (filter_var($thumbnail_url_input, FILTER_VALIDATE_URL)) {
                $final_thumbnail_path = $thumbnail_url_input;
                if ($current_thumbnail_path_in_db && strpos($current_thumbnail_path_in_db, 'data/uploads/') === 0) {
                    $file_to_delete = UPLOADS_DIR . basename($current_thumbnail_path_in_db);
                    if (file_exists($file_to_delete) && is_writable($file_to_delete)) {
                        unlink($file_to_delete);
                    }
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: サムネイルURLの形式が正しくありません。'];
                header('Location: edit_post.php?id=' . urlencode($post_id));
                exit;
            }
        } 
    }

    if (empty($post_id) || empty($title)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: ID、タイトルは必須です。'];
        header('Location: ' . ($post_id ? 'edit_post.php?id=' . urlencode($post_id) : 'index.php'));
        exit;
    }

    $updated_post_data = [
        'id' => $post_id,
        'title' => $title,
        'date' => $existing_post_data['date'],
        'thumbnail' => $final_thumbnail_path,
        'content_markdown' => $content_markdown,
        'embed_html' => !empty($embed_html_base64) ? $embed_html_base64 : null
    ];

    if (update_post_entry($updated_post_data)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => '投稿が正常に更新されました。'];
        header('Location: post.php?id=' . urlencode($post_id));
    } else {
         if (!isset($_SESSION['message'])) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 投稿の更新に失敗しました。'];
        }
        header('Location: edit_post.php?id=' . urlencode($post_id));
    }
    exit;

} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 不正なアクセスです。'];
    header('Location: index.php');
    exit;
}