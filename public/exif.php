<img width="300" src="/images/oshiro.jpeg">
<br>

<?php
  
// Open a the file from local folder
$fp = fopen('./images/oshiro.jpeg', 'rb');
  
// Read the exif headers
$headers = exif_read_data($fp);
  
// Print the headers
echo 'EXIF Headers:' . '<br>';
  
print("<pre>".print_r($headers, true)."</pre>");
