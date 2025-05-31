<?php
// simple_blog/create_post.php (再修正版)
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => '不正なリクエストです。(CSRFトークンエラー)'];
        header('Location: admin.php');
        exit;
    }

    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    // ★修正点1: フォームからの本文の変数名を変更★
    $content_markdown = isset($_POST['content_markdown']) ? trim($_POST['content_markdown']) : '';
    // ★修正点2: フォームからの埋め込みHTMLの変数名を変更 (クライアントでBase64エンコード済み)★
    $embed_html_base64 = isset($_POST['embed_html_base64']) ? $_POST['embed_html_base64'] : null;

    if (empty($title) || empty($content_markdown)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'タイトルと本文は必須です。'];
        header('Location: admin.php');
        exit;
    }

    $final_thumbnail_path = null; // 最終的にDBに保存するサムネイルパスを初期化
    $thumbnail_url_input = isset($_POST['thumbnail_url']) ? trim($_POST['thumbnail_url']) : null;

    // 1. 新しいファイルがアップロードされた場合 (最優先)
    $uploaded_file_path = handle_thumbnail_upload('thumbnail_file');
    if ($uploaded_file_path) {
        $final_thumbnail_path = $uploaded_file_path; // 新しいアップロードパスを使用
    } 
    // 2. ファイルアップロードがなく、サムネイルURLが指定された場合
    else if (!empty($thumbnail_url_input)) {
        if (filter_var($thumbnail_url_input, FILTER_VALIDATE_URL)) {
            $final_thumbnail_path = $thumbnail_url_input; // 新しいURLを使用
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: サムネイルURLの形式が正しくありません。'];
            header('Location: admin.php'); // 新規投稿ページに戻る
            exit;
        }
    } 
    // 3. ファイルアップロードもURL指定もなければ null のまま


    $new_post_id = generate_unique_id(); // functions.php の generate_unique_id() を使用

    $new_post = [
        'id' => $new_post_id,
        'title' => $title,
        'date' => date('Y-m-d H:i:s'),
        'thumbnail' => $final_thumbnail_path, // 決定されたサムネイルパスをセット
        'content_markdown' => $content_markdown,
        'embed_html' => !empty($embed_html_base64) ? $embed_html_base64 : null // クライアントでBase64済みなのでそのまま保存
    ];

    if (add_post_entry($new_post)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => '新しい投稿が作成されました。'];
        header('Location: post.php?id=' . $new_post_id);
        exit;
    } else {
        if (!isset($_SESSION['message'])) { // add_post_entry がすでにメッセージをセットしている可能性も考慮
            $_SESSION['message'] = ['type' => 'error', 'text' => '投稿の保存に失敗しました。'];
        }
        header('Location: admin.php');
        exit;
    }
} else {
    header('Location: admin.php');
    exit;
}