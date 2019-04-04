<?php

if(!$argv[1]){
    exit('缺少sql文件路径');
}

if(!file_exists($argv[1])){
    exit($argv[1].'文件不存在');
}

$content=file_get_contents($argv[1]);
preg_match_all('/CREATE TABLE([\s\S]*?);/',$content,$matches);
$table=[];
$tableDetail=[];

foreach ($matches[1] as $item){
    //匹配表名字及字段
    preg_match_all('/.*?`(.*?)`.*/',$item,$matches1);
    $tableName=$matches1[1][0];
    $table[]=$tableName;
    array_pop($matches1[1]);
    array_shift($matches1[1]);
    array_pop($matches1[0]);
    array_shift($matches1[0]);
    foreach ($matches1[1] as $key=>$file){
        $tableDetail[$tableName][$file]=[];
        if(strpos($matches1[0][$key],'COMMENT')===false){
            //匹配字段类型无描述
            preg_match('/`\s(.*?),/',$matches1[0][$key],$matches3);
            $tableDetail[$tableName][$file]['type']=$matches3[1];
            $tableDetail[$tableName][$file]['comment']='';
        }else{
            //匹配字段类型有描述
            preg_match('/`\s(.*?)\sCOMMENT\s\'(.*?)\',/',$matches1[0][$key],$matches2);
            $tableDetail[$tableName][$file]['type']=$matches2[1];
            $tableDetail[$tableName][$file]['comment']=$matches2[2];
        }
    }
}
$mdContent = '';
foreach ($table as $tableName){
    //导航
    $mdContent .= '* ['.$tableName.']'.'(#'.$tableName.')'."\n";
}
foreach ($tableDetail as $tableName=>$details){//详情
    $mdContent .= '## '.$tableName."\n";
    $mdContent .= "|字段名称|字段类型|字段含义|\n|:---:|:---:|:---:|\n";
    foreach ($details as $field=>$detail){
        $mdContent .='|'.$field.'|'.$detail['type'].'|'.$detail['comment']."|\n";
    }
}
file_put_contents(__DIR__.'/MD.md',$mdContent);
