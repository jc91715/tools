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


$line=[];//最终结果
$canReOpen=[];//库存
$startDot=[0,0];//开始的点
$line[]=[0,0];
$count=1;//默认值达到 $stepCount重置
$nextStep=[];//保存改点走$stepCount步有多少种可能
$extraNotPlaceDot=[];//不可放置的点
$stepCount=6;//每次前进几步进行比较
$shortLine=getShortLine($line,$canReOpen,$startDot,$mazePositions,$notPlacedDot,$finishDot,$n,$count,$nextStep,$extraNotPlaceDot,$stepCount);

echo "解迷宫";
echo getHtml($positions,$lineDot,$shortLine,$n);
echo "<br>";
echo "迷宫路线";
echo getAllHtml($positions,$lineDot,$n);
//echo getHtml($positions,$lineDot,$n);


/**
 * 生成迷宫路线
 * @param $positions
 * @param $startDot
 * @param $placedDot
 * @param $notPlacedDot
 * @param $n
 * @return array
 */
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

/**
 * 所有迷宫的点
 * @param $positions
 * @param $lineDot
 * @return array
 */
function mazePosition($positions,$lineDot){

    $mazePositions=[];
    foreach ($positions as $position){

            if(in_array($position,$lineDot)){
                $mazePositions[]=$position;
            }
    }
    return $mazePositions;
}

/**
 *  计算最短路线
 *
 * 迷宫平面坐标系
 * @param $line
 * 待用的点
 * @param $canReOpen
 * 开始的点
 * @param $startDot
 * 所有迷宫的可走的点
 * @param $mazePositions
 * 不能放置的点比如平面坐标系外的点
 * @param $notPlacedDot
 * 迷宫终点
 * @param $endDot
 * 没用到坐标系的宽
 * @param $n
 * 初始值默认1 最终会达到 $stepCount后会重置
 * @param $count
 * 存放一个点的走 $stepCount 步所有可能性类似于这样的数组 json_decode({"0":[1,0],"10":[[1,1]],"11":[[1,2],[2,1]],"12":[[2,2],[0,2]],"22":[[3,2],[3,2]],"02":[[0,3]],"21":[[2,2],[3,1]],"31":[[3,2],[3,0]]}',true)
 * @param $nextStep
 * 走的过程中不能放置的点
 * @param $extraNotPlaceDot
 * @param $stepCount
 * @return array
 */
