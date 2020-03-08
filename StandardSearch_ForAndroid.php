<?php
	/*
	//建立MySQL連線
	$con=mysqli_connect("localhost","barry","barry","fileinfo");
	
	
	//是否連接
	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}//if (mysqli_connect_errno())
	
	//清除資料參數
	$clear = "delete from result where ip = '".$ip."'";
	mysqli_query($con,$clear);
	
	$Similar = 0.01;
	//UTF8資料格式
	mysqli_query($con,"set names 'utf8'");	
	//第一次寫入
	$sql = "insert into search(id,ip,similar,search_word) values(null,\"".$ip."\",\"".$Similar."\",\"".$Keyword."\")";
	mysqli_query($con,$sql);
	//關閉資料庫
	mysqli_close($conn);
	*/
	
	//語意探索參數
	//$ip = $_POST["ip"];
	if (!empty($_SERVER["HTTP_CLIENT_IP"])){
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}else{
		$ip = $_SERVER["REMOTE_ADDR"];
	}
	$Keyword = $_POST["Keyword"];
	
	//紀錄
	if($Keyword!="")
	{
		file_put_contents('History_Android_App.txt', $ip."\t標準搜尋\t".$Keyword."\n", FILE_APPEND);
	}
	
	//開始清除資料
	//mysqli_query($con,$clear);
	
	//開始語意探索
	/*
	if($ip!="" && $Keyword!="")
	{
		if(mysqli_query($con,$sql))
		{
			echo 'Not Inserted'; 		
		}
		else
		{
			echo 'Inserted';
		}
	}
	*/
	
	/*
	//建立HTML檔案
	$filename="PeterLo.html";
	
	//寫入HTML檔案
	file_put_contents($filename,$ip."<br />",FILE_APPEND); 
	file_put_contents($filename,$Keyword."<br />",FILE_APPEND);
 
	//印出HTML檔案內容
	$msg = file_get_contents($filename);
	echo $msg;
	unlink('PeterLo.html');
	*/
	
?>
