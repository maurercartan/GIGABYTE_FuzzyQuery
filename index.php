<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<?php
	error_reporting(0);    //隱藏報錯
	//寫入ini檔
	function write_ini_file($assoc_arr, $path)
	{
		$content = arr2ini($assoc_arr);

		if (!$handle = fopen($path, 'w')) 
		{
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		return $success;
	}
 
	function arr2ini(array $a, array $parent = array())
	{
		$out = '';
		foreach ($a as $k => $v)
		{
			if (is_array($v))
			{
				$sec = array_merge((array) $parent, (array) $k);
				$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
				$out .= arr2ini($v, $sec);
			}
			else
			{
				//$v = str_replace('"', '\"', $v);
				$v = iconv(mb_detect_encoding($v), "utf-8", $v);
				$out .= "$k=$v" . PHP_EOL;
			}
		}
		return $out;
	}
	
	//讀取ini檔
	function read_ini_file($path)
	{
		$str = parse_ini_file($path, true);
		return $str;
	}
	
	//讀取txt檔
	function read_txt_file($path)
	{
		$myfile = fopen($path, "r") or die("Unable to open file!");
		$str = fread($myfile,filesize($path));
		fclose($myfile);
		return $str;
	}
	
	//改變關鍵字顏色
	function colorText($text,$searchArray,$color)
	{
		for($i=0;$i<count($searchArray);$i++)
		{
			$text = str_replace($searchArray[$i], "<font color=\"".$color."\">".$searchArray[$i]."</font>", $text);
		}
		return $text;
	}
	
	function fixedSplit($text,$len)
	{
		$myLength = floor(strlen($text)/$len);
		$result = [];
		for ($i = 0; $i < $myLength; $i++)
		{
			$result[$i] = substr($text,$i * $len, $len);
		}
		return $result;
	}
	
	function Byte_to_String($bytes)
	{
		$strList = fixedSplit($bytes, 2);
		$result = [];
		for($i=0;$i<sizeof($strList);$i++)
		{
			$result[$i] = base_convert($strList[$i],16,10);
		}
		return implode(array_map("chr", $result));
	}
	
	//取得底層目錄名稱
	function getDirName($dirPath)
	{
		$arr = explode('\\',$dirPath);
		return $arr[sizeof($arr)-1];
	}
	
	function workingPage()
	{
		echo "<center><table>";
		echo "<tr><td colspan='6' align=\"center\">";
		echo "<a href=\"index.php\" title=\"Gigabyte\">";
		echo "<img width='1000px' height='500px' src=\"Image\\\\scoring.gif\" />";
		echo "</a>";
		echo "</td></tr>";
		
		echo "<tr><td colspan='6' align=\"center\">";
		echo "<font size='10' color='red'>KM系統學習中, 請稍候....</font>";
		echo "</td></tr>";
		echo "</table></center>";
	}
	
	function loginPage()
	{
		echo "<center><table>";
		echo "<tr><td colspan='6' align=\"center\">";
		echo "<a href=\"index.php\" title=\"Gigabyte\">";
		echo "<img src=\"Image\\\\總工程處.png\" />";
		echo "</a>";
		echo "</td></tr>";
		echo "<tr height='10px'><td></td></tr>";
		echo "<tr><td colspan='6' align=\"center\">";
		echo "<font color='blue' size='5' style=\"font-weight:bold;\">KM查詢系統</font>";
		echo "</td></tr>";
		echo "</table></center>";
		
		echo "<center><table>";
		echo "<form method='post'>";
		echo "<tr height='50px' align=\"center\">";
		echo "<td>帳號:</td>";
		echo "<td><input type=\"text\" name=\"user\" value=\"".$_POST['user']."\" style=\"width:150px\"></input></td>";
		echo "</tr>";
		echo "<tr height='50px' align=\"center\">";
		echo "<td>密碼:</td>";
		echo "<td><input type=\"password\" name=\"passwd\" style=\"width:150px\"></input></td>";
		echo "</tr>";
		echo "<tr height='50px' align=\"center\">";
		echo "<td colspan='2'>";
		echo "<input type='submit' value='登入' style=\"width:200px\" onClick=\"this.form.action='index.php';this.form.submit();\">";
		echo "</td>";
		echo "</tr>";
		echo "<tr height='50px' align=\"center\">";
		echo "<td colspan='2'>";
		echo "<font color='red'>建議於Google Chrome操作使用</font>";
		echo "</td>";
		echo "</tr>";
		echo "</form>";
		$msg = "";
		if($_POST['user']!="" or $_POST['passwd']!="")
		{
			if($_POST['user']=="gigabyte" and $_POST['passwd']=="gigabyte")
			{
				$msg = "<font color='blue'>遊客-登入成功!!</font>";
			}
			else if($_POST['user']=="SE" and $_POST['passwd']=="p291se")
			{
				$msg = "<font color='green'>管理者-登入成功!!</font>";
			}
			else
			{
				$msg = "<font color='red'>登入失敗!!</font>";
			}
		}
		echo "<tr height='50px' align=\"center\">";
		echo "<td colspan='2'>".$msg."</td>";
		echo "</tr>";
	}
	
	function ghost()
	{
		$arr = read_ini_file("config.cfg");
		$index = $arr['UPDATE']['index'];
		$count = $arr['ARGUMENT']['COUNT'];
		$dataPath = $_POST['dataPath'];
		
		echo "<div style=\"float:left;margin-left:20px;margin-top:20px;margin-right:0px;\">";
		echo "知識類別:";
		echo "<div style=\"margin-left:20;margin-top:10;\">";
		echo "<form method='post'>";
		
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"all\" onclick=\"check_all(this,'dataPath[]')\"><img width='50' height='50' src=\"Image\\\\all.png\" />all</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"0\"><img width='50' height='50' src=\"Image\\\\preferences_system.png\" />".getDirName(Byte_to_String($arr["UPDATE"][0]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"1\"><img width='50' height='50' src=\"Image\\\\new.ico\" />".getDirName(Byte_to_String($arr["UPDATE"][1]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"2\"><img width='50' height='50' src=\"Image\\\\datasheet.png\" />".getDirName(Byte_to_String($arr["UPDATE"][2]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"3\"><img width='50' height='50' src=\"Image\\\\sop.png\" />".getDirName(Byte_to_String($arr["UPDATE"][3]))."</input></br>";

		echo "</div>";
		echo "</div>";

		$keyWord = stripslashes($_POST['keyWord']);
		
						
		//瀏覽總人數
		$counter = intval(file_get_contents("counter.dat")) + 1;
		$fp = fopen("counter.dat", "w");
		fwrite($fp, $counter);
		fclose($fp);

		echo "<center><table>";
		echo "<tr><td colspan='6' align=\"center\">";
		echo "<a href=\"index.php\" title=\"Gigabyte\">";
		echo "<img src=\"Image\\\\總工程處.png\" />";
		echo "</a>";
		echo "</td></tr>";
		echo "<tr height='50px'>";
		echo "<td>輸入關鍵字:</td>";
		echo "<td><input id=\"textBox\" type=\"text\" name=\"keyWord\" style=\"width:150px\" value=\"".$_POST['keyWord']."\"></input></td>";
		//echo "<td><input id=\"startStopButton\" type=\"button\" height=25 width=25 onclick=\"startButton(event)\" style=\"background-image:url(./Image/microphone.ico);background-position: center;background-repeat: no-repeat;background-size: cover;\"/></td>";
		echo "<td><input id='standardButton' type='submit' value='標準查詢' onClick=\"this.form.action='standardSearch.php';this.form.submit();ready();\"></td>";
		echo "<td><input id='similarButton' type='submit' value='語意探索' onClick=\"this.form.action='similarSearch.php';this.form.submit();ready();\"></td>";
		echo "<td><input type='hidden' value=\"".$_POST['user']."\" name='user'></td>";
		echo "<td width=80><label id=\"infoBox\"></label></td>";
		echo "</tr>";
		echo "</form>";
		$arr = read_ini_file('config.cfg');
		$count = $arr['ARGUMENT']['COUNT'];
		echo "<tr><td height=\"70px\" colspan='6' align='center'><span id='span'>";
		echo "<font color='red'>資料庫總筆數:".$arr['ARGUMENT']['COUNT']."</font></br>";
		echo "<font color='red'>瀏覽總人數: ".$counter."</font>";
		echo "</span></td></tr>";
		echo "</table></center>";
	}
	
	function SE()
	{
		$arr = read_ini_file("config.cfg");
		$index = $arr['UPDATE']['index'];
		$count = $arr['ARGUMENT']['COUNT'];
		$dataPath = $_POST['dataPath'];
		
		echo "<div style=\"float:left;margin-left:20px;margin-top:20px;margin-right:0px;\">";
		echo "知識類別:";
		echo "<div style=\"margin-left:20;margin-top:10;\">";
		echo "<form method='post'>";
		/*
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"all\" onclick=\"check_all(this,'dataPath[]')\">all</input></br>";
		for($i=0;$i<$index;$i++)
		{
			$value = Byte_to_String($arr["UPDATE"][$i]);
			if(!in_array($i,$dataPath))
			{
				echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"".$i."\">".getDirName($value)."</input></br>";
			}
		}*/

		echo "<input type=\"checkbox\" checked=\"checked\" name=\"all\" onclick=\"check_all(this,'dataPath[]')\"><img width='50' height='50' src=\"Image\\\\all.png\" />all</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"0\"><img width='50' height='50' src=\"Image\\\\preferences_system.png\" />".getDirName(Byte_to_String($arr["UPDATE"][0]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"1\"><img width='50' height='50' src=\"Image\\\\new.ico\" />".getDirName(Byte_to_String($arr["UPDATE"][1]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"2\"><img width='50' height='50' src=\"Image\\\\datasheet.png\" />".getDirName(Byte_to_String($arr["UPDATE"][2]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"3\"><img width='50' height='50' src=\"Image\\\\sop.png\" />".getDirName(Byte_to_String($arr["UPDATE"][3]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"4\"><img width='50' height='50' src=\"Image\\\\debugger.png\" />".getDirName(Byte_to_String($arr["UPDATE"][4]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"5\"><img width='50' height='50' src=\"Image\\\\knowledgebase.png\" />".getDirName(Byte_to_String($arr["UPDATE"][5]))."</input></br>";
		echo "<input type=\"checkbox\" checked=\"checked\" name=\"dataPath[]\" value=\"6\"><img width='50' height='50' src=\"Image\\\\development.png\" />".getDirName(Byte_to_String($arr["UPDATE"][6]))."</input></br>";
		
		echo "</div>";
		echo "</div>";

		$keyWord = stripslashes($_POST['keyWord']);
		
						
		//瀏覽總人數
		$counter = intval(file_get_contents("counter.dat")) + 1;
		$fp = fopen("counter.dat", "w");
		fwrite($fp, $counter);
		fclose($fp);

		echo "<center><table>";
		echo "<tr><td colspan='6' align=\"center\">";
		echo "<a href=\"index.php\" title=\"Gigabyte\">";
		echo "<img src=\"Image\\\\總工程處.png\" />";
		echo "</a>";
		echo "</td></tr>";
		echo "<tr height='50px'>";
		echo "<td>輸入關鍵字:</td>";
		echo "<td><input id=\"textBox\" type=\"text\" name=\"keyWord\" style=\"width:150px\" value=\"".$_POST['keyWord']."\"></input></td>";
		//echo "<td><input id=\"startStopButton\" type=\"button\" height=25 width=25 onclick=\"startButton(event)\" style=\"background-image:url(./Image/microphone.ico);background-position: center;background-repeat: no-repeat;background-size: cover;\"/></td>";
		echo "<td><input id='standardButton' type='submit' value='標準查詢' onClick=\"this.form.action='standardSearch.php';this.form.submit();ready();\"></td>";
		echo "<td><input id='similarButton' type='submit' value='語意探索' onClick=\"this.form.action='similarSearch.php';this.form.submit();ready();\"></td>";
		echo "<td><input type='hidden' value=\"".$_POST['user']."\" name='user'></td>";
		echo "<td width=80><label id=\"infoBox\"></label></td>";
		echo "</tr>";
		echo "</form>";
		$arr = read_ini_file('config.cfg');
		$count = $arr['ARGUMENT']['COUNT'];
		echo "<tr><td height=\"70px\" colspan='6' align='center'><span id='span'>";
		echo "<font color='red'>資料庫總筆數:".$arr['ARGUMENT']['COUNT']."</font></br>";
		echo "<font color='red'>瀏覽總人數: ".$counter."</font>";
		echo "</span></td></tr>";
		echo "</table></center>";
	}
?>

<title>總工程處知識管理查詢系統</title>

<?php
	$userName = $_POST['user'];
	$passWord = $_POST['passwd'];
	//$userName = "root";
	//$passWord = "barry";
	if($userName=="gigabyte" and $passWord=="gigabyte")
	{
		ghost();
	}
	else if($userName=="SE" and $passWord=="p291se")
	{
		SE();
	}
	else
	{
		loginPage();
		//workingPage();
	}
?>

<script type="text/javascript">
	var infoBox; // 訊息 label
	var textBox; // 最終的辨識訊息 text input
	//var tempBox; // 中間的辨識訊息 text input
	//var startStopButton; // 「辨識/停止」按鈕
	var final_transcript = ''; // 最終的辨識訊息的變數
	var recognizing = false; // 是否辨識中

	function startButton(event) 
	{
		infoBox = document.getElementById("infoBox"); // 取得訊息控制項 infoBox
		textBox = document.getElementById("textBox"); // 取得最終的辨識訊息控制項 textBox
		//tempBox = document.getElementById("tempBox"); // 取得中間的辨識訊息控制項 tempBox
		//startStopButton = document.getElementById("startStopButton"); // 取得「辨識/停止」這個按鈕控制項
		//langCombo = document.getElementById("langCombo"); // 取得「辨識語言」這個選擇控制項
		if (recognizing) 
		{ // 如果正在辨識，則停止。
			recognition.stop();
		} 
		else 
		{ // 否則就開始辨識
			textBox.value = ''; // 清除最終的辨識訊息
			//tempBox.value = ''; // 清除中間的辨識訊息
			final_transcript = ''; // 最終的辨識訊息變數
			//recognition.lang = langCombo.value; // 設定辨識語言
			recognition.lang = "cmn-Hant-TW";
			recognition.start(); // 開始辨識
		}
	}

	if (!('webkitSpeechRecognition' in window)) 
	{  // 如果找不到 window.webkitSpeechRecognition 這個屬性
		// 就是不支援語音辨識，要求使用者更新瀏覽器。 
		infoBox.innerText = "本瀏覽器不支援語音辨識，請更換瀏覽器！(Chrome 25 版以上才支援語音辨識)";
	} 
	else 
	{
		var recognition = new webkitSpeechRecognition(); // 建立語音辨識物件 webkitSpeechRecognition
		recognition.continuous = true; // 設定連續辨識模式
		recognition.interimResults = true; // 設定輸出中先結果。

		recognition.onstart = function() 
		{ // 開始辨識
			recognizing = true; // 設定為辨識中
			//startStopButton.value = "按此停止"; // 辨識中...按鈕改為「按此停止」。  
			//infoBox.innerText = "辨識中...";  // 顯示訊息為「辨識中」...
			infoBox.innerHTML = "<img src='Image\\wait.gif' width='20' height='20'/>辨識中...";  // 顯示訊息為「辨識中」...
		};

		recognition.onend = function() 
		{ // 辨識完成
			recognizing = false; // 設定為「非辨識中」
			//startStopButton.value = "開始辨識";  // 辨識完成...按鈕改為「開始辨識」。
			infoBox.innerText = ""; // 不顯示訊息
		};

		recognition.onresult = function(event) 
		{ // 辨識有任何結果時
			var interim_transcript = ''; // 中間結果
			for (var i = event.resultIndex; i < event.results.length; ++i) 
			{ // 對於每一個辨識結果
				if (event.results[i].isFinal) 
				{ // 如果是最終結果
					final_transcript += event.results[i][0].transcript; // 將其加入最終結果中
				} 
				else 
				{ // 否則
					interim_transcript += event.results[i][0].transcript; // 將其加入中間結果中
				}
			}
			if (final_transcript.trim().length > 0) // 如果有最終辨識文字
				textBox.value = final_transcript; // 顯示最終辨識文字
			//if (interim_transcript.trim().length > 0) // 如果有中間辨識文字
			//	tempBox.value = interim_transcript; // 顯示中間辨識文字
		};
	}
	function ready()
	{
		var span = document.getElementById('span');
		span.innerHTML = "<img src='Image\\wait.gif' width='20' height='20'/>搜尋中...";
	}
	
	function check_all(obj,cName) 
	{ 
		var checkboxs = document.getElementsByName(cName); 
		for(var i=0;i<checkboxs.length;i++){checkboxs[i].checked = obj.checked;} 
	}
</script>
