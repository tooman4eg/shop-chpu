<?php
/*
Это  класс с основными методами  по апдейту базы

*/
include ('../config/connect.inc.php');
	/* 	
	*/
	
	
	function  set_db_connection ()		{ 
			@ $db=  mysql_pconnect (DB_HOST,DB_USER,DB_PASS);
			if (!$db) {
					echo 'Не могу подключиться к базе данных.';
					exit;
					$result =false ;
				}
			else {
					mysql_query('set names cp1251');
					mysql_select_db(DB_NAME);
					$result =true;
				}
	
			return $result;
		}


/**проверяем есть ли такой товар в базе
апдейтим склад 
*/
		
function update_stock_position ($in_stock,$price, $product_code)
	{
		$price = round (price*1.08, -1);
		//Вариант независящий от активности товара
		$check_sql ="select count(*) as count from ".DB_PRFX."products where product_code='".$product_code."'";

		//$check_sql ="select count(*) as count from ".DB_PRFX."products where product_code='".$product_code."' and enabled='1'";
		$sql_result = mysql_query($check_sql) or die( mysql_error( $db_conn ));      

		$row = mysql_fetch_assoc($sql_result);
		//print_r ($row);
		
		if ($row['count']==1)	{			
			$result = "UPDATE ".DB_PRFX."products SET in_stock='".$in_stock."', price=".$price." WHERE  product_code='".$product_code."';"; 			
			//echo $result."<br>";
		}
	else {
			if ($row['count']>1)
				{
			//		echo "warning! more whan one code. product_code= $product_code"."<br>";
				$result = "UPDATE ".DB_PRFX."products SET in_stock='".$in_stock."', price=".$price." WHERE  product_code='".$product_code."';"; 					
				}
			else {			
				//echo "не найден товар.  ввести новый? product_code= $product_code" ."<br>";				
				$result 	=false;
				}
		}

		return $result;
	}	
	

	
function getLastOrderNumber ()	
{ 

	$query ="select max(orderID) as  orderID from ".DB_PRFX."orders";
	$sql_result = mysql_query($query) or die( mysql_error( $db_conn ));      
	$row = mysql_fetch_assoc($sql_result);
	$maxOrderId = $row["orderID"];
	
	$query    = "SHOW TABLE STATUS from ".DB_NAME;
	$result = mysql_query($query);
	while($array = mysql_fetch_array($result)) {
		if ($array['Name']==DB_PRFX."orders")
		$ai = $array['Auto_increment'];
}
	if ($ai<$maxOrderId) echo "что-то пошло не так.  Максимальный номер заказа уже больше счетчика накрутки";


   return $ai;
    
}
	
function showFormNakrutka()	{
	 ?><form action="<?php echo $_SERVER['DOMAIN_NAME']. $_SERVER['PHP_SELF']; ?>" method="POST"> 
			<input type="text" name="countInc" value="0">
			<input type="submit" value="Накрутить" name="submit">
		</form>
		<?php
	}	
	
function writelog ($logtext)
{
    $fp = fopen('parse.txt', 'a');    
    $log = fwrite($fp, $logtext);
    if (!$log)      
     {return "Error write log data";}     
    fclose($fp);
}

function isNumber($value){
		/* Проверяем является ли переменная числом. Большим 0  
		*/
			$arr["result"] = false;
		if (!isset($value))  $arr["answ"]= "пустое значение для накрутки";
		else if (!is_numeric($value)) $arr["answ"] = "Надо ввести число >0";
			else	if ($value<=4) $arr["answ"] = "слишком маленькая накрутка. надо 5 или выше. иначе можно сбить заказ";
			else
//			if (isset($value) &&	is_numeric($value) && $value>4)
				$arr["result"]= true;		
			
			return $arr;
	}
	

?>