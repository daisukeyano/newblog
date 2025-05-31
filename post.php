<?php
// simple_blog/post.php
require_once __DIR__ . '/functions.php';

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
    header('Location: index.php');
    exit;
}

$post_title = htmlspecialchars($current_post['title']);
$post_date_formatted = htmlspecialchars(date('M d, Y', strtotime($current_post['date'])));
$post_body_html = parse_markdown_text($current_post['content_markdown']);
// ★修正点1: プレースホルダー画像を placehold.co に変更★
$post_thumbnail = !empty($current_post['thumbnail']) ? htmlspecialchars($current_post['thumbnail']) : 'https://placehold.co/960x640/EAEAEA/888888?text=No+Image';
$post_embed_html = '';

if (!empty($current_post['embed_html'])) {
    $post_embed_html = base64_decode_safe($current_post['embed_html']);
    if (strpos($post_embed_html, 'twitter.com') !== false) {
        $post_embed_html .= '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
    }
    if (strpos($post_embed_html, 'instagram.com') !== false) {
        $post_embed_html .= '<script async src="//www.instagram.com/embed.js"></script>';
    }
}

$page_title = $post_title . " - AI News";
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <?php include 'header.php'; ?>
    <!-- <body>タグはheader.phpで開かれるため、ここでは閉じない -->

        <!-- ★修正点2: px-40-adjusted を Tailwind クラスに置き換え★ -->
        <div class="px-4 sm:px-6 md:px-10 lg:px-40 flex flex-1 justify-center py-5">
          <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <!-- 投稿タイトルと日付 -->
            <h1 class="text-[#111418] tracking-light text-[28px] sm:text-[32px] font-bold leading-tight px-4 text-left pb-3 pt-6"><?php echo $post_title; ?></h1>
            <p class="text-[#60758a] text-sm font-normal leading-normal pb-3 pt-1 px-4">Published on <?php echo $post_date_formatted; ?></p>
            
            <!-- メイン画像（サムネイル） -->
            <div class="flex w-full grow bg-white @container py-3">
              <div class="w-full gap-1 overflow-hidden bg-white @[480px]:gap-2 aspect-[3/2] flex">
                <div
                  class="w-full bg-center bg-no-repeat bg-cover aspect-auto rounded-none flex-1"
                  style='background-image: url("<?php echo $post_thumbnail; ?>");'
                ></div>
              </div>
            </div>

            <!-- HTML埋め込みコンテンツ -->
            <?php if (!empty($post_embed_html)): ?>
                <div class="post-content p-4">
                    <?php echo $post_embed_html; ?>
                </div>
            <?php endif; ?>

            <!-- 本文（Markdownから変換されたHTML） -->
            <!-- ★修正点3: 本文内の要素にも px-4 を追加して左右パディングを適用★ -->
            <div class="post-content pt-1">
                <?php echo str_replace(['<p>', '<ul>', '<ol>', '<blockquote>', '<pre>'], ['<p class="px-4">', '<ul class="px-4">', '<ol class="px-4">', '<blockquote class="px-4">', '<pre class="px-4">'], $post_body_html); ?>
            </div>

            <!-- 編集ボタンと戻るリンク -->
            <div class="px-4 py-5 flex flex-col sm:flex-row justify-end gap-3">
                <a href="edit_post.php?id=<?php echo htmlspecialchars($current_post['id']); ?>" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em]">
                    <span class="truncate">この記事を編集</span>
                </a>
                <a href="javascript:history.back()" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em]">
                    <span class="truncate">← 戻る</span>
                </a>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- 管理ページへの固定ボタン -->
    <a href="admin.php" class="admin-fixed-button" title="管理ページへ">管理<br>ページ</a>
  </body>
</html>