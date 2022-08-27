<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (!isset($_GET['edit_entry_id'])) {
  // 編集対象を取得するのに必要なクエリパラメータがなければエラー
  header("HTTP/1.1 404 Not Found");
  print('error!');
  return;
}

// クエリパラメータの edit_entry_id から編集対象を取得
$select_edit_entry_sth = $dbh->prepare('SELECT * FROM bbs_entries WHERE id = :id');
$select_edit_entry_sth->execute([
  ':id' => intval($_GET['edit_entry_id']),
]);
$edit_entry = $select_edit_entry_sth->fetch();

if (empty($edit_entry)) {
  // 編集対象がなければエラー
  header("HTTP/1.1 404 Not Found");
  print('error!');
  return;
}

if (isset($_POST['body'])) {
  // POSTで送られてくるフォームパラメータ body がある場合

  // 編集対象の行をupdateする
  $insert_sth = $dbh->prepare("UPDATE bbs_entries SET body = :body WHERE id = :id");
  $insert_sth->execute([
      ':body' => $_POST['body'],
      ':id' => $edit_entry['id'],
  ]);

  // 処理が終わったら投稿一覧にリダイレクトする
  header("HTTP/1.1 302 Found");
  header("Location: ./enshu2.php");
  return;
}
?>

ID: <?= $edit_entry['id'] ?> (投稿日時 <?= $edit_entry['created_at'] ?>) の投稿を編集します。
<form method="POST" action="./enshu2_editform.php?edit_entry_id=<?= $edit_entry['id'] ?>">
  <textarea name="body"><?= htmlspecialchars($edit_entry['body']) ?></textarea>
  <button type="submit">送信</button>
</form>

