<?php

$n=20;
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
list($lineDot,$finishDot)=line($positions,$startDot,$placedDot,$notPlacedDot,$n);

//平面上的所有迷宫点
$mazePositions=mazePosition($positions,$lineDot);


//查找最小的点
$line=[];
$canReOpen=[];//库存
$startDot=[0,0];//开始的点
$line[]=[0,0];
$shortLine=getShortLine($line,$canReOpen,$startDot,$mazePositions,$notPlacedDot,$finishDot,$n);
echo "解迷宫";
echo getHtml($positions,$lineDot,$shortLine,$n);
echo "<br>";
echo "迷宫路线";
echo getAllHtml($positions,$lineDot,$n);
//echo getHtml($positions,$lineDot,$n);


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

   if($nextDot[0]>=$n&&$nextDot[1]>0&&$nextDot[1]<=$n){
       return [$placedDot,$nextDot];
   }
   return line($positions,$nextDot,$placedDot,$notPlacedDot,$n);
}

function mazePosition($positions,$lineDot){
    $mazePositions=[];
    foreach ($positions as $position){

            if(in_array($position,$lineDot)){
                $mazePositions[]=$position;
            }
    }
    return $mazePositions;
}

function getShortLine($line,$canReOpen,$startDot,$mazePositions,$notPlacedDot,$endDot,$n){

    if($startDot==$endDot){
//        echo(json_encode($line));
        return $line;
    }

    $dotWrapper=GetDotWrappers($startDot);

    $allOpen = array_reduce($canReOpen, 'array_merge', array());

    $arr=[];

    foreach ($dotWrapper as $dot){

        if(in_array($dot,$mazePositions)&&!in_array($dot,$line)&&!in_array($dot,$allOpen)&&!in_array($dot,$notPlacedDot)){

            $arr[distance($dot,$endDot)][]=$dot;
        }


    }

    if(empty($arr)){//死点
        $notPlacedDot[]=$startDot;//这个点不能放
        array_pop($line);//去除这个点

        $length=count($line);
        $dot=$line[$length-1];//去除上面这个点后的最后一个点
        $dotWrapper=GetDotWrappers($dot);//这个点周围的点

        if(!empty($canReOpen)){//库存中是否有点
            $reOpen = array_pop($canReOpen);//取出库存中最后一个库存
            $ifIntersect=false;
            foreach ($reOpen as $open){
                if(in_array($open,$dotWrapper)){//有交集，说明最后一个库存可用
                    $ifIntersect=true;
                    break;
                }
            }
            if($ifIntersect){//可用的库存
                $nextStartDot=array_shift($reOpen);//库存中的第一个点默认是最短距离的
                array_push($line,$nextStartDot);
                if(!empty($reOpen)){//没用完，再放进去
                    array_push($canReOpen,$reOpen);
                }
            }else{//不可用。把库存再放进去
                array_push($canReOpen,$reOpen);
                $nextStartDot = $dot;
            }
        }else{
            $nextStartDot = $dot;
        }
    }else{
        ksort($arr);//按距离大小升序排序

        $arr = array_reduce($arr, 'array_merge', array());
//        echo json_encode($arr);
//        echo "<br>";
        $nextStartDot=array_shift($arr);//取出最小距离的作为开始点

        array_push($line,$nextStartDot);
        if(!empty($arr)){//还有可用的点，放进库存
            $canReOpen[] = array_values($arr);
        }
    }



    return getShortLine($line,$canReOpen,$nextStartDot,$mazePositions,$notPlacedDot,$endDot,$n);

}


function getAllHtml($positions,$lineDot,$n){
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
function getHtml($positions,$lineDot,$shortLine,$n){
    $str="<table border='1'>";

    foreach (array_chunk($positions,$n+1) as $row){
        $str.="<tr>";
        foreach ($row as $tr){
            if(in_array($tr,$lineDot)){
                if(in_array($tr,$shortLine)){
                    $str.="<td style='color: white;background-color: blue'>点{$tr[0]},{$tr[1]}</td>";
                }else{
                    $str.="<td style='color: white;background-color: red'>点{$tr[0]},{$tr[1]}</td>";
                }

            }else{
                $str.="<td>{$tr[0]},{$tr[1]}</td>";
            }
        }
        $str.="<tr>";

    };
    $str.="</table>";
    return $str;
}

function distance($start,$end){
    return abs($end[0]-$start[0])+abs($end[1]-$start[1]);
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


