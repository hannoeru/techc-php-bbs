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
  header("Location: ./enshu2.php");
  return;
}

// 全件取得
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>

<!-- フォームのPOST先はこのファイル自身にする -->
<form method="POST" action="./enshu2.php">
  <textarea name="body"></textarea>
  <button type="submit">送信</button>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
  <div style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dl>
      <dt>日時</dt>
      <dd><?= $entry['created_at'] ?></dd>
      <dt>内容</dt>
      <dd><?= nl2br(htmlspecialchars($entry['body'])) // 必ず htmlspecialchars() すること ?></dd>
    </dl>
    <div>
      <!--
        編集フォームへのリンク
        URLクエリパメータ edit_entry_id として投稿テーブル(bbs_entriesテーブル)の主キー(idカラム)を編集フォームに渡す。
        編集フォームでは主キーを元に編集対象の投稿を取得したり更新したりする。
      -->
      <a href="./enshu2_editform.php?edit_entry_id=<?= $entry['id'] ?>">編集する</a>
    </div>
  </div>
<?php endforeach ?>

