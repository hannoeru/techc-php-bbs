<?php
if (isset($_GET['color'])) {

  preg_match('/#([0-f]{2})([0-f]{2})([0-f]{2})/', $_GET['color'], $matches);

  array_shift($matches);

  $matches = array_map(function($color) {
    return hexdec($color);
  }, $matches);

  $image = imagecreate(500, 500);
  imagecolorallocate($image, ...$matches);

  header('Content-Type: image/png');
  imagepng($image);

  return;
}
?>

色を選んで「決定」を押してね。<br>
<form method="GET">
  <input type="color" name="color" placeholder="#000000">
  <button type="submit">決定</button>
</form>

