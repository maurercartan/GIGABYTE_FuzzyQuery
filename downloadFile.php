<?php
	error_reporting(0);    //隱藏報錯
	header("Content-Type:text/html; charset=utf-8");
	
	$filePath = $_POST['file'];
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
		unlink($name);
	}
	/*
	$arr = glob('*.pdf*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		unlink($name);
	}*/
	
	//執行exe檔
	$myIP = "";
	if($_SERVER["REMOTE_ADDR"]=="::1") $myIP = "0_0_0_0";
	else $myIP = str_replace(".","_",$_SERVER["REMOTE_ADDR"]);
	
	$str = bin2hex($filePath.PHP_EOL);
	exec("php_Call_exe.exe \"".$str."\" \"".$myIP."\"");

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
	
	/*
	$arr = glob('*.xls*');
	for($i=0;$i<count($arr);$i++)
	{
		$name = dirname(__FILE__)."\\".$arr[$i];
		unlink($name);
	}*/

	//讀取文件
	

	if (strpos($subName, 'xls') !== false) {
		$name = $myIP.".".$subName;
		$file = dirname(__FILE__)."\\".$myIP.".".$subName;
		$len = filesize($file); // Calculate File Size
		
		$app = "vnd.ms-powerpoint";
		header("Content-Type:application/".$app); // Send type of file
		header("Content-Disposition: inline; filename=".$name.";");	// Send File Name
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$len); // Send File Size
		@readfile($file);
	}
	else
	{
		$subName = "pdf";
		$name = $myIP.".pdf";
		$file = dirname(__FILE__)."\\".$myIP.".pdf";
		$len = filesize($file); // Calculate File Size
	
		$filename = $myIP.'.pdf';
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $len);
		header('Accept-Ranges: bytes');
		@readfile($file);
	}
?>