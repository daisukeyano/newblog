<?php
// simple_blog/header.php

// $page_title は各ページで設定されることを想定
if (!isset($page_title)) {
    $page_title = "PHPシンプルブログ"; // デフォルトタイトル
}
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- カスタムスタイルシート (Tailwind CSS の後に読み込む) -->
    <link rel="stylesheet" href="style.css">
    
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link
      rel="stylesheet"
      as="style"
      onload="this.rel='stylesheet'"
      href="https://fonts.googleapis.com/css2?display=swap&family=Newsreader%3Awght%40400%3B500%3B700%3B800&family=Noto+Sans%3Awght%40400%3B500%3B700%3B900"
    />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64," />
</head>
<body class="relative flex size-full min-h-screen flex-col bg-white group/design-root overflow-x-hidden" style='font-family: Newsreader, "Noto Sans", sans-serif;'>
    <div class="layout-container flex h-full grow flex-col">
        <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#f0f2f5] px-4 sm:px-6 md:px-10 py-3">
            <div class="flex items-center gap-4 text-[#111418]">
                <div class="size-4">
                    <svg viewBox="0 0 48 48" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M6 6H42L36 24L42 42H6L12 24L6 6Z"></path></svg>
                </div>
                <h2 class="text-[#111418] text-lg font-bold leading-tight tracking-[-0.015em]"><a href="index.php">AI News</a></h2>
            </div>
            <div class="flex flex-1 justify-end gap-2 sm:gap-4 md:gap-8">
                <a href="admin.php"
                    class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-3 sm:px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em]"
                >
                    <span class="truncate">管理ページ</span>
                </a>
            </div>
        </header>
        <!-- ヘッダーの終わり (メインコンテンツは各PHPファイルで開始) -->