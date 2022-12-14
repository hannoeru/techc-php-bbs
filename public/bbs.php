<?php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

if (isset($_POST['body'])) {
  // POSTで送られてくるフォームパラメータ body がある場合

  $image_filename = null;
  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
    // アップロードされた画像がある場合
    if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
      // アップロードされたものが画像ではなかった場合
      header("HTTP/1.1 302 Found");
      header("Location: ./bbs.php");
    }

    // 元のファイル名から拡張子を取得
    $pathinfo = pathinfo($_FILES['image']['name']);
    $extension = $pathinfo['extension'];
    // 新しいファイル名を決める。他の投稿の画像ファイルと重複しないように時間+乱数で決める。
    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
    $filepath =  '/var/www/upload/image/' . $image_filename;
    move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }

  // insertする
  $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (body, image_filename) VALUES (:body, :image_filename)");
  $insert_sth->execute([
    ':body' => $_POST['body'],
    ':image_filename' => $image_filename,
  ]);

  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}

// いままで保存してきたものを取得
$select_sth = $dbh->prepare('SELECT * FROM bbs_entries ORDER BY created_at DESC');
$select_sth->execute();
?>

<!-- フォームのPOST先はこのファイル自身にする -->
<form id="form">
  <textarea name="body" required></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <button id="btn" type="submit">送信</button>
</form>

<hr>

<?php foreach ($select_sth as $entry) : ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;" id="<?= $entry['id'] ?>">
    <dt>ID</dt>
    <dd><?= $entry['id'] ?></dd>
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <?php
    $body = nl2br(htmlspecialchars($entry['body']));
    $urlPattern = htmlspecialchars('/>>([1-9][0-9]?+)/');
    $replace = '<a href="#\\1">>>\\1</a>';
    $body = preg_replace($urlPattern, $replace, $body);
    ?>
    <dd>
      <?= $body // 必ず htmlspecialchars() すること 
      ?>
      <?php if (!empty($entry['image_filename'])) : // 画像がある場合は img 要素を使って表示 
      ?>
        <div>
          <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
        </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>

<script type="module">
  import axios from 'https://esm.sh/axios'
  import imageCompression from 'https://esm.sh/browser-image-compression@2.0.0';

  const form = document.getElementById("form");
  const btn = document.getElementById("btn");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    let body = e.target[0].value;
    let image = e.target[1].files[0];

    if (image && image.size > 5 * 1024 * 1024) {
      btn.innerHTML = "圧縮中...";
      // ファイルが5MBより多い場合
      image = await compressImage(image);
      btn.innerHTML = "送信";
    }

    const formData = new FormData();
    body = body.replace('<br>', '\n')
    formData.append("body", body);

    if (image) {
      formData.append("image", image);
    }

    const res = await axios.post("./bbs.php", formData, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    });
    console.log(res);

    window.location.reload();
  });

  async function compressImage(imageFile) {
    console.log(`originalFile size ${imageFile.size / 1024 / 1024} MB`);
    try {
      const compressedFile = await imageCompression(imageFile, {
        maxSizeMB: 4,
        useWebWorker: true
      });
      console.log(`compressedFile size ${compressedFile.size / 1024 / 1024} MB`);
      return compressedFile;
    } catch (error) {
      console.log(error);
      alert("イメージリサイズに失敗しました");
      return null;
    }
  }
</script>
