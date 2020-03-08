<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
		sort($searchArray);
		for($i=0;$i<count($searchArray);$i++)
		{
			$startPos = containKeyWord($text,array($searchArray[$i]));
			$endPos = mb_strlen($searchArray[$i]);
			if(strtolower(mb_substr($text,$startPos,$endPos))==strtolower($searchArray[$i]))
			{
				$text = mb_substr($text,0,$startPos)."<font color=\"".$color."\">".mb_substr($text,$startPos,$endPos)."</font>".mb_substr($text,$startPos+$endPos);
			}
		}
		return $text;
	}
	
	//擷取部分字串
	function subText($text,$topicArray,$stringLength)
	{
		$mySubText = "";
		$searchPosition = containKeyWord($text,$topicArray);
		if($searchPosition>0)
		{
			if($stringLength < mb_strlen($text))
			{
				$start = $searchPosition-$stringLength/2;
				$length = $stringLength;
				
				if($start<0)
				{
					$start = 0;
				}
				if($start+$length > mb_strlen($text))
				{
					$start = mb_strlen($text)- $length;
					return mb_substr($text,$start,$length);
				}
				else return mb_substr($text,$start,$length);
			}
			else return $text;
		}
		else return mb_substr($text,0,$stringLength);
	}
	
	//(多條件)字串分割
	function multiexplode($arr,$str)
	{
		$ready = str_replace($arr,$arr[0],$str);
		return explode($arr[0],$ready);
	}
	
	//microseconds轉換
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
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
	
	//移除多餘字串
	function removeText($text)
	{
		$newText = $text;
		while(strpos($newText, "\n") !== false) $newText = str_replace("\n","",$newText);
		while(strpos($newText, "\r") !== false) $newText = str_replace("\r","",$newText);
		while(strpos($newText, "	") !== false) $newText = str_replace("	"," ",$newText);
		while(strpos($newText, "　") !== false) $newText = str_replace("　"," ",$newText);
		while(strpos($newText, "  ") !== false) $newText = str_replace("  "," ",$newText);
		while(strpos($newText, "..") !== false) $newText = str_replace("..",".",$newText);
		while(strpos($newText, "_") !== false) $newText = str_replace("_","",$newText);
		return $newText;
	}
	
	function containArray($str,$selectArray,$pathArray)
	{
		for($i=0;$i<count($selectArray);$i++)
		{
			if(strpos($str, $pathArray[$selectArray[$i]]) !== false) return true;
		}
		return false;
	}
	
	function path2id($path,$pathArray)
	{
		for($i=0;$i<count($pathArray);$i++)
		{
			if(strpos($path,$pathArray[$i]) !== false) return $i;
		}
		return -1;
	}
	
	function containKeyWord($text,$searchArray)
	{
		for($i=0;$i<count($searchArray);$i++)
		{
			$pos = mb_strpos(strtolower($text),$searchArray[$i]);
			if($pos!="") return $pos;
		}
		return 0;
	}
	
	function toUpper($arr)
	{
		$newArr = array();
		$count = 0;
		for($i=0;$i<count($arr);$i++)
		{
			$newArr[$count] = $arr[$i];
			$count++;
			if(!in_array(strtolower($arr[$i]),$arr))
			{
				$newArr[$count] = strtolower($arr[$i]);
				$count++;
			}
		}
		return $newArr;
	}
?>

<title>總工程處知識管理查詢系統</title>

<?php
	$dataPath = $_POST['dataPath'];
	$keyWord = stripslashes($_POST['keyWord']);
	$arr = read_ini_file("config.cfg");
	$index = $arr["UPDATE"]["index"];
	$dbHost = $arr["ARGUMENT"]["HOST"];
	$dbUser = $arr["ARGUMENT"]["USER"];
	$dbPassword = $arr["ARGUMENT"]["PASSWORD"];
	$dbDatabase = $arr["ARGUMENT"]["DATABASE_NAME"];
	$dirList = [];

	echo "<div style=\"float:left;margin-left:20px;margin-top:20px;margin-right:0px;\">";
	echo "知識類別:";
	echo "<div style=\"margin-left:20;margin-top:10;\">";
	echo "<form method='post'>";
	$msg = array();
	for($i=0;$i<$index;$i++)
	{
		if(in_array($i,$dataPath)) $msg[$i] = "checked=\"checked\"";
		else $msg[$i] = "";
	}
	
	$iconArray = array();
	$iconArray[0] = "Image\\\\preferences_system.png";
	$iconArray[1] = "Image\\\\new.ico";
	$iconArray[2] = "Image\\\\datasheet.png";
	$iconArray[3] = "Image\\\\sop.png";
	$iconArray[4] = "Image\\\\debugger.png";
	$iconArray[5] = "Image\\\\knowledgebase.png";
	$iconArray[6] = "Image\\\\development.png";
	
	$pathArray = array();
	for($i=0;$i<$index;$i++)
	{
		$pathArray[$i] = getDirName(Byte_to_String($arr["UPDATE"][$i]));
	}
	
	if($_POST['user']=="gigabyte")
	{
		echo "<input type=\"checkbox\" name=\"all\" onclick=\"check_all(this,'dataPath[]')\"><img width='50' height='50' src=\"Image\\\\all.png\" />all</input></br>";
		echo "<input type=\"checkbox\" ".$msg[0]." name=\"dataPath[]\" value=\"0\"><img width='50' height='50' src=\"Image\\\\preferences_system.png\" />".getDirName(Byte_to_String($arr["UPDATE"][0]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[1]." name=\"dataPath[]\" value=\"1\"><img width='50' height='50' src=\"Image\\\\new.ico\" />".getDirName(Byte_to_String($arr["UPDATE"][1]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[2]." name=\"dataPath[]\" value=\"2\"><img width='50' height='50' src=\"Image\\\\datasheet.png\" />".getDirName(Byte_to_String($arr["UPDATE"][2]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[3]." name=\"dataPath[]\" value=\"3\"><img width='50' height='50' src=\"Image\\\\sop.png\" />".getDirName(Byte_to_String($arr["UPDATE"][3]))."</input></br>";
	}
	else if($_POST['user']=="SE")
	{
		echo "<input type=\"checkbox\" name=\"all\" onclick=\"check_all(this,'dataPath[]')\"><img width='50' height='50' src=\"Image\\\\all.png\" />all</input></br>";
		echo "<input type=\"checkbox\" ".$msg[0]." name=\"dataPath[]\" value=\"0\"><img width='50' height='50' src=\"Image\\\\preferences_system.png\" />".getDirName(Byte_to_String($arr["UPDATE"][0]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[1]." name=\"dataPath[]\" value=\"1\"><img width='50' height='50' src=\"Image\\\\new.ico\" />".getDirName(Byte_to_String($arr["UPDATE"][1]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[2]." name=\"dataPath[]\" value=\"2\"><img width='50' height='50' src=\"Image\\\\datasheet.png\" />".getDirName(Byte_to_String($arr["UPDATE"][2]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[3]." name=\"dataPath[]\" value=\"3\"><img width='50' height='50' src=\"Image\\\\sop.png\" />".getDirName(Byte_to_String($arr["UPDATE"][3]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[4]." name=\"dataPath[]\" value=\"4\"><img width='50' height='50' src=\"Image\\\\debugger.png\" />".getDirName(Byte_to_String($arr["UPDATE"][4]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[5]." name=\"dataPath[]\" value=\"5\"><img width='50' height='50' src=\"Image\\\\knowledgebase.png\" />".getDirName(Byte_to_String($arr["UPDATE"][5]))."</input></br>";
		echo "<input type=\"checkbox\" ".$msg[6]." name=\"dataPath[]\" value=\"6\"><img width='50' height='50' src=\"Image\\\\development.png\" />".getDirName(Byte_to_String($arr["UPDATE"][6]))."</input></br>";
	}
	else
	{
		 header("location: index.php");
	}
	echo "</div>";
	echo "</div>";
	
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

	if($keyWord=="")
	{
		$arr = read_ini_file('config.cfg');
		$count = $arr['ARGUMENT']['COUNT'];
		echo "<tr><td height=\"70px\" colspan='6' align='center'><span id='span'>";
		echo "<font color='red'>資料庫總筆數:".$arr['ARGUMENT']['COUNT']."</font></br>";
		echo "<font color='red'>瀏覽總人數: ".$counter."</font>";
		echo "</span></td></tr>";
		echo "</table></center>";
	}
?>
			
<?php
	$start = microtime_float();

	//傳遞資料
	$mySearch = iconv(mb_detect_encoding($keyWord), "utf-8", $keyWord);
	$mySearch = strtolower($mySearch);
	$myIP = $_SERVER["REMOTE_ADDR"];
	if($mySearch!="")
	{
		file_put_contents('History_Web.txt', $myIP."\t標準搜索\t".$mySearch."\n", FILE_APPEND);
	}
	//file_put_contents('History.txt', $mySearch."\n", FILE_APPEND);
	if($keyWord!="")
	{
		$stop_word = array(' ','　',',','，','．','/','／','\\','＼','=','＝','+','＋','-','－','*','＊','(','（',')','）','&','＆','^','︿','%','％','$','＄','#','＃','@','＠','!','！','~','～','`','‵');
		$topic_array = multiexplode($stop_word,$keyWord);
		$topic_array = toUpper($topic_array);
		$color_array = array("#FFB7DD","#FFDDAA","#EEFFBB","#BBFFEE","#CCEEFF","#CCBBFF","#F0BBFF");
		
		$conn = mysql_connect($dbHost,$dbUser,$dbPassword);	//連結資料庫
		mysql_select_db($dbDatabase);				//使用資料庫
		mysql_query("set names 'utf8'");		//以uft8寫入資料 
		$keyWord = $_POST['keyWord'];
		$myStr = "select id,name,dir_path,text from info where (";
		for($i=0;$i<count($topic_array);$i++)
		{
			$myStr = $myStr."(name like '%".$topic_array[$i]."%' or text like '%".$topic_array[$i]."%') and ";
			//$myStr = $myStr."(name like '%".$topic_array[$i]."%' or name like '%".strtoupper($topic_array[$i])."%' or text like '%".$topic_array[$i]."% or text like '%".strtoupper($topic_array[$i])."%') and ";
		}
		$myStr = $myStr."(";
		for($i=0;$i<count($dataPath);$i++)
		{
			$myStr = $myStr."dir_path like \"%".getDirName(Byte_to_String($arr["UPDATE"][$dataPath[$i]]))."%\" or ";
		}
		$myStr = trim($myStr," or ")."));";

		$data = mysql_query($myStr);	//篩選資料

		$end = microtime_float();
		$time = round($end-$start,2);
		$arr = read_ini_file('config.cfg');
		$count = $arr['ARGUMENT']['COUNT'];
		echo "<tr><td height=\"70px\" colspan='6' align='center'><span id='span'>";
		echo "<font color='red'>資料庫總筆數:".$arr['ARGUMENT']['COUNT']."</font></br>";
		echo "<font color='red'>瀏覽總人數: ".$counter."</font></br>";
		echo "共有 ".mysql_num_rows($data)." 項結果(搜尋時間：".$time."秒)";
		echo "</span></td></tr>";
		echo "</table></center>";
		echo "</td></tr></table></center>";
?>

		<center>
			<table style="border-collapse:separate; border-spacing:40px 20px;">
				<tr>
					<td>搜索詞：</td>
					<?php
					for($i=0;$i<count($topic_array);$i++){
					?>
						<td>
							<?php
								echo "<input type=\"button\" value=\"".$topic_array[$i]."\" style=\"background-color:".$color_array[$i%7]."\";>";
							?>
						</td>
					<?php
					}
					?>
				</tr>
			</table>
		</center>
		<center><table width="900" border='1'>
			<tr>
				<td width="40">編號</td>
				<td>檔案內容</td>
				<td>知識類別</td>
			</tr>
			<?php
				for($i=0;$i<mysql_num_rows($data);$i++)
				{
					//可讀取中文
					header("Content-Type:text/html; charset=utf-8");
					$rs = mysql_fetch_row($data);
					$myID = $rs[0];
					$myName = $rs[1];
					$myDir = $rs[2];
					$myText = $rs[3];
					$subText = subText(removeText($myText),$topic_array,100);
					
					echo "<tr>";
					echo "<td>";
					echo $myID;
					echo "</td>";
					echo "<td>";
					$filePath = $myDir."/".$myName;
					$fileArray = explode("/",$filePath);
					$fileName = $fileArray[count($fileArray)-1];
					echo "<form action=\"downloadFile.php\" method=\"post\" target=\"_blank\">";
					echo "<input type='hidden' value='".$filePath."' name='file'>";
					echo "<input type='submit' value='".$fileName."'>";
					echo "</form>";
					echo colorText($subText,$topic_array,"red");
					echo "</td>";
					
					echo "<td>";
					$width=70;
					$height=70;
					echo "<img src=\"".$iconArray[path2id($myDir,$pathArray)]."\" width='".$width."' height='".$height."'/>";
					echo "</td>";
					echo "</tr>";
				}
			?>
		</table></center>
<?php
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
			//textBox.value = ''; // 清除最終的辨識訊息
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
		recognition.continuous = false; // 設定連續辨識模式
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
			{
				//textBox.value = final_transcript; // 顯示最終辨識文字
				textBox.value = textBox.value.concat(final_transcript); // 顯示最終辨識文字
			}
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