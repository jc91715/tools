<?php


$font_width = 16; // 单字宽度
$font_height = 16; // 单字高度
$byteCount = $font_width * $font_height / 8;//一个点阵占的字节数


$str=iconv("utf-8","gb2312//IGNORE", "我是大是的大萨法fasf大多数");
$n=5;//每一行四个字

//所有字的字模
$dot = getDot($str,$byteCount);
/**
 * $positions 平面坐标系
 * $sections 每一个字模在平面坐标系中的点
 * $spreadSections 所有字模在平面坐标系中的点，是$sections的展开
 *
 */
list($positions,$sections,$spreadSections) = getPositionsSections($dot,$byteCount,$n);

/**
 * 输出点阵html字符串
 */
echo getOutHtml($positions,$sections,$spreadSections,$n);




/**
 * 从字库中获得每一个字的字模
 * @param $str
 * @param $byteCount
 * @return string
 */
function getDot($str,$byteCount){
    $dot='';
    $fontFileName = './HZK16';//字库名字
    $fp = fopen($fontFileName, "rb");
    for ($i=0;$i<strlen($str);$i++){

        if(ord($str[$i])<160){//非汉字
            $location=(ord($str{$i}) + 156-1) * $byteCount;
        }else {//汉字
            $qh = ord($str[$i]) - 32 - 128;//区码
            $wh = ord($str[++$i]) - 32 - 128;//位码
            $location = (94 * ($qh - 1) + ($wh - 1)) * $byteCount; /* 计算汉字字模在文件中的位置 */
        }
        fseek($fp, $location, SEEK_SET);//定位到汉字或字母指针开始的地方
        $dot.= fread($fp, $byteCount);//读取32字节的长度，一个字节8位，一行依次放两个字节组成16*16的点阵
    }
    fclose($fp);
    return $dot;
}


/**
 * 建平面按坐标系。并把每一区块用平面坐标系表示
 * @param $dot
 * @param $byteCount
 * @param $n
 * @return array
 */
function getPositionsSections($dot,$byteCount,$n){

    $count= strlen($dot)/$byteCount;//多少个字
    $positions=[];
    $sections =[];
    $sectionCount=$count;

    for ($i=0;$i<$sectionCount;$i++){
        $sections[]=[];

    }

    $yHeight=(intval($count/$n)*16+16);
    $xWeight=16*$n;
    for ($i=0;$i<$yHeight;$i++){
        for ($j=0;$j<$xWeight;$j++){
            $positions []=[$j,$i];
            $x=ceil(($j+1)/16);
            $y=ceil(($i+1)/16);
            $y--;
            $x--;
            $sections[(($y)*$n+$x)][] = [$j,$i];

        }
    }

    for ($b=0;$b<$count;$b++){//每一个字占用的点阵
        $str = substr($dot,($b)*32,$byteCount);//第几个字
        $dot_string='';
        for ($c = 0; $c < $byteCount; $c++){
            $dot_string .= sprintf("%08b", ord($str[$c]));
            if ($c % 2 == 1) {

                for($a=0;$a<strlen($dot_string);$a++){
                    if($dot_string[$a]){//和平面坐标系关联起来
                        $sections[$b][intval(16*floor($c/2)+$a)][]=1;
                    }
                }
                $dot_string = '';
            }
        }
    }
    $spreadSections=[];//每一个字块的的点展开到数组中
    foreach ($sections as $section){
        $spreadSections  = array_merge($spreadSections,$section);
    }

    return [$positions,$sections,$spreadSections,$count,$sectionCount];


}


function getOutHtml($positions,$sections,$spreadSections,$n){
    $str="<html><body><table border='1' width='100%' style='text-align: center'>";
    foreach (array_chunk($positions,16*$n) as $row){

        $str.=getOutRow($row,$sections,$spreadSections);
    }
    $str .= "</table></body>
</html>";
    return $str;
}

function getOutRow($row,$sections,$spreadSections){

    $str="<tr>";
    foreach ($row as $td) {
        if (!in_array($td,$spreadSections)){//不在平面坐标系中说明这个位置是一个点
            $str .= "<td style='color: white;background-color: red;'>O</td>";
        }else {
            $str .= "<td>O</td>";
        }
    }
    $str.="<tr>";
    return $str;
}

