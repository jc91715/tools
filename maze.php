<?php

$n=300;
$positions=[];
for ($i=0;$i<=$n;$i++){
    for ($j=0;$j<=$n;$j++){
        $positions[] =[$j,$i] ;
    }
}

//开始的点
$startDot=[0,0];
//已经放置的点
$placedDot=[];
//不可放置的点
$notPlacedDot=notPlacedDot($positions,$n);
$placedDot[]=$startDot;



//所有穿过的点
$lineDot=line($positions,$startDot,$placedDot,$notPlacedDot,$n);

echo getHtml($positions,$lineDot,$n);


function line($positions,$startDot,$placedDot,$notPlacedDot,$n){
    
    $dotWrapper=GetDotWrappers($startDot);
    $key=rand(0,3);
    $nextDot =  $dotWrapper[$key];

    $i=0;
    $notPlacedDotAll =array_merge($notPlacedDot,[]);
    while (in_array($nextDot,$placedDot)||in_array($nextDot,$notPlacedDotAll)){

        $i++;
       if(badDot($nextDot,$placedDot,$notPlacedDot,$n)){//是否是坏点

           $notPlacedDotAll[]=$nextDot;

          $startDot=$placedDot[count($placedDot)-1];
           $dotWrapper=GetDotWrappers($startDot);
           $key=rand(0,3);
           $nextDot =  $dotWrapper[$key];

          if(badDot($startDot,$placedDot,$notPlacedDotAll,$n)){//本身是否是坏点
              $notPlacedDotAll[]= array_pop($placedDot);

              $startDot=$placedDot[count($placedDot)-1];
              $dotWrapper=GetDotWrappers($startDot);
              $key=rand(0,3);
              $nextDot =  $dotWrapper[$key];

          }

        }else{
            $key=rand(0,3);
            $nextDot =  $dotWrapper[$key];

        }
        if($i>4&&count($placedDot)>1){//循环大于3次 还没找到去除该点
            $i=0;
            $notPlacedDot[]= array_pop($placedDot);
            $startDot=$placedDot[count($placedDot)-1];
            $dotWrapper=GetDotWrappers($startDot);
            $key=rand(0,3);
            $nextDot =  $dotWrapper[$key];


        }

    }


    $placedDot[]=$nextDot;

   if($nextDot[0]>=19&&$nextDot[1]>0&&$nextDot[1]<=19){
       return $placedDot;
   }
   return line($positions,$nextDot,$placedDot,$notPlacedDot,$n);
}

function getHtml($positions,$lineDot,$n){
    $str="<table border='1'>";

    foreach (array_chunk($positions,$n+1) as $row){
        $str.="<tr>";
        foreach ($row as $tr){
            if(in_array($tr,$lineDot)){
                $str.="<td style='color: white;background-color: red'>点{$tr[0]},{$tr[1]}</td>";
            }else{
                $str.="<td>{$tr[0]},{$tr[1]}</td>";
            }
        }
        $str.="<tr>";

    };
    $str.="</table>";
    return $str;
}

function notPlacedDot($positions,$n)
{
    
    $notPlacedDot=[];
    foreach ($positions as $position){
        $dotWrapper=GetDotWrappers($position);

        foreach ($dotWrapper as $dot){
            if($dot[0]<0||$dot[1]<0||$dot[0]>$n||$dot[1]>$n){
                $notPlacedDot[]=$dot;
            }
        }
    }
    return $notPlacedDot;
}

/**
 * 是否是坏点
 * @param $dotWrapper
 * @param $placedDot
 * @param $notPlacedDot
 * @return bool
 */
function badDot($position,$placedDot,$notPlacedDot,$n){
    
    $dotWrapper=GetDotWrappers($position);
    $i=0;
    foreach ($dotWrapper as $dot){
        if($dot[0]<0||$dot[1]<0||$dot[1]>$n||in_array($dot,$placedDot)||in_array($dot,$notPlacedDot)){
            $i++;
        }
    }
    if($i==4){
        return true;
    }
    return false;

}


function GetDotWrappers($placementEd){
    $top = calculateXY($placementEd[0],$placementEd[1],'top');
    $bottom = calculateXY($placementEd[0],$placementEd[1],'bottom');
    $left = calculateXY($placementEd[0],$placementEd[1],'left');
    $right = calculateXY($placementEd[0],$placementEd[1],'right');
    return [$top,$bottom,$left,$right];
}

function calculateXY($x,$y,$default='top')
{
    switch ($default){
        case 'top':
            $y=$y+1;
            break;
        case 'bottom':
            $y=$y-1;
            break;
        case 'left':
            $x=$x-1;
            break;
        case 'right':
            $x=($x+1);
            break;
        case 'left_top':
            $x=($x-1);
            $y=($y+1);
            break;
        case 'right_top':
            $x=($x+1);
            $y=($y+1);
            break;
        case 'right_bottom':
            $x=($x+1);
            $y=($y-1);
            break;
        case 'left_bottom':
            $x=($x-1);
            $y=($y-1);
            break;
    }

    return [$x,$y];
}


