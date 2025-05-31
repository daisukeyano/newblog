<?php
// エラー表示を有効にする（開発時）
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

define('JSON_FILE_PATH', __DIR__ . '/data/posts.json');
// ★修正点1: UPLOADS_DIR のパスを修正 (data/uploads/ に変更)★
define('UPLOADS_DIR', __DIR__ . '/data/uploads/'); 

// CSRFトークンを生成・検証する関数
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    // hash_equals を使用してタイミング攻撃を防ぐ
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 全ての投稿を取得する関数
// (以前の get_posts() は get_all_posts() に名称変更されています)
function get_all_posts($sort = true) {
    if (!file_exists(JSON_FILE_PATH)) {
        return [];
    }
    $json_data = file_get_contents(JSON_FILE_PATH);
    if ($json_data === false) {
        error_log("Failed to read JSON file: " . JSON_FILE_PATH);
        return [];
    }
    $posts = json_decode($json_data, true);

    if ($posts === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error in get_all_posts: " . json_last_error_msg() . " File content: " . $json_data);
        return [];
    }
    $posts = $posts ?: []; // $posts が null や false の場合に空配列を代入

    if ($sort && is_array($posts)) {
        usort($posts, function($a, $b) {
            $timeA = isset($a['date']) ? strtotime($a['date']) : 0;
            $timeB = isset($b['date']) ? strtotime($b['date']) : 0;
            return $timeB - $timeA; // 新しい投稿が先頭に来るように降順
        });
    }
    return $posts;
}

// IDで特定の投稿を取得する関数
function get_post_by_id($id) {
    $posts = get_all_posts(false); // ソートは不要なので false
    foreach ($posts as $post) {
        if (isset($post['id']) && $post['id'] === $id) {
            return $post;
        }
    }
    return null;
}

// 投稿を保存する関数
function save_posts($posts) {
    // save_posts 内でのソートは get_all_posts で行われるため、ここからは削除
    // しかし、get_all_posts(false) で取得した場合、ここでソートしないと保存順がバラバラになる
    // 既存のget_all_posts()がソートを制御しているので、今回はsave_posts内でのソートはそのまま残します
    if (is_array($posts)) {
        usort($posts, function($a, $b) {
            $timeA = isset($a['date']) ? strtotime($a['date']) : 0;
            $timeB = isset($b['date']) ? strtotime($b['date']) : 0;
            return $timeB - $timeA;
        });
    } else {
        $posts = [];
    }

    $json_data = json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json_data === false) {
        error_log("JSON encode error in save_posts: " . json_last_error_msg());
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 投稿データのJSONエンコードに失敗しました。'];
        return false;
    }

    $data_dir = dirname(JSON_FILE_PATH);
    if (!is_dir($data_dir)) {
        if (!mkdir($data_dir, 0775, true) && !is_dir($data_dir)) { // 厳密なチェック
            error_log("Failed to create directory: " . $data_dir);
            $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: dataディレクトリの作成に失敗しました。'];
            return false;
        }
    }
    if (!is_writable($data_dir)) {
        error_log("Directory not writable: " . $data_dir);
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: dataディレクトリに書き込み権限がありません。'];
        return false;
    }
    if (file_exists(JSON_FILE_PATH) && !is_writable(JSON_FILE_PATH)) {
        error_log("File not writable: " . JSON_FILE_PATH);
         $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: posts.jsonファイルに書き込み権限がありません。'];
        return false;
    }
    if (file_put_contents(JSON_FILE_PATH, $json_data, LOCK_EX) === false) {
        error_log("Failed to write to JSON file: " . JSON_FILE_PATH . " Error: " . print_r(error_get_last(), true));
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: posts.jsonファイルへの書き込みに失敗しました。'];
        return false;
    }
    return true;
}

// 新しい投稿を追加する関数
function add_post_entry($new_post) {
    $posts = get_all_posts(false); // ソートせずに取得
    array_unshift($posts, $new_post); // 新しい投稿を配列の先頭に追加
    return save_posts($posts);
}

// 既存の投稿を更新する関数
function update_post_entry($updated_post_data) {
    $posts = get_all_posts(false); // ソートせずに取得
    $found = false;
    foreach ($posts as $key => $post) {
        if (isset($post['id']) && $post['id'] === $updated_post_data['id']) {
            $posts[$key]['title'] = $updated_post_data['title'];
            // 投稿日は変更しない場合は $post['date'] を使う
            // $posts[$key]['date'] = $updated_post_data['date']; 
            $posts[$key]['thumbnail'] = $updated_post_data['thumbnail'];
            $posts[$key]['content_markdown'] = $updated_post_data['content_markdown'];
            $posts[$key]['embed_html'] = $updated_post_data['embed_html'];
            $found = true;
            break;
        }
    }
    if ($found) {
        return save_posts($posts);
    }
    $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 更新対象の投稿が見つかりませんでした。(ID: ' . ($updated_post_data['id'] ?? '不明') . ')'];
    return false;
}

