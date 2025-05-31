<?php
// simple_blog/index.php
require_once __DIR__ . '/functions.php';

$posts = get_all_posts();

$hero_post = null;
$featured_posts = [];
$latest_news_posts = [];

if (!empty($posts)) {
    $hero_post = $posts[0];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($posts[$i])) {
            $featured_posts[] = $posts[$i];
        }
    }
    // Latest Newsは4番目以降の投稿をすべて取得
    $latest_news_posts = array_slice($posts, 4);
}

// ★修正点1: プレースホルダー画像を placehold.co に変更★
$placeholder_thumbnail = 'https://placehold.co/960x480/EAEAEA/888888?text=No+Image';
$placeholder_card_thumbnail = 'https://placehold.co/400x200/EAEAEA/888888?text=No+Image';

$page_title = "AI News - PHPシンプルブログ";
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <?php include 'header.php'; ?>
    <!-- <body>タグはheader.phpで開かれるため、ここでは閉じない -->

        <!-- ★修正点2: px-40-adjusted を Tailwind クラスに置き換え★ -->
        <div class="px-4 sm:px-6 md:px-10 lg:px-40 flex flex-1 justify-center py-5">
          <div class="layout-content-container flex flex-col max-w-[960px] flex-1">
            <?php if (empty($posts)): ?>
                <div class="py-10 text-center text-[#60758a]">
                    <p>まだ投稿がありません。管理ページから新しい投稿を作成してください。</p>
                </div>
            <?php else: ?>
                <!-- メインの大きな記事 -->
                <div class="@container">
                <div class="p-4 sm:p-6 md:p-8"> <!-- パディングを広げ、全体的な余白を追加 -->
                    <?php if ($hero_post): ?>
                    <a href="post.php?id=<?php echo htmlspecialchars($hero_post['id']); ?>"
                        class="bg-cover bg-center flex flex-col justify-end overflow-hidden bg-white @[480px]:rounded-lg min-h-60 sm:min-h-80"
                        style='background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 25%), url("<?php echo htmlspecialchars($hero_post['thumbnail'] ?: $placeholder_thumbnail); ?>");'>
                        <div class="flex p-4 sm:p-6 md:p-8"> <!-- タイトルエリアのパディングを増やし、タイトルを画像の下部に配置 -->
                            <p class="text-white tracking-light text-xl sm:text-2xl md:text-3xl font-extrabold leading-snug"> <!-- モバイルでのタイトルサイズを調整 -->
                                <?php echo htmlspecialchars($hero_post['title']); ?>
                            </p>
                        </div>
                    </a>
                    <?php else: ?>
                        <div
                            class="bg-cover bg-center flex flex-col justify-end overflow-hidden bg-white @[480px]:rounded-lg min-h-60 sm:min-h-80"
                            style='background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 25%), url("<?php echo $placeholder_thumbnail; ?>");'
                        >
                            <div class="flex p-4 sm:p-6 md:p-8">
                                <p class="text-white tracking-light text-xl sm:text-2xl md:text-3xl font-extrabold leading-snug">新しいブログ投稿を始めましょう！</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                </div>

                <!-- Featured Articles (横スクロール) -->
                <?php if (!empty($featured_posts)): ?>
                <h2 class="text-[#111418] text-xl sm:text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-4 pt-6">Featured Articles</h2>
                <div class="flex overflow-x-auto no-scrollbar">
                    <div class="flex items-stretch p-4 gap-4"> <!-- gap-3をgap-4に広げる -->
                        <?php foreach ($featured_posts as $post): ?>
                        <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="flex h-full flex-1 flex-col gap-4 rounded-lg min-w-[240px] md:min-w-60 shadow-md hover:shadow-lg transition-shadow bg-white p-4">
                            <div
                                class="w-full bg-center bg-no-repeat aspect-video bg-cover rounded-lg flex flex-col"
                                style='background-image: url("<?php echo htmlspecialchars($post['thumbnail'] ?: $placeholder_card_thumbnail); ?>");'
                            ></div>
                            <div>
                                <p class="text-[#111418] text-lg font-semibold leading-normal"><?php echo htmlspecialchars($post['title']); ?></p>
                                <p class="text-[#60758a] text-base text-gray-700 leading-relaxed line-clamp-3"> <!-- line-clamp-3 を追加 -->
                                    <?php echo htmlspecialchars(truncate_text($post['content_markdown'], 80)); ?>
                                </p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php if (count($featured_posts) < 3): ?>
                            <?php for ($i = 0; $i < (3 - count($featured_posts)); $i++): ?>
                                <div class="flex h-full flex-1 flex-col gap-4 rounded-lg min-w-[240px] md:min-w-60 opacity-70 shadow-md bg-white p-4">
                                    <div
                                        class="w-full bg-center bg-no-repeat aspect-video bg-cover rounded-lg flex flex-col"
                                        style='background-image: url("<?php echo $placeholder_card_thumbnail; ?>");'
                                    ></div>
                                    <div>
                                        <p class="text-[#111418] text-lg font-semibold leading-normal">サンプル記事タイトル</p>
                                        <p class="text-[#60758a] text-base text-gray-700 leading-relaxed line-clamp-3"> <!-- line-clamp-3 を追加 -->
                                            サンプル本文の抜粋です。
                                        </p>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Latest News -->
                <?php if (!empty($latest_news_posts)): ?>
                <h2 class="text-[#111418] text-xl sm:text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-4 pt-6">Latest News</h2>
                <?php foreach ($latest_news_posts as $post): ?>
                <a href="post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="p-4 block hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex flex-col sm:flex-row items-stretch justify-between gap-4 py-4 border-b border-gray-200">
                        <div class="flex flex-col gap-1 flex-1 sm:flex-[2_2_0px]">
                            <p class="text-[#60758a] text-xs font-normal leading-normal">Published: <?php echo htmlspecialchars(date('M d, Y', strtotime($post['date']))); ?></p> <!-- 日付をさらに小さく -->
                            <p class="text-[#111418] text-lg font-bold leading-snug"><?php echo htmlspecialchars($post['title']); ?></p>
                            <p class="text-[#60758a] text-base text-gray-700 leading-relaxed line-clamp-3"> <!-- line-clamp-3 を追加 -->
                                <?php echo htmlspecialchars(truncate_text($post['content_markdown'], 100)); ?>
                            </p>
                        </div>
                        <div
                            class="w-full bg-center bg-no-repeat aspect-video bg-cover rounded-lg sm:w-1/3 sm:max-w-[180px] min-w-[120px] order-first sm:order-last"
                            style='background-image: url("<?php echo htmlspecialchars($post['thumbnail'] ?: $placeholder_card_thumbnail); ?>");'
                        ></div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>

            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- 管理ページへの固定ボタン -->
    <a href="admin.php" class="admin-fixed-button" title="管理ページへ">管理<br>ページ</a>
  </body>
</html>