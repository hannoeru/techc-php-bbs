<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (isset($_POST['body'])) {
  // POSTで送られてくるフォームパラメータ body がある場合

  // insertする
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body) VALUES (:body)");
  $insert_sth->execute([
      ':body' => $_POST['body'],
  ]);

  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./enshu1.php");
  return;
}

$select_sth = null;
if (isset($_GET['search'])) {
    // 絞り込み
    $select_sth = $dbh->prepare('SELECT * FROM bbs_entries WHERE body LIKE :search ORDER BY created_at DESC');
    $select_sth->execute([
        'search' => '%' . $_GET['search'] . '%',
    ]);
} else {
    // 全件取得
    $select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
    $select_sth->execute();
}
?>

<!-- フォームのPOST先はこのファイル自身にする -->
<form method="POST" action="./enshu1.php">
  <textarea name="body"></textarea>
  <button type="submit">送信</button>
</form>

<hr>

<form method="GET" action="./enshu1.php">
  <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
  <button type="submit">検索</button>
  <?php if(!empty($_GET['search'])): ?>
  <a href="?search=">絞り込み解除</a>
  <?php endif; ?>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <dd><?= nl2br(htmlspecialchars($entry['body'])) // 必ず htmlspecialchars() すること ?></dd>
  </dl>
<?php endforeach ?>

