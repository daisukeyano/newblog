# PHPシンプルブログ

シンプルで使いやすいPHPベースのブログシステムです。Markdownサポート、画像アップロード、HTML埋め込み機能を備えています。

## 特徴

- **Markdownサポート**: 記事をMarkdown形式で作成・編集
- **画像アップロード**: サムネイル画像のアップロードまたはURL指定
- **HTML埋め込み**: Twitter、Instagramなどの埋め込みコンテンツ対応
- **レスポンシブデザイン**: Tailwind CSSを使用したモダンなデザイン
- **セキュリティ**: CSRF保護、ファイルアップロード制限
- **JSONベース**: データベース不要、JSONファイルでデータ管理

## 必要な環境

- PHP 7.4以上
- Webサーバー（Apache、Nginx等）
- 書き込み権限（dataディレクトリ用）

## インストール

1. **ファイルのダウンロード**
   ```bash
   git clone [リポジトリURL]
   cd simple_blog
   ```

2. **Parsedownライブラリの設置**
   
   [Parsedown.php](https://github.com/erusev/parsedown/releases)をダウンロードし、プロジェクトルートに配置してください。
   ```
   simple_blog/
   ├── Parsedown.php  ← ここに配置
   ├── index.php
   ├── functions.php
   └── ...
   ```

3. **ディレクトリ権限の設定**
   ```bash
   mkdir -p data/uploads
   chmod 755 data
   chmod 755 data/uploads
   ```

4. **Webサーバーの設定**
   
   プロジェクトルートをWebサーバーのドキュメントルートに設定するか、適切なディレクトリに配置してください。

## ファイル構成

```
simple_blog/
├── index.php              # トップページ
├── post.php              # 記事詳細ページ
├── admin.php             # 新規投稿作成ページ
├── edit_post.php         # 投稿編集ページ
├── create_post.php       # 投稿作成処理
├── update_post.php       # 投稿更新処理
├── delete_post.php       # 投稿削除処理
├── functions.php         # 共通関数
├── header.php            # 共通ヘッダー
├── style.css             # カスタムスタイル
├── Parsedown.php         # Markdownパーサー（要ダウンロード）
└── data/
    ├── posts.json        # 投稿データ（自動作成）
    └── uploads/          # アップロード画像（自動作成）
```

## 使用方法

### 1. 基本的な操作

1. **トップページ**: `index.php` にアクセス
2. **新規投稿**: 「管理ページ」ボタンから投稿作成
3. **記事編集**: 記事詳細ページの「この記事を編集」ボタン

### 2. 投稿の作成

1. 管理ページ（`admin.php`）にアクセス
2. 以下の項目を入力：
   - **タイトル**: 記事のタイトル
   - **本文**: Markdown形式で記事内容を記述
   - **サムネイル**: 画像ファイルアップロードまたはURL指定
   - **HTML埋め込み**: Twitter、Instagram等の埋め込みコード

3. 「投稿する」ボタンで記事を作成

### 3. Markdown記法の例

```markdown
# 見出し1
## 見出し2

**太字** *斜体*

- リスト項目1
- リスト項目2

1. 番号付きリスト
2. 項目2

[リンクテキスト](https://example.com)

> 引用文

`コード`

```
コードブロック
```
```

### 4. HTML埋め込み機能

Twitter、Instagram等の埋め込みコードを貼り付けることができます：

- **Twitter**: ツイートの埋め込みコードをコピー
- **Instagram**: 投稿の埋め込みコードをコピー 
- **注意**: 末尾の`<script>`タグは削除してください（自動で追加されます）

## 設定とカスタマイズ

### 1. サイト情報の変更

`header.php`でサイト名を変更：
```php
<h2 class="..."><a href="index.php">AI News</a></h2>  <!-- ここを変更 -->
```

### 2. アップロード制限の変更

`functions.php`の`handle_thumbnail_upload`関数内：
```php
$max_size = 2 * 1024 * 1024; // 2MB → 任意のサイズに変更
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif']; // 許可する拡張子
```

### 3. デザインのカスタマイズ

- `style.css`: 追加のカスタムスタイル
- Tailwind CSSクラスを直接編集してデザイン変更

### 4. レイアウト設定

`index.php`で表示記事数を調整：
```php
// Featured Articlesに表示する記事数（現在は2-4番目の投稿）
for ($i = 1; $i <= 3; $i++) {  // 3を変更

// Latest Newsに表示する記事（現在は5番目以降）
$latest_news_posts = array_slice($posts, 4);  // 4を変更
```

## セキュリティ

### 実装済みのセキュリティ対策

- **CSRF保護**: フォーム送信時のトークン検証
- **ファイルアップロード制限**: 
  - ファイル形式の検証（MIME type + 拡張子）
  - ファイルサイズ制限（デフォルト2MB）
  - 安全なファイル名の生成
- **入力サニタイゼーション**: XSS対策
- **Base64エンコーディング**: HTML埋め込みコードの安全な保存

### 追加の推奨事項

1. **認証システムの実装** (本システムには含まれていません)
2. **HTTPSの使用**
3. **定期的なバックアップ**
4. **Webサーバーのセキュリティ設定**

## トラブルシューティング

### よくある問題

1. **「投稿の保存に失敗しました」エラー**
   - `data`ディレクトリの書き込み権限を確認
   - `chmod 755 data`を実行

2. **画像アップロードエラー**
   - `data/uploads`ディレクトリの存在と権限を確認
   - PHP設定（`upload_max_filesize`、`post_max_size`）を確認

3. **Markdownが表示されない**
   - `Parsedown.php`ファイルの存在を確認
   - ファイルパスが正しいか確認

4. **埋め込みコンテンツが表示されない**
   - scriptタグが削除されているか確認
   - ブラウザのコンソールでエラーを確認

### ログとデバッグ

エラーログは以下で確認：
```php
// functions.php内でerror_log()を使用
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

## データのバックアップ

重要なデータは以下の場所にあります：
- `data/posts.json`: 全記事データ
- `data/uploads/`: アップロード画像

定期的にこれらのファイル・ディレクトリをバックアップしてください。

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。

## 依存関係

- [Parsedown](https://github.com/erusev/parsedown): Markdownパーサー
- [Tailwind CSS](https://tailwindcss.com/): CSSフレームワーク（CDN経由）

## 貢献

バグ報告や機能提案は、GitHubのIssuesでお知らせください。

## 更新履歴

- **v1.0.0**: 初回リリース
  - 基本的なブログ機能
  - Markdownサポート
  - 画像アップロード機能
  - HTML埋め込み機能
  - レスポンシブデザイン