function getShortLine($line,$canReOpen,$startDot,$mazePositions,$notPlacedDot,$endDot,$n,$count,$nextStep,$extraNotPlaceDot,$stepCount){

    $dotWrapper=GetDotWrappers($startDot);  //一个点的上下左右点
    $allOpen = array_reduce($canReOpen, 'array_merge', array()); //待用点
    $arr=[];//周围符合条件的点
    $notPlacedDotAll =array_merge($notPlacedDot,$extraNotPlaceDot);
    foreach ($dotWrapper as $dot){
        if(in_array($dot,$mazePositions)&&!in_array($dot,$line)&&!in_array($dot,$allOpen)&&!in_array($dot,$notPlacedDotAll)){
            $arr[distance($dot,$endDot)][]=$dot;
        }
    }

    if($count==1){//首次进入父节点

        if($startDot==$endDot){
            return $line;
        }
        if(empty($arr)){//死点
            array_pop($line);
            $extraNotPlaceDot[]=$startDot;
            $nextStartDot = $line[count($line)-1];

        }else{

            ksort($arr);//按距离大小升序排序
            $arr = array_reduce($arr, 'array_merge', array());
            $canReOpen=[];
            if(!empty($arr)){
                $canReOpen[] = array_values($arr);
            }
            $nextSteps=[];//父节点走$stepCount步，周围所有可能走的点，是一个二维数组 类似于这样json_decode([{"0":[1,0],"10":[[1,1]],"11":[[1,2],[2,1]],"12":[[2,2],[0,2]],"22":[[3,2],[3,2]],"02":[[0,3]],"21":[[2,2],[3,1]],"31":[[3,2],[3,0]]}]',true)
            $count++;
            do{//求周围点的可能
                $nextStep=[];
                $nextStartDot=array_shift($arr);//依次取出最小距离的作为开始点
                if($nextStartDot==$endDot){
                    array_push($line,$nextStartDot);
                    return $line;
                }
                $nextStep[0]=$nextStartDot;//父顶点
                //调用 getShortLine 进入到if($count==1){｝else{} 的else 代码块。求走$stepCount的所有可能的点
                $nextSteps[] = getShortLine($line,$canReOpen,$nextStartDot,$mazePositions,$notPlacedDot,$endDot,$n,$count,$nextStep,$extraNotPlaceDot,$stepCount);

            }while(!empty($arr));

            $result=[];
            foreach ($nextSteps as $step){
                if(isset($step[0])){
                    $ar=[];
                    $item=$step[0];
                    unset($step[0]);
                    $parent=[];
                    $result=linkTable($result,$step,$item,$item,$ar,$parent);//将结果转化为链表的结构
                }
            }
            $nextSteps=$result;
            if(empty($nextSteps)){
                array_pop($line);
                $extraNotPlaceDot[]=$startDot;
                $nextStartDot = $line[count($line)-1];
            }else{
                $full=[];
                $notFull=[];
                foreach ($nextSteps as $k1=>$temp){
                    if(count($temp)==$stepCount){//走完$stepCount的点
                        $full[$k1]=$temp;
                    }else{//没走完$stepCount的点
                        $notFull[$k1]=$temp;
                    }
                }
                if(!empty($full)){//优先使用走完$stepCount步的点
                    $temps=[];
                    foreach ($full as $key=>$tmp){
                        $end=$tmp[count($tmp)-1];
                        $distance=distance($end,$endDot);
                        $temps[$distance]=$tmp;
                    }
                }else{
                    $temps=[];
                    foreach ($notFull as $key=>$tmp){
                        $end=$tmp[count($tmp)-1];
                        $distance=distance($end,$endDot);
                        $temps[$distance]=$tmp;
                    }
                }

                ksort($temps);//按距离大小升序排序
                $steps=array_shift($temps);//取出最近的一个组点
                $nextStartDot=$steps[count($steps)-1];//最后一个点作为开始点
                $line=array_merge($line,$steps);
                if($nextStartDot==$endDot){
                    return $line;
                }
            }

        }
        //继续下一个点重复以上步骤
        $count=1;
        return getShortLine($line,$canReOpen,$nextStartDot,$mazePositions,$notPlacedDot,$endDot,$n,$count,$nextStep,$extraNotPlaceDot,$stepCount);
    }else{//求每一个父顶点下的所有可能点

        if($startDot==$endDot){
            return $nextStep;
        }
        if($count<=$stepCount){
            if(empty($arr)){//死点
                $extraNotPlaceDot[]=$startDot;//这个点不能放
                foreach ($nextStep as $key=>&$next){//把这个点移出去， $nextStep 属于结构类似与这样 json_decode({"0":[1,0],"10":[[1,1]],"11":[[1,2],[2,1]],"12":[[2,2],[0,2]],"22":[[3,2],[3,2]],"02":[[0,3]],"21":[[2,2],[3,1]],"31":[[3,2],[3,0]]}',true)
                    if($startDot==$next){
                        unset($nextStep[$key]);
                        continue;
                    }
                    if(in_array($startDot,$next)){
                        unset($next[array_search($startDot,$next)]);

                        if(empty($next)){
                            unset($nextStep[$key]);
                        }else{
                            $next=array_values($next);
                        }
                    }
                }
                return $nextStep;
            }else{
                ksort($arr);//按距离大小升序排序
                $arr = array_reduce($arr, 'array_merge', array());

                $canReOpen[] = array_values($arr);

                do{//继续改点的分裂直到达到$stepCount 一个点可能分裂为1个两个或三个
                    $count_tmp=$count;
                    $nextStartDot=array_shift($arr);
                    $nextStep[$startDot[0].$startDot[1]][]=$nextStartDot;

                    $count_tmp++;
                    $nextStep=getShortLine($line,$canReOpen,$nextStartDot,$mazePositions,$notPlacedDot,$endDot,$n,$count_tmp,$nextStep,$extraNotPlaceDot,$stepCount);

                }while(!empty($arr));

                return $nextStep=array_map(function ($val){
                    if(isset($val[0])&&!is_array($val[0])){//父节点第一个点
                        return $val;
                    }
                    return array_values(array_unique($val,SORT_REGULAR));
                },$nextStep);

            }
        }else{

            if(empty($arr)){//死点
                $extraNotPlaceDot[]=$startDot;//这个点不能放
            }
            return $nextStep;
        }
    }
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

function linkTable($result,$steps,$item,$start,$ar,$parent){

    if(isset($steps[$item[0].$item[1]])&&!empty($steps[$item[0].$item[1]])){
        $items=$steps[$item[0].$item[1]];
        $length=count($items);
        for ($i=0;$i<$length;$i++){
            if(!in_array($items[$i],$ar)){//避免形成一个环
                $parent[]=$items[$i];
                $ar[]=$items[$i];
                $item=$items[$i];
                $result=linkTable($result,$steps,$item,$start,$ar,$parent);
                array_pop($parent);
                $ar=$parent;
            }
        }
        return array_values(array_unique($result,SORT_REGULAR ));
    }else{
        array_unshift($ar,$start);
        $result[]=$ar;
        return $result;
    }
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


