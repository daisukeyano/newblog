<?php
// simple_blog/admin.php - New Post Form
require_once __DIR__ . '/functions.php';

$csrf_token = generate_csrf_token();

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$page_title = "新規投稿作成 - 管理ページ";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <?php include 'header.php'; // 共通ヘッダーをインクルード ?>
    <!-- <body>タグはheader.phpで開かれるため、ここでは閉じない -->

    <!-- ★修正点: パディングをTailwindクラスで指定★ -->
    <main class="container px-4 sm:px-6 md:px-10 lg:px-40">
        <div class="form-container">
            <h2>新規投稿作成</h2>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo htmlspecialchars($message['type']); ?>">
                    <?php echo htmlspecialchars($message['text']); ?>
                </div>
            <?php endif; ?>

            <form action="create_post.php" method="POST" enctype="multipart/form-data" onsubmit="return encodeEmbedCode();">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="title">タイトル</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="content_markdown">本文 (Markdown形式)</label>
                    <textarea id="content_markdown" name="content_markdown" rows="15" required></textarea>
                </div>

                <div class="form-group">
                    <label for="thumbnail_file">サムネイル画像 (ファイルアップロード)</label>
                    <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
                    <p style="font-size: 0.8em; color: #888; margin-top: 5px;">または、以下のURLを指定:</p>
                </div>

                <div class="form-group">
                    <label for="thumbnail_url">サムネイル画像URL (ファイルアップロードが優先されます)</label>
                    <input type="url" id="thumbnail_url" name="thumbnail_url">
                </div>

                <div class="form-group">
                    <label for="embed_code">HTML埋め込みコード (例: Twitter, Instagramの埋め込み。末尾のscriptタグは削除してください)</label>
                    <textarea id="embed_code" name="embed_html_base64" rows="5"></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">投稿する</button>
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
    </script>
</body>
</html>