// IDで投稿を削除する関数
function delete_post_by_id($id) {
    $posts = get_all_posts(false); // ソートせずに取得
    $initial_count = count($posts);
    $deleted_post_thumbnail = null;

    $posts = array_filter($posts, function($post) use ($id, &$deleted_post_thumbnail) {
        if (isset($post['id']) && $post['id'] === $id) {
            $deleted_post_thumbnail = $post['thumbnail'] ?? null;
            return false; // この投稿を削除
        }
        return true;
    });

    if (count($posts) < $initial_count) { // 投稿が実際に削除された場合
        if (save_posts(array_values($posts))) { // array_valuesでインデックスを再構築
            // 関連するサムネイルファイルがローカルアップロードされたものであれば削除
            // 'data/uploads/' というプレフィックスでローカルアップロードかを判断
            if ($deleted_post_thumbnail && strpos($deleted_post_thumbnail, 'data/uploads/') === 0) {
                $file_to_delete = UPLOADS_DIR . basename($deleted_post_thumbnail);
                if (file_exists($file_to_delete) && is_writable($file_to_delete)) {
                    unlink($file_to_delete);
                }
            }
            return true;
        }
        return false;
    }
    $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 削除対象の投稿が見つかりませんでした。(ID: ' . $id . ')'];
    return false;
}

// MarkdownテキストをHTMLに変換する関数
function parse_markdown_text($markdown_text) {
    if (empty(trim($markdown_text))) {
        return '';
    }
    // Parsedown.php は functions.php の最後で読み込み済みなので、ここで再度読み込まない
    global $Parsedown; 
    if (!isset($Parsedown)) { // 念のためのチェック
        require_once __DIR__ . '/Parsedown.php';
        $Parsedown = new Parsedown();
    }
    return $Parsedown->text($markdown_text);
}

// ★修正点2: handle_thumbnail_upload 関数をシンプル化 (ファイルアップロードのみ)★
// 古いファイルの削除や、URLの優先順位付けは呼び出し元 (create/update_post.php) で行う
function handle_thumbnail_upload($file_input_name) {
    // ファイルが選択されていない、またはエラーがある場合は null を返す
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_NO_FILE) {
             $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'php.iniのupload_max_filesize超過。',
                UPLOAD_ERR_FORM_SIZE  => 'フォームのMAX_FILE_SIZE超過。',
                UPLOAD_ERR_PARTIAL    => '一部のみアップロード。',
                UPLOAD_ERR_NO_TMP_DIR => 'テンポラリフォルダなし。',
                UPLOAD_ERR_CANT_WRITE => 'ディスク書き込み失敗。',
                UPLOAD_ERR_EXTENSION  => 'PHP拡張機能による中止。',
            ];
            $error_message = $upload_errors[$_FILES[$file_input_name]['error']] ?? '不明なアップロードエラー。';
            $_SESSION['message'] = ['type' => 'error', 'text' => 'エラーコード ' . $_FILES[$file_input_name]['error'] . ': ' . $error_message];
        }
        return null;
    }

    $file = $_FILES[$file_input_name];
    $upload_dir = UPLOADS_DIR;

    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
             $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: アップロードディレクトリ (' . $upload_dir . ') の作成に失敗しました。'];
            return null;
        }
    }
    if (!is_writable($upload_dir)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: アップロードディレクトリ (' . $upload_dir . ') に書き込み権限がありません。'];
        return null;
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($file_mime_type, $allowed_mimes)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 許可されていないファイル形式です。'];
        return null;
    }
    
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: 許可されていないファイル拡張子です。'];
        return null;
    }

    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: ファイルサイズが大きすぎます (最大2MB)。'];
        return null;
    }

    $new_filename = uniqid('thumb_', true) . '.' . $file_extension;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // ★HTMLで参照するためのパスを返す (例: data/uploads/...)★
        return 'data/uploads/' . $new_filename; 
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'エラー: ファイルのアップロード処理に失敗しました。'];
        return null;
    }
}

// functions.php の最下部近くでParsedownインスタンスを初期化
require_once __DIR__ . '/Parsedown.php';
$Parsedown = new Parsedown();

// Base64エンコード/デコードの共通関数
function base64_encode_safe($data) {
    return base64_encode($data);
}

function base64_decode_safe($data) {
    return base64_decode($data);
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function truncate_text($text, $length = 150) {
    if (mb_strlen($text, 'UTF-8') > $length) {
        return mb_substr($text, 0, $length, 'UTF-8') . '...';
    }
    return $text;
}

// 投稿データのID生成関数
function generate_unique_id() {
    return uniqid('', true);
}