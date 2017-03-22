<?php 
/*
Выгружаем данные- количество на складе, цены и кода продукта
 сосбтвенного склада в файл filename
*/

define('DBMS', 'mysql');                      // database system  
define('DB_HOST', 'localhost');       // database host    
define('DB_USER', 'bnshop');   // username         
define('DB_PASS', 'sR7kE1P6');   // password         
define('DB_NAME', 'bnshop');       // database name    
define('DB_PRFX', 'ikej_');     // database prefix  

//echo "'".DB_HOST."' '".DB_USER."' '".DB_PASS."' '".DB_NAME."'".time();
$path_to_save_export_file ="";

	
/**
select sklad
*/
	if (set_db_connection ())	{
		$query ="select in_stock, Price, product_code from ".DB_PRFX."products" ;
		$filename ="/var/www/a60373/data/www/bnshop.ru/core/hsm/sklad.csv";
		//$_SERVER[DOCUMENT_ROOT] ."/core/hsm/sklad.csv";
		//echo $filename;
		$fp = fopen($filename, 'w');
		$result = mysql_query($query) or die( mysql_error( $db_conn ) );      
		// output header row (if at least one row exists)
		$row = mysql_fetch_assoc($result);
        /*   
		if($row) 		{
			fputcsv($fp, array_keys($row));
			// reset pointer back to beginning
			mysql_data_seek($result, 0);
        }		*/
		$i=1;
		while($row = mysql_fetch_assoc($result)) 		{			
			fputcsv($fp, $row);
			$i++;
		}     
		
		fclose($fp);
      echo "вывод склада окончен, Экспортировано $i товаров";
	}
	else 	{
      echo "ошибка коннекта";
	}
	

	
	
/*
Это  класс с основными методами  по апдейту базы

*/

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
				//	echo "warning! more whan one code. product_code= $product_code"."<br>";
				$result = "UPDATE ".DB_PRFX."products SET in_stock='".$in_stock."', price=".$price." WHERE  product_code='".$product_code."';"; 					
				}
			else {			
				//echo "не найден товар.  ввести новый? product_code= $product_code" ."<br>";				
				$result 	=false;
				}
		}

		return $result;
	}	
	
	function writelog ($logtext)
{
    $fp = fopen('parse.txt', 'a');    
    $log = fwrite($fp, $logtext);
    if (!$log)      
     {return "Error write log data";}     
    fclose($fp);
}
	
	

?>
