<?php
    //open connection to mysql db
    $connection = mysqli_connect("localhost","barry","barry","fileinfo") or die("Error " . mysqli_error($connection));
	$account = $_POST["account"];
	$password = $_POST["password"];
	$filename = $_POST["filename"];

    //fetch table rows from mysql db
    $sql = "select class.name from user,class,user_class where (user.name ='".$account."') and (user.password = '".$password."') and (user.id = user_class.userid) and (class.id = user_class.classid)";
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
