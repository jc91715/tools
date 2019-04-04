<?php
$arr = [
    [
        'id'=>1,
        'score'=>10,
    ],
    [
        'id'=>2,
        'score'=>30,
    ],
    [
        'id'=>3,
        'score'=>50,
    ],
    [
        'id'=>4,
        'score'=>50,
    ]
];
array_multisort(array_column($arr,'score'),SORT_ASC,$arr);
$results = array_slice($arr, 0, 3, true);
$length = count($arr);
$resultLength = count($results);
while($resultLength<$length &&$results[$resultLength-1]['score']==$arr[$resultLength]['score']){
    array_push($results,$arr[$resultLength]);
    $resultLength = count($results);
}
$id =1;
$if_winner = 0;
if(in_array($id,array_column($results,'id'))){
    $if_winner = 1;
}
var_dump($results);
