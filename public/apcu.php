<?php

if (!apcu_exists('count')) {
    $count = 1;
    apcu_store('count', $count, 0);
}

$count = apcu_fetch('count');

echo 'あなたは ' . $count . ' 人目の訪問者です!';

$count += 1;

apcu_store('count', $count, 0);

