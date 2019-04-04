<?php


if(!$argv[1]){
    exit('缺少图片文件路径');
}
if(!file_exists($argv[1])){
    exit($argv[1].'文件不存在');
}
$src = $argv[1];
$base64_img = base64EncodeImage($src);
echo $base64_img;

function base64EncodeImage ($image_file)
{
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;

}
