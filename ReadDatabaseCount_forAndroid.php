<?php

$arr = read_ini_file('config.cfg');
$count = $arr['ARGUMENT']['COUNT'];
echo $count;

//讀取ini檔
	function read_ini_file($path)
	{
		$str = parse_ini_file($path, true);
		return $str;
	}
	
?>
