<?php
	error_reporting(0);    //隱藏報錯
	header("Content-Type:text/html; charset=utf-8");
	
	$filePath = $_POST["file"];
	$filename = $_POST["filename"];
	$list = explode('.',$filePath);
	$subName = $list[count($list)-1];
	
	
	//刪除文件
	$arr = glob('*.ppt*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		unlink($name);
	}
	$arr = glob('*.doc*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		unlink($name);
	}
	$arr = glob('*.xls*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		
	}
	$name3 = dirname(__FILE__)."\\".$filename.".doc";
	unlink($name3);
	$name2 = dirname(__FILE__)."\\".$filename.".pdf";
	unlink($name2);
	/*
	$name2 = dirname(__FILE__)."\\".$filename.".ppt";
	unlink($name2);
	$name2 = dirname(__FILE__)."\\".$filename.".doc";
	unlink($name2);
	$name2 = dirname(__FILE__)."\\".$filename.".xls";
	unlink($name2);
	*/

	//執行exe檔
	//if($_SERVER["REMOTE_ADDR"]=="::1") $myIP = "0_0_0_0";
	//else $myIP = str_replace(".","_",$_SERVER["REMOTE_ADDR"]);
	
	$str = bin2hex($filePath.PHP_EOL);
	exec("php_Call_exe.exe \"".$str."\" \"".$filename."\"");
	
	//刪除文件
	$arr = glob('*.ppt*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		unlink($name);
	}
		
	$arr = glob('*.doc*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		unlink($name);
	}
	$name3 = dirname(__FILE__)."\\".$filename.".doc";
	unlink($name3);
	
	//寫入記事本
	//$mytxt = fopen("Peter_Lo.txt", "w") or die("Unable to open file!");
	//fwrite($mytxt, $filename);
	//fclose($mytxt);
	
?>