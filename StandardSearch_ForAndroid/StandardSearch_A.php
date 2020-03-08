<?php
    //open connection to mysql db
    $connection = mysqli_connect("localhost","barry","barry","fileinfo") or die("Error " . mysqli_error($connection));
	$filename = $_POST["filename"];
	//key
	$key1 = $_POST["key1"];
	$key2 = $_POST["key2"];
	$key3 = $_POST["key3"];
	$key4 = $_POST["key4"];
	$key5 = $_POST["key5"];
	$key6 = $_POST["key6"];
	$key7 = $_POST["key7"];
	$key8 = $_POST["key8"];
	$key9 = $_POST["key9"];
	$key10 = $_POST["key10"];
	//class
	$class1 = $_POST["class1"];
	$class2 = $_POST["class2"];
	$class3 = $_POST["class3"];
	$class4 = $_POST["class4"];
	$class5 = $_POST["class5"];
	$class6 = $_POST["class6"];
	$class7 = $_POST["class7"];

    //fetch table rows from mysql db
    $sql = "(select name,date,dir_path,"
	."if(instr((replace(replace(text,'  ',' '),'..','.')),'".$key1."')>=60,(substr(replace(replace(text,'  ',' '),'..','.'),(locate('".$key1."',(replace(replace(text,'  ',' '),'..','.'))))-50,120)),"
	."if(instr((replace(replace(text,'  ',' '),'..','.')),'".$key1."')<60,(substr(replace(replace(text,'  ',' '),'..','.'),1,120)),"
	."if(instr((replace(replace(text,'  ',' '),'..','.')),'".$key1."')>=(length((replace(replace(text,'  ',' '),'..','.')))-60),(substr(replace(replace(text,'  ',' '),'..','.'),length((replace(replace(text,'  ',' '),'..','.')))-120,120)),(substr(replace(replace(text,'  ',' '),'..','.'),1,100))"
	."))) from info where (dir_path like '%".$class1."%' or dir_path like '%".$class2."%' or dir_path like '%".$class3."%' or dir_path like '%".$class4."%' or dir_path like '%".$class5."%' or dir_path like '%".$class6."%' or dir_path like '%".$class7."%') and (name like '%".$key1."%' or text like '%".$key1."%')";
    
	for ( $i=2 ; $i<10 ; $i++ ) 
	{
		$sql = $sql."and (name like '%".$key.$i."%' or text like '%".$key.$i."%')";
	}
	
	$sql = $sql."order by date)";
	
	$result = mysqli_query($connection, $sql) or die("Error in Selecting " . mysqli_error($connection));

    //create an array
    $emparray = array();
    while($row =mysqli_fetch_assoc($result))
    {
        $emparray[] = $row;
    }
    echo json_encode($emparray);

    //close the db connection
    mysqli_close($connection);
	
	//Output to JSON File
	$file_Name = $filename . '.json';
	if(file_put_contents($file_Name,json_encode($emparray)))
	{
		//echo $file_Name . 'file_created';
	}//if
	else
	{
		echo 'There is some error';
	}//else
?>
