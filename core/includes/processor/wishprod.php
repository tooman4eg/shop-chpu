<?php
  #####################################
  # ShopCMS: Скрипт интернет-магазина
  # Copyright (c) by ADGroup
  # http://shopcms.ru
  #####################################

if(isset($_GET["addprod"]))
{
  
  function showproducts($cid, $orderID) //show products of the selected category
  {
	  if(CONF_CHECKSTOCK)
		$q = db_query("select productID, name, product_code FROM ".PRODUCTS_TABLE."  WHERE in_stock > 0 and Price > 0 and categoryID=".(int)$cid);
	  else
		$q = db_query("select productID, name, product_code FROM ".PRODUCTS_TABLE." WHERE  Price > 0 and categoryID=".(int)$cid);
		
      echo "<table class=\"adn\">";
      while ($row = db_fetch_row($q))
      {
          echo "<tr><td><a href=\"".ADMIN_FILE."?addprod=yes&amp;do=wishprod&amp;orderID=".$orderID."&amp;categoryID=".$cid."&amp;select_product=".$row[0]."\" style=\"font-size: 10px;\">"
              ."[".$row[2]."]".$row[1]."</a></td></tr>";
      }
      echo "</table>";
  }

  $relaccess = checklogin();

  if (CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(16, $relaccess))) //unauthorized
  {
      die(ERROR_FORBIDDEN);
  }

  if (!isset($_GET["owner"]) and !isset($_GET["orderID"])) //'owner product' not set
  {
      echo "<center><font color=red>".ERROR_CANT_FIND_REQUIRED_PAGE."</font>\n<br><br>\n";
      echo "<a href=\"javascript:window.close();\">".CLOSE_BUTTON."</a></center></body>\n</html>";
      exit;
  }

  $orderID = (int)$_GET["orderID"];
  $categoryID = isset($_GET["categoryID"]) ? (int)$_GET["categoryID"] : 0;

  if (isset($_GET["select_product"])) //add 2 wish-list (related items list)
  {
	$_GET["select_product"] = (int)$_GET["select_product"];

      if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
      {
          Redirect(ADMIN_FILE."?do=wishprod&safemode=yes&owner=".$owner);
      }

		$q3 = db_query( "select `Price`,`Quantity` from `".DB_PRFX."ordered_carts` where `orderID`=".$orderID);
		$old_clear_total_price = 0;
		while( $row3=db_fetch_row($q3) ) $old_clear_total_price += $row3["Price"]*$row3["Quantity"];
		
		$q45 = db_query( "select `order_discount`, `order_amount` from ".ORDERS_TABLE." where `orderID`=".(int)$_GET["orderID"]);
		$old_order_discount_row = db_fetch_row($q45);
		$old_order_discount = $old_order_discount_row['order_discount'];
		$old_order_discount_amount = ($old_order_discount*$old_clear_total_price)/100;
		$old_order_amount = $old_order_discount_row['order_amount'];
		
		$other_part_amount = $old_order_amount + $old_order_discount_amount - $old_clear_total_price;
									
		db_query("INSERT INTO ".SHOPPING_CART_ITEMS_TABLE." (productID) VALUES ('".$_GET["select_product"]."')");		
		
		$last_itemID = db_insert_id(); 
	  
		$q = db_query("select name,Price,product_code FROM `".DB_PRFX."products` WHERE productID=".$_GET["select_product"]);
		$row = db_fetch_row($q);
		list($product_name, $product_price, $pr_code) = $row; 		

		$extra = GetExtraParametrs($_GET["select_product"]);	
		foreach($extra as $key => $product_extra) 
		{
			if($product_extra["option_type"] == 1) 
			{
				if($product_extra["values_to_select_count"] > 0) 
				{
					$options[$key]["optionID"] = $product_extra["optionID"];								

					if($product_extra["variantID"] > 0) 
					{
						foreach($product_extra["values_to_select"] as $value_to_select) 
						{
							if($value_to_select["variantID"] == $product_extra["variantID"])
							{
								$options[$key]["price_surplus"] = $value_to_select["price_surplus"];
							}
						}
						$options[$key]["variantID"] = $product_extra["variantID"];	
					}
					else 
					{
						$options[$key]["variantID"] = 1;	
						$options[$key]["price_surplus"] = $product_extra["values_to_select"][0]["price_surplus"];	
					}
				}			
			}
		}
		
		if(count($options) > 0)
		{		
			foreach($options as $option)
				db_query("INSERT INTO `".DB_PRFX."item_options` (itemID, optionID, orderID, variantID, price_surplus) 
												VALUES ('".$last_itemID."',
														'".$option["optionID"]."',
														'".$orderID."',
														'".$option["variantID"]."',
														'".$option["price_surplus"]."')");													
		}

    	$q = db_query("select `itemID` FROM `".DB_PRFX."ordered_carts` WHERE `name` LIKE '%".$product_name."%' AND `orderID`=".$orderID." limit 0,1");
		if ($cnt = db_fetch_row($q)) // update
		{
			db_query("UPDATE `".DB_PRFX."ordered_carts` SET `Quantity` = `Quantity`+1 WHERE `itemID` =".$cnt[0]." LIMIT 1");
		}
		else // insert
		{
			$qps = db_query( "select SUM(`price_surplus`) from `".DB_PRFX."item_options` where `itemID`=".(int)$last_itemID." and `orderID`=".(int)$orderID);
			if( $rowsum=db_fetch_row($qps) ) $product_price += $rowsum[0];		
			
			if(strlen($pr_code) > 0) $pr_code = '['.$pr_code.']';
			db_query("INSERT INTO `".DB_PRFX."ordered_carts` (itemID, orderID, name, Price, Quantity, tax,	load_counter) VALUES ('".$last_itemID."', '{$orderID}', '{$pr_code} {$product_name}', '{$product_price}', '1', '0', '0')");		
		}
		
		if(CONF_CHECKSTOCK)
			db_query("UPDATE `".DB_PRFX."products`  SET `in_stock`=(`in_stock` - 1) WHERE `productID`=".$_GET["select_product"]);		
		
		$q1 = db_query( "select `Price`, `Quantity` from `".DB_PRFX."ordered_carts` where `orderID`=".$orderID);
		$new_clear_total_price = 0;
		while( $row2=db_fetch_row($q1) ) $new_clear_total_price += $row2["Price"]*$row2["Quantity"];
		
		$qqq = db_query( "select customerID, currency_round from ".ORDERS_TABLE." where `orderID`=".(int)$_GET["orderID"]);
		$ORDcustomerID = db_fetch_row($qqq);
		$new_order_discount = dscCalculateDiscount( $new_clear_total_price, regGetLoginById( $ORDcustomerID[0] ) );
		$currency_round = $ORDcustomerID[1];
		$new_order_amount = $new_clear_total_price + $other_part_amount - $new_clear_total_price*$new_order_discount["discount_percent"]/100;	
		
		db_query("UPDATE `".DB_PRFX."orders` SET order_amount=".$new_order_amount.", order_discount=".$new_order_discount["discount_percent"]." where orderID=".$orderID);	
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel=STYLESHEET href="data/admin/style.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>">
<title>Добавление товара</title>
</head>
<body>

<?php
  if (isset($_GET["safemode"]))
  {
      echo "<table class=\"adminw\"><tr><td align=\"left\"><table class=\"adn\"><tr>
          <td><img src=\"data/admin/stop2.gif\" align=\"left\" class=\"stop\"></td>
          <td class=\"splin\"><span class=\"error\">".ERROR_MODULE_ACCESS2."</span><br><br>".ERROR_MODULE_ACCESS_DES2."</td>
          </tr></table></td></tr></table>\n";

  }
?>
<table class="adn">
<tr class="lineb">
<td colspan="2" align="left">Добавление товара в заказ</td>
</tr>
<tr class="lineb">
<td width="50%" align="left"><?php
  echo ADMIN_RELATED_PRODUCTS_SELECT;
?></td>
<td width="50%" align="left"><?php
  echo ADMIN_SELECTED_PRODUCTS;
?></td></tr>
<tr>
<td valign=top><div style="background: #FFFFFF; padding: 5px;">
<?php
  $out = catGetCategoryCompactCList($categoryID);

  //show categories tree
  for ($i = 0; $i < count($out); $i++)
  {
      if ($out[$i]["categoryID"] == 0) continue;

      echo "<table class=\"adn\"><tr><td class=\"l1\">";
      for ($j = 0; $j < $out[$i]["level"] - 1; $j++)
      {
          if ($j == $out[$i]["level"] - 2)
          {
              echo "<img src=\"data/admin/pm.gif\" alt=\"\">";
          }
          else
          {
              echo "<img src=\"data/admin/pmp.gif\" alt=\"\">";
          }
      }
      if ($out[$i]["categoryID"] == $categoryID) //no link on selected category

      {
          echo "<img src=\"data/admin/minus.gif\" alt=\"\"></td><td class=\"l2\"><b>".$out[$i]["name"].
              "</b>\n";
          showproducts($categoryID, $orderID);
          echo "</td></tr></table>\n";
      }
      else //make a link

      {
          echo "<a href=\"".ADMIN_FILE."?addprod=yes&amp;do=wishprod&amp;orderID=".$orderID."&amp;categoryID=".$out[$i]["categoryID"]."\"";
          echo "><img src=\"data/admin/mplus.gif\" alt=\"\"></a></td><td class=\"l2\">";
          echo "<a href=\"".ADMIN_FILE."?addprod=yes&amp;do=wishprod&amp;orderID=".$orderID."&amp;categoryID=".$out[$i]["categoryID"]."\"";
          echo ">".$out[$i]["name"]."</a></td></tr></table>\n";

      }
  }
?>
</div>
</td>
<td valign=top><div style="background: #FFFFFF; padding: 5px;">
<?php
  $q = db_query("select name,itemID FROM `".DB_PRFX."ordered_carts` WHERE orderID=".$orderID);
  while ($row = db_fetch_row($q))
  {
	  echo "<table class=\"adn\"><tr>";
	  echo "<td class=\"l2\" style=\"font-size: 10px;\">".$row[0]."</td></tr></table>";
  }
?>
</div>
</td>
</tr>
</table>
<table class="adn"><tr><td class="separ"><img src="data/admin/pixel.gif" alt="" class="sep"></td></tr><tr><td class="se5"></td></tr></table>
<div align="center"><a href="#" onClick="window.close();" class="inl"><?php
  echo SAVE_BUTTON;
?></a></div>
<table class="adn"><tr><td class="se5"></td></tr></table>
</body>
</html>
<?php 
}
else
{
function showproducts($cid, $owner) //show products of the selected category

  {
      $q = db_query("select productID, name FROM ".PRODUCTS_TABLE." WHERE categoryID=".(int)$cid);
      echo "<table class=\"adn\">";
      while ($row = db_fetch_row($q))
      {
          echo "<tr><td><a href=\"".ADMIN_FILE."?do=wishprod&amp;owner=".$owner."&amp;categoryID=".$cid."&amp;select_product=".$row[0]."\" style=\"font-size: 10px;\">".
              $row[1]."</a></td></tr>";
      }
      echo "</table>";
  }

  $relaccess = checklogin();

  if (CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(16, $relaccess))) //unauthorized

  {
      die(ERROR_FORBIDDEN);
  }

  if (!isset($_GET["owner"])) //'owner product' not set

  {
      echo "<center><font color=red>".ERROR_CANT_FIND_REQUIRED_PAGE."</font>\n<br><br>\n";
      echo "<a href=\"javascript:window.close();\">".CLOSE_BUTTON."</a></center></body>\n</html>";
      exit;
  }

  $_GET["owner"] = isset($_GET["owner"]) ? $_GET["owner"] : 0;
  $owner = (int)$_GET["owner"];
  $categoryID = isset($_GET["categoryID"]) ? $_GET["categoryID"] : 0;
  $categoryID = (int)$categoryID;

  if (isset($_GET["select_product"])) //add 2 wish-list (related items list)
  {
  $_GET["select_product"] = (int)$_GET["select_product"];

      if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

      {
          Redirect(ADMIN_FILE."?do=wishprod&safemode=yes&owner=".$owner);
      }

      $q = db_query("select count(*) FROM ".RELATED_CONTENT_TABLE." WHERE productID=".$_GET["select_product"]." AND Owner=".$owner);
      $cnt = db_fetch_row($q);
      if ($cnt[0] == 0) // insert
               db_query("INSERT INTO ".RELATED_CONTENT_TABLE." (productID, Owner) VALUES ('".$_GET["select_product"]."', '".$owner."')");
  }

  if (isset($_GET["delete"])) //remove from wish-list

  {
      if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

      {
          Redirect(ADMIN_FILE."?do=wishprod&safemode=yes&owner=".$owner);
      }
      db_query("DELETE FROM ".RELATED_CONTENT_TABLE." WHERE productID=".(int)$_GET["delete"]." AND Owner=".$owner);
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel=STYLESHEET href="data/admin/style.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET; ?>">
<title><?php echo STRING_PROD_TITLE_W; ?></title>
</head>
<body>

<?php
  if (isset($_GET["safemode"]))
  {
      echo "<table class=\"adminw\"><tr><td align=\"left\"><table class=\"adn\"><tr>
          <td><img src=\"data/admin/stop2.gif\" align=\"left\" class=\"stop\"></td>
          <td class=\"splin\"><span class=\"error\">".ERROR_MODULE_ACCESS2."</span><br><br>".ERROR_MODULE_ACCESS_DES2."</td>
          </tr></table></td></tr></table>\n";

  }
?>
<table class="adn">
<tr class="lineb">
<td colspan="2" align="left"><?php
  echo STRING_PROD_TITLE_W;
?></td>
</tr>
<tr class="lineb">
<td width="50%" align="left"><?php
  echo ADMIN_RELATED_PRODUCTS_SELECT;
?></td>
<td width="50%" align="left"><?php
  echo ADMIN_SELECTED_PRODUCTS;
?></td></tr>
<tr>
<td valign=top><div style="background: #FFFFFF; padding: 5px;">
<?php
  $out = catGetCategoryCompactCList($categoryID);

  //show categories tree
  for ($i = 0; $i < count($out); $i++)
  {
      if ($out[$i]["categoryID"] == 0) continue;

      echo "<table class=\"adn\"><tr><td class=\"l1\">";
      for ($j = 0; $j < $out[$i]["level"] - 1; $j++)
      {
          if ($j == $out[$i]["level"] - 2)
          {
              echo "<img src=\"data/admin/pm.gif\" alt=\"\">";
          }
          else
          {
              echo "<img src=\"data/admin/pmp.gif\" alt=\"\">";
          }
      }
      if ($out[$i]["categoryID"] == $categoryID) //no link on selected category

      {
          echo "<img src=\"data/admin/minus.gif\" alt=\"\"></td><td class=\"l2\"><b>".$out[$i]["name"].
              "</b>\n";
          showproducts($categoryID, $owner);
          echo "</td></tr></table>\n";
      }
      else //make a link

      {
          echo "<a href=\"".ADMIN_FILE."?do=wishprod&amp;owner=".$owner."&amp;categoryID=".$out[$i]["categoryID"]."\"";
          echo "><img src=\"data/admin/mplus.gif\" alt=\"\"></a></td><td class=\"l2\">";
          echo "<a href=\"".ADMIN_FILE."?do=wishprod&amp;owner=".$owner."&amp;categoryID=".$out[$i]["categoryID"]."\"";
          echo ">".$out[$i]["name"]."</a></td></tr></table>\n";

      }
  }
?>
</div>
</td>
<td valign=top><div style="background: #FFFFFF; padding: 5px;">
<?php
  $q = db_query("select productID FROM ".RELATED_CONTENT_TABLE." WHERE Owner=".$owner);
  while ($row = db_fetch_row($q))
  {
      $p = db_query("select name FROM ".PRODUCTS_TABLE." WHERE productID=".$row[0]);
      if ($r = db_fetch_row($p))
      {
          echo "<table class=\"adn\"><tr>";
          echo "<td class=\"l2\" style=\"font-size: 10px;\">".$r[0]."</td>";
          echo "<td class=\"l3\"><a href=\"".ADMIN_FILE."?do=wishprod&amp;owner=".$owner."&amp;categoryID=".$categoryID."&amp;delete=".$row[0]."\">X</a></td></tr></table>";
      }
  }
?>
</div>
</td>
</tr>
</table>

<table class="adn"><tr><td class="separ"><img src="data/admin/pixel.gif" alt="" class="sep"></td></tr><tr><td class="se5"></td></tr></table>
<div align="center"><a href="#" onClick="window.close();" class="inl"><?php
  echo SAVE_BUTTON;
?></a></div>
<table class="adn"><tr><td class="se5"></td></tr></table>
</body>
</html>
<?php
}
?>