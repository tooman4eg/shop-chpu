<?php 

		include_once("httpdocs/core/config/connect.inc.php");
        $UserName_bnshop = DB_USER;
        $PassWord_bnshop = DB_PASS;
        $DataBase_bnshop = DB_NAME;
        $HostName_bnshop = DB_HOST; 

        $UserName_bonmarko = 'bonmarko_bn';
        $PassWord_bonmarko = 'bender';
        $DataBase_bonmarko = 'bonmarko_bn';
        $HostName_bonmarko = 'mysqlserver'; 
		
        $MySQLConnection = mysql_connect($HostName_bnshop, $UserName_bnshop, $PassWord_bnshop) or die("Unable to connect to MySQL Database!!");
        $MySQLSelectedDB = mysql_select_db($DataBase_bnshop, $MySQLConnection) or die("Could not Set the Database!!");

        $MySQLRecordSet = mysql_query("SELECT `product_code`,`in_stock` FROM ".PRODUCTS_TABLE." WHERE LENGTH(`product_code`)>0");

        while ($MyRow = mysql_fetch_array($MySQLRecordSet, MYSQL_ASSOC)) 
		{
            $bonmarko_products[$MyRow["product_code"]] = $MyRow["in_stock"];
        }
        mysql_close($MySQLConnection);
		
		
        $MySQLConnection = mysql_connect($HostName_bonmarko, $UserName_bonmarko, $PassWord_bonmarko) or die("Unable to connect to MySQL Database!!");
        $MySQLSelectedDB = mysql_select_db($DataBase_bonmarko, $MySQLConnection) or die("Could not Set the Database!!");
		
		echo '<table cellspacing="0" cellpadding="0" width="100%">';
		echo '<tr>';
		echo '<td colspan=2 align="center" class="hmin">';		
		echo 'Результаты синхронизации баз';				
		echo '</td>';	
		echo '</tr>';
		echo '<tr>';
		echo '<td align="center" class="hmin">';		
		echo 'Код товара';				
		echo '</td>';	
		echo '<td align="center" class="hmin">';		
		echo 'Новое количество';				
		echo '</td>';			
		echo '</tr>';		
		foreach($bonmarko_products as $art => $stock)
		{
			if(mysql_query("UPDATE `bonmarko_bnproducts` SET `in_stock`='".$stock."' WHERE `product_code`='".$art."' LIMIT 0,1"))
			{
				echo '<tr>';
				echo '<td align="center" class="hmin">';		
				echo $art;				
				echo '</td>';	
				echo '<td align="center" class="hmin">';		
				echo $stock;				
				echo '</td>';			
				echo '</tr>';
			}
		}
		echo '</table>';		
        mysql_close($MySQLConnection);
				

?>  