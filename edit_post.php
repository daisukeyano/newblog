<?php
// simple_blog/edit_post.php - Edit Post Form
require_once __DIR__ . '/functions.php';

$csrf_token = generate_csrf_token();

$post_id = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';

$posts = get_all_posts();
$current_post = null;
foreach ($posts as $post) {
    if ($post['id'] === $post_id) {
        $current_post = $post;
        break;
    }
}

if (!$current_post) {
    header('Location: admin.php');
    exit;
}

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$display_embed_code = !empty($current_post['embed_html']) ? base64_decode_safe($current_post['embed_html']) : '';
$display_thumbnail_url = $current_post['thumbnail'] ?? ''; 

$page_title = "投稿編集 - " . htmlspecialchars($current_post['title']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?php include 'header.php'; // 共通ヘッダーをインクルード ?>
    <!-- <body>タグはheader.phpで開かれるため、ここでは閉じない -->

    <!-- ★修正点: パディングをTailwindクラスで指定★ -->
    <main class="container px-4 sm:px-6 md:px-10 lg:px-40">
        <div class="form-container">
            <h2>投稿編集</h2>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo htmlspecialchars($message['type']); ?>">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endif; ?>

            <form action="update_post.php" method="POST" enctype="multipart/form-data" onsubmit="return encodeEmbedCode();">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($current_post['id']); ?>">

                <div class="form-group">
                    <label for="title">タイトル</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($current_post['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="content_markdown">本文 (Markdown形式)</label>
                    <textarea id="content_markdown" name="content_markdown" rows="15" required><?php echo htmlspecialchars($current_post['content_markdown']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="thumbnail_file">サムネイル画像 (ファイルアップロード)</label>
                    <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
                    <?php if (!empty($current_post['thumbnail'])): ?>
                        <p style="font-size: 0.8em; color: #888; margin-top: 5px;">現在の画像: <img src="<?php echo htmlspecialchars($current_post['thumbnail']); ?>" style="max-width: 100px; max-height: 100px; vertical-align: middle;"></p>
                        <input type="hidden" name="existing_thumbnail" value="<?php echo htmlspecialchars($current_post['thumbnail']); ?>">
                        <label style="font-size: 0.9em; display: inline-block; margin-top: 5px;"><input type="checkbox" name="delete_thumbnail" value="1"> サムネイルを削除</label>
                    <?php endif; ?>
                    <p style="font-size: 0.8em; color: #888; margin-top: 5px;">または、以下のURLを指定:</p>
                </div>

                <div class="form-group">
                    <label for="thumbnail_url">サムネイル画像URL (ファイルアップロードが優先されます)</label>
                    <input type="url" id="thumbnail_url" name="thumbnail_url" value="<?php echo htmlspecialchars($display_thumbnail_url); ?>">
                </div>

                <div class="form-group">
                    <label for="embed_code">HTML埋め込みコード (例: Twitter, Instagramの埋め込み。末尾のscriptタグは削除してください)</label>
                    <textarea id="embed_code" name="embed_html_base64" rows="5"><?php echo htmlspecialchars($display_embed_code); ?></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">更新する</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo htmlspecialchars($current_post['id']); ?>', '<?php echo htmlspecialchars($csrf_token); ?>');">この投稿を削除</button>
                </div>
            </form>
        </div>
        <a href="index.php" class="back-link">← ホームに戻る</a>
    </main>

    <script>
        function encodeEmbedCode() {
            const embedCodeField = document.getElementById('embed_code');
            if (embedCodeField && embedCodeField.value.trim() !== '') {
                embedCodeField.value = btoa(embedCodeField.value);
            }
            return true;
        }

        function confirmDelete(postId, csrfToken) {
            if (confirm('本当にこの投稿を削除しますか？')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_post.php';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = postId;
                form.appendChild(idInput);

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>