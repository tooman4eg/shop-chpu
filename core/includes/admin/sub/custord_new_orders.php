<?php
#####################################
# ShopCMS: Скрипт интернет-магазина
# Copyright (c) by ADGroup
# http://shopcms.ru
#####################################

if (  !defined('ADMIN_FILE')  ) 
{
        define('ADMIN_FILE', 'admin.php');

}
        //orders list
        if (  !strcmp($sub, "new_orders") )
        {
        if ( CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(7,$relaccess))) //unauthorized
        {
                          $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
                        } else {

                $order_detailes = (  isset($_POST["orders_detailed"]) || isset($_GET["orders_detailed"])  );

                if ( !$order_detailes )
                {

                        $order_statuses = ostGetOrderStatues();

                        function _setCallBackParamsToSearchOrders( &$callBackParam )
                        {
                                if ( isset($_GET["sort"]) )
                                        $callBackParam["sort"] = $_GET["sort"];
                                if ( isset($_GET["direction"]) )
                                        $callBackParam["direction"] = $_GET["direction"];

                                if ( $_GET["order_search_type"] == "SearchByOrderID" )
                                        $callBackParam["orderID"] = (int)$_GET["orderID_textbox"];
                                else if ( $_GET["order_search_type"] == "SearchByStatusID" )
                                {
                                        $orderStatuses = array();
                                        $data = ScanGetVariableWithId( array("checkbox_order_status") );
                                        foreach( $data as $key => $val )
                                                if ( $val["checkbox_order_status"] == "1" )
                                                        $orderStatuses[] = $key;
                                        $callBackParam["orderStatuses"] = $orderStatuses;
                                }
                        }

                        function _copyDataFromGetToPage( &$smarty, &$order_statuses )
                        {
                                if ( isset($_GET["order_search_type"])  )
                                        $smarty->assign( "order_search_type", $_GET["order_search_type"] );
                                if ( isset($_GET["orderID_textbox"]) )
                                        $smarty->assign( "orderID", (int)$_GET["orderID_textbox"] );
                                $data = ScanGetVariableWithId( array("checkbox_order_status") );
                                for( $i=0; $i<count($order_statuses); $i++ )
                                        $order_statuses[$i]["selected"] = 0;
                                foreach( $data as $key => $val )
                                {
                                        if ( $val["checkbox_order_status"] == "1" )
                                        {
                                                for( $i=0; $i<count($order_statuses); $i++ )
                                                        if ( (int)$order_statuses[$i]["statusID"] == (int)$key )
                                                                $order_statuses[$i]["selected"] = 1;
                                        }
                                }
                        }

                        function _getReturnUrl()
                        {
                                $url = ADMIN_FILE."?dpt=custord&sub=new_orders";
                                if ( isset($_GET["order_search_type"]) )
                                        $url .= "&order_search_type=".$_GET["order_search_type"];
                                if ( isset($_GET["orderID_textbox"]) )
                                        $url .= "&orderID_textbox=".$_GET["orderID_textbox"];
                                $data = ScanGetVariableWithId( array("checkbox_order_status") );
                                foreach( $data as $key => $val )
                                        $url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];
                                if ( isset($_GET["offset"]) )
                                        $url .= "&offset=".$_GET["offset"];
                                if ( isset($_GET["show_all"]) )
                                        $url .= "&show_all=".$_GET["show_all"];
                                $data = ScanGetVariableWithId( array("set_order_status") );
                                $changeStatusIsPressed = (count($data)!=0);
                                if ( isset($_GET["search"]) || $changeStatusIsPressed )
                                        $url .= "&search=1";
                                if ( isset($_GET["sort"]) )
                                        $url .= "&sort=".$_GET["sort"];
                                if ( isset($_GET["direction"]) )
                                        $url .= "&direction=".$_GET["direction"];
                                return base64_encode( $url );
                        }

                        function _getUrlToNavigate()
                        {
                                $url = ADMIN_FILE."?dpt=custord&sub=new_orders";
                                if ( isset($_GET["order_search_type"]) )
                                        $url .= "&order_search_type=".$_GET["order_search_type"];
                                if ( isset($_GET["orderID_textbox"]) )
                                        $url .= "&orderID_textbox=".$_GET["orderID_textbox"];
                                $data = ScanGetVariableWithId( array("checkbox_order_status") );
                                foreach( $data as $key => $val )
                                        $url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];

                                $data = ScanGetVariableWithId( array("set_order_status") );
                                $changeStatusIsPressed = (count($data)!=0);

                                if ( isset($_GET["search"]) || $changeStatusIsPressed )
                                        $url .= "&search=1";

                                if ( isset($_GET["sort"]) )
                                        $url .= "&sort=".$_GET["sort"];
                                if ( isset($_GET["direction"]) )
                                        $url .= "&direction=".$_GET["direction"];

                                return $url;
                        }


                        function _getUrlToSort()
                        {
                                $url = ADMIN_FILE."?dpt=custord&sub=new_orders";
                                if ( isset($_GET["order_search_type"]) )
                                        $url .= "&order_search_type=".$_GET["order_search_type"];
                                if ( isset($_GET["orderID_textbox"]) )
                                        $url .= "&orderID_textbox=".$_GET["orderID_textbox"];
                                $data = ScanGetVariableWithId( array("checkbox_order_status") );
                                foreach( $data as $key => $val )
                                        $url .= "&checkbox_order_status_".$key."=".$val["checkbox_order_status"];
                                if ( isset($_GET["offset"]) )
                                        $url .= "&offset=".$_GET["offset"];
                                if ( isset($_GET["show_all"]) )
                                        $url .= "&show_all=".$_GET["show_all"];

                                $data = ScanGetVariableWithId( array("set_order_status") );
                                $changeStatusIsPressed = (count($data)!=0);

                                if ( isset($_GET["search"]) || $changeStatusIsPressed )
                                        $url .= "&search=1";
                                return $url;
                        }

                        if(isset($_POST["status_cpast"])){
                        $dataup = ScanPostVariableWithId( array( "ordsel" ) );
                        foreach( $dataup as $key => $val )
                        {
                        ostSetOrderStatusToOrder( (int)$key, $_POST["status_cpast"], '', '' );
                        }
                        $smarty->assign( "status_cpast_ok", 1 );
                        }else{
                        $smarty->assign( "status_cpast_ok", 0 );
                        }

                        if(isset($_POST["orders_delete"])){
                        $dataup2 = ScanPostVariableWithId( array( "ordsel" ) );
                        foreach( $dataup2 as $key => $val )
                        {
                        ordDeleteOrder( (int)$key );
                        }
                        $smarty->assign( "orders_delete_ok", 1 );
                        }else{
                        $smarty->assign( "orders_delete_ok", 0 );
                        }


                        $data = ScanGetVariableWithId( array("set_order_status") );
                        $changeStatusIsPressed = (count($data)!=0);

                        if ( isset($_GET["search"]) || $changeStatusIsPressed )
                        {
                                _copyDataFromGetToPage( $smarty, $order_statuses );

                                $callBackParam = array();
                                _setCallBackParamsToSearchOrders( $callBackParam );
                                $orders = array();
                                $count = 0;
                                $navigatorHtml = GetNavigatorHtml( _getUrlToNavigate(), 20,
                                        'ordGetOrders', $callBackParam, $orders, $offset, $count );
                                $smarty->assign( "orders", $orders );
                                $smarty->assign( "navigator", $navigatorHtml );
                        }

                        if ( isset($_GET["offset"]) )
                                $smarty->assign( "offset", $_GET["offset"] );
                        if ( isset($_GET["show_all"]) )
                                $smarty->assign( "show_all", $_GET["show_all"] );
                        if ( isset($_GET["status_del"]) ){
                        if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                        {
                        Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&safemode=yes" );
                        }
                        DelOrdersBySDL((int)$_GET["status_del"]);
                        $smarty->assign( "status_del_ok", 1 );
                        }else{
                        $smarty->assign( "status_del_ok", 0 );
                        }

                        $smarty->hassign( "urlToSort", _getUrlToSort() );
                        $smarty->hassign( "urlToReturn", _getReturnUrl() );
                        $smarty->assign( "order_statuses", $order_statuses );
                }
                else
                {
				
function _GetExtraParametrsForItem( $productID, $itemID, $currency_code ){

        if(!is_array($productID)){

                $ProductIDs = array($productID);
                $IsProducts = false;
        }elseif(count($productID)) {

                $ProductIDs = &$productID;
                $IsProducts = true;
        }else {

                return array();
        }

        $ProductsExtras = array();
        $sql = 'select povt.productID,pot.optionID,pot.name,povt.option_value,povt.option_type,povt.option_show_times, povt.variantID, povt.optionID
                FROM ?#PRODUCT_OPTIONS_VALUES_TABLE as povt LEFT JOIN  ?#PRODUCT_OPTIONS_TABLE as pot ON pot.optionID=povt.optionID
                WHERE povt.productID IN (?@) ORDER BY pot.sort_order, pot.name
        ';
        $Result = db_phquery($sql, $ProductIDs);

        while ($_Row = db_fetch_assoc($Result)) {

                $_Row;
                $b=null;
                if (($_Row['option_type']==0 || $_Row['option_type']==NULL) && strlen( trim($_Row['option_value']))>0){

                        $ProductsExtras[$_Row['productID']][] = array(
                                'option_type' => $_Row['option_type'],
                                'name' => $_Row['name'],
                                'option_value' => $_Row['option_value']
                        );
                }
/**
* @features "Extra options values"
* @state begin
*/
                else if ( $_Row['option_type']==1 ){

                        //fetch all option values variants
                        $sql = 'select povvt.option_value, povvt.variantID, post.price_surplus'.
								' FROM '.PRODUCTS_OPTIONS_SET_TABLE.' as post
                                LEFT JOIN '.PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE.' as povvt
                                ON povvt.variantID=post.variantID
                                WHERE povvt.optionID='.$_Row['optionID'].' AND post.productID='.$_Row['productID'].' AND povvt.optionID='.$_Row['optionID'].'
                                ORDER BY povvt.sort_order, povvt.option_value
                        ';
                        $q2=db_query($sql);
                        $_Row['values_to_select']=array();
                        $i=0;
                        while( $_Rowue = db_fetch_assoc($q2)  ){

                                $_Row['values_to_select'][$i]=array();
                                $_Row['values_to_select'][$i]['option_value'] = $_Rowue['option_value'];
                                $_Row['values_to_select'][$i]['option_valueWithOutPrice'] = $_Rowue['option_value'];
                                $_Row['values_to_select'][$i]['price_surplus'] = show_priceWithOutUnit($_Rowue['price_surplus']);
                                $_Row['values_to_select'][$i]['variantID']=$_Rowue['variantID'];
                                $i++;
                        }
                        $_Row['values_to_select_count'] = count($_Row['values_to_select']);
						
						$e = db_query( "select variantID, price_surplus from `".DB_PRFX."item_options` 
										where `itemID` =".$itemID." and `optionID` =".$_Row['optionID']." limit 0,1" );
						if($erow = db_fetch_row($e))
						{
							$_Row['variantID'] = $erow[0];
							$_Row['price_surplus'] = $erow[1]." ".$currency_code;
						}
												
                        $ProductsExtras[$_Row['productID']][] = $_Row;
                }
                /**
* @features "Extra options values"
* @state end
*/
        }
        if(!$IsProducts){

                if(!count($ProductsExtras))return array();
                else {
                        return $ProductsExtras[$productID];
                }
        }
        return $ProductsExtras;
}
				
				
                        if ( isset($_GET["delete"]) )
                        {
                                if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                                {
                                        Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".(int)$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]."&safemode=yes" );
                                }

                                ordDeleteOrder( (int)$_GET["orderID"] );
                                Redirect( base64_decode($_GET["urlToReturn"]) );
                        }

                        if ( isset($_POST["set_status"]) )
                        {
                                if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                                {
                                        Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]."&safemode=yes" );
                                }

                                if ( (int)$_POST["status"] != -1 )
                                        ostSetOrderStatusToOrder( (int)$_GET["orderID"],
                                                $_POST["status"],
                                                isset($_POST['status_comment'])?$_POST['status_comment']:'',
                                                isset($_POST['notify_customer'])?$_POST['notify_customer']:'' );

                                Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".(int)$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"] );
                        }


                       if ( isset($_POST["save"]) )
                        {
                                if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                                {
                                        Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]."&safemode=yes" );
                                }
								
								if(isset($_POST["customer_first_name"]) && strlen($_POST["customer_first_name"]) > 0) $customer_first_name = $_POST["customer_first_name"];
								if(isset($_POST["customer_last_name"]) && strlen($_POST["customer_last_name"]) > 0) $customer_last_name = $_POST["customer_last_name"];
								if(isset($_POST["shipping_address"]) && strlen($_POST["shipping_address"]) > 0) $shipping_address = $_POST["shipping_address"];
								if(isset($_POST["shipping_city"]) && strlen($_POST["shipping_city"]) > 0) $shipping_city = $_POST["shipping_city"];
								if(isset($_POST["shipping_state"]) && strlen($_POST["shipping_state"]) > 0) $shipping_state = $_POST["shipping_state"];
								if(isset($_POST["shipping_country"]) && strlen($_POST["shipping_country"]) > 0) $shipping_country = $_POST["shipping_country"];
								if(isset($_POST["customers_comment"]) && strlen($_POST["customers_comment"]) > 0) $customers_comment = $_POST["customers_comment"];
								if(isset($_POST["admin_comment"]) && strlen($_POST["admin_comment"]) > 0) $admin_comment = $_POST["admin_comment"];								

								if(isset($_POST["shipping_cost"]) && strlen($_POST["shipping_cost"]) > 0) $shipping_cost = ", shipping_cost 	='".(float)$_POST["shipping_cost"]."', order_amount = order_amount-".(float)$_POST["old_shipping_cost"]." + ".(float)$_POST["shipping_cost"]." ";								
							
								$q = mysql_query("show columns from `".DB_PRFX."orders` like 'admin_comment'");
								$row = mysql_fetch_row($q);
								if(!isset($row[0]))								
								db_query( "ALTER TABLE `".DB_PRFX."orders` ADD `admin_comment` TEXT CHARACTER SET cp1251 COLLATE cp1251_general_ci NULL");
								db_query( "update `".DB_PRFX."orders` set ".
                                "        customer_firstname='".xToText($customer_first_name)."', ".
                                "        customer_lastname='".xToText($customer_last_name)."', ".
                                "        shipping_address='".xToText($shipping_address)."', ".
                                "        shipping_city 	='".xToText($shipping_city)."', ".
                                "        shipping_state 	='".xToText($shipping_state)."', ".
                                "        shipping_country 	='".xToText($shipping_country)."', ".
                                "        customers_comment 	='".xToText($customers_comment)."', ".
                                "        admin_comment 	='".xToText($admin_comment)."' ".	
                                $shipping_cost.								
                                " where orderID=".(int)$_GET["orderID"]) or die($er = mysql_error());
								
                                Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".(int)$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]);
                        }
					
                        if ( (isset($_POST["order_edited"]) and $_POST["order_edited"] > 0 and $_POST['qty_'.$_POST["itemID"]] > 0) or (isset($_GET["delete_id"]) and $_GET["delete_id"] > 0) )
                        {
							if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
							{
								Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"]."&safemode=yes" );
							}
							
							$itemID = (isset($_GET["delete_id"])) ? $_GET["delete_id"] : $_POST["itemID"];
							
							$old_clear_total_price = 0;
							$q3 = db_query( "select `Price`,`Quantity` from ".ORDERED_CARTS_TABLE." where `orderID`=".(int)$_GET["orderID"]);
							while( $row3=db_fetch_row($q3) ) $old_clear_total_price += $row3["Price"]*$row3["Quantity"];

							$q45 = db_query( "select `order_discount`, `order_amount` from ".ORDERS_TABLE." where `orderID`=".(int)$_GET["orderID"]);
							$old_order_discount_row = db_fetch_row($q45);
							$old_order_discount = $old_order_discount_row['order_discount'];
							$old_order_discount_amount = ($old_order_discount*$old_clear_total_price)/100;
							$old_order_amount = $old_order_discount_row['order_amount'];
							
							$other_part_amount = $old_order_amount + $old_order_discount_amount - $old_clear_total_price;
							
							if(isset($_GET["delete_id"]))
							{
								if ( CONF_CHECKSTOCK )
								{
									$q = db_query( "SELECT  `Quantity` FROM ".ORDERED_CARTS_TABLE." WHERE `itemID`=".$itemID);
									$row1 = db_fetch_row( $q );
									
									$prID = GetProductIdByItemId($itemID);
									
									db_query("UPDATE ".PRODUCTS_TABLE." SET `in_stock`=`in_stock`+".$row1["Quantity"]." WHERE `productID`=".$prID);
								}

								db_query("DELETE FROM ".SHOPPING_CART_ITEMS_CONTENT_TABLE." where itemID=".(int)$itemID);
								db_query("DELETE FROM ".ORDERED_CARTS_TABLE." where itemID=".(int)$itemID);									
								
								$message = "<b>Товар был удален из заказа.</b>";	
							}
							else
							{
								if ( CONF_CHECKSTOCK )
								{								
									$q = db_query( "SELECT  `in_stock` FROM ".PRODUCTS_TABLE." WHERE `productID`=".$_POST["product_item"]);
									$row = db_fetch_row( $q );
									
									$q = db_query( "SELECT  `Quantity` FROM ".ORDERED_CARTS_TABLE." WHERE `itemID`=".$itemID);
									$row1 = db_fetch_row( $q );

									if ($_POST['qty_'.$itemID] >= ($row['in_stock']+$row1['Quantity']))
									{
										if($row['in_stock'] > 0)
										{
											db_query("UPDATE ".PRODUCTS_TABLE." SET `in_stock`=0 WHERE `productID`=".$_POST["product_item"]);
											$d = $row1['Quantity']+$row['in_stock'];
											db_query("UPDATE ".ORDERED_CARTS_TABLE." SET `Quantity`=".$d. 
											" WHERE `itemID`=".$itemID);		
											$message = "<b>".$_POST['product_name']." закончился.</b>";
										}
										elseif ($row['in_stock'] === 0)
										{
											$f = $row1['Quantity'] - $_POST['qty_'.$itemID];
											db_query("UPDATE ".PRODUCTS_TABLE." SET `in_stock`=".(int)$f." WHERE `productID`=".$_POST["product_item"]);
											db_query("UPDATE ".ORDERED_CARTS_TABLE." SET `Quantity`=".$_POST['qty_'.$itemID]. 
											" WHERE `itemID`=".$_POST["itemID"]);		
											$message = "<b>Количество ".$_POST['product_name']." было изменено на ".$_POST['qty_'.$itemID].". На складе осталось ".$row['in_stock'].".</b>";								  }
									}

									if ($_POST['qty_'.$itemID] < ($row['in_stock']+$row1['Quantity']))
									{		
										$stock = $row['in_stock'] - $_POST['qty_'.$itemID]+$row1['Quantity'];
										$q = db_query("UPDATE ".PRODUCTS_TABLE."  SET `in_stock`=".$stock." WHERE `productID`=".$_POST["product_item"]);
										
										$q = db_query("UPDATE ".ORDERED_CARTS_TABLE." SET `Quantity`=".$_POST['qty_'.$itemID]." 
										WHERE `itemID`=".$itemID);
										$message = "<b>Количество ".$_POST['product_name']." было изменено на ".$_POST['qty_'.$itemID].". На складе осталось ".$stock.".</b>";
									}	
								}
								else
								{
									$q = db_query("UPDATE ".ORDERED_CARTS_TABLE." SET `Quantity`=".$_POST['qty_'.$itemID]." 
									WHERE `itemID`=".$itemID);
									$message = "<b>Количество ".$_POST['product_name']." было изменено на ".$_POST['qty_'.$itemID].".</b>";
								}
							}
							
							$q1 = db_query( "select `Price`, `Quantity` from ".ORDERED_CARTS_TABLE." where `orderID`=".(int)$_GET["orderID"]);
							$new_clear_total_price = 0;
							while( $row2=db_fetch_row($q1) ) $new_clear_total_price += $row2["Price"]*$row2["Quantity"];


							$qqq = db_query( "select customerID, currency_round from ".ORDERS_TABLE." where `orderID`=".(int)$_GET["orderID"]);
							$ORDcustomerID = db_fetch_row($qqq);
							$new_order_discount = dscCalculateDiscount( $new_clear_total_price, regGetLoginById( $ORDcustomerID[0] ) );
							$currency_round = $ORDcustomerID[1];
							$new_order_amount = $new_clear_total_price + $other_part_amount - $new_clear_total_price*$new_order_discount["discount_percent"]/100;	
							db_query("UPDATE ".ORDERS_TABLE." SET order_amount=".$new_order_amount.", order_discount=".$new_order_discount["discount_percent"]." where orderID=".(int)$_GET["orderID"]);			

							if(strlen($message) > 1) $message = "&message=".$message;
							Redirect(ADMIN_FILE."?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=".(int)$_GET["orderID"]."&urlToReturn=".$_GET["urlToReturn"].$message);
                        }

						if ( isset($_POST["extraparametrs_edited"]) and $_POST["extraparametrs_edited"] > 0 )
					{
						$q2 = db_query( "select SUM(`price_surplus`) from `".DB_PRFX."item_options` where `orderID`=".(int)$_GET["orderID"]);
						if( $row3=db_fetch_row($q2) ) $old_options_total_price = $row3[0];
						
						$extraparametrs_edited_value = explode(":", $_POST['option_select_'.$_POST["itemID"]]);
								db_query("UPDATE `".DB_PRFX."item_options` SET 
																		`variantID`='".(int)$extraparametrs_edited_value[1]."', "." 	
																		`price_surplus`='".(float)$extraparametrs_edited_value[0]."' "." 
										WHERE `itemID` =".$_POST["itemID"]." and `optionID` =".$_POST["optionID"]."  LIMIT 1");
										
						$prID = GetProductIdByItemId($_POST["itemID"]);
						$pr = GetProduct( $prID);
						$clear_price_product = $pr["Price"];
						$qps = db_query( "select SUM(`price_surplus`) from `".DB_PRFX."item_options` where `orderID`=".(int)$_GET["orderID"]." and `itemID` =".$_POST["itemID"]);
						if( $row5=db_fetch_row($qps) ) $options_total_price_for_item = $row5[0];
						
								db_query("UPDATE `".DB_PRFX."ordered_carts` SET 
																		`Price`='".($clear_price_product+$options_total_price_for_item)."'  	
										WHERE `itemID` =".(int)$_POST["itemID"]."  LIMIT 1");

						$q2 = db_query( "select SUM(`price_surplus`) from `".DB_PRFX."item_options` where `orderID`=".(int)$_GET["orderID"]);
						if( $row3=db_fetch_row($q2) ) $new_options_total_price = $row3[0];
						
						$q2 = db_query( "select Quantity from `".DB_PRFX."ordered_carts` where `itemID` =".(int)$_POST["itemID"]);
						if( $row44=db_fetch_row($q2) ) $item_quantity = $row44[0];
						
						$change_options_total_price = ($new_options_total_price - $old_options_total_price)*$item_quantity;
						db_query("UPDATE `".DB_PRFX."orders` SET order_amount=order_amount+".$change_options_total_price." where orderID=".(int)$_GET["orderID"]);	

					}
                        if ( isset($_GET["urlToReturn"]) )
                                $smarty->assign( "encodedUrlToReturn", $_GET["urlToReturn"] );
                        if ( isset($_GET["urlToReturn"]) )
                                $smarty->hassign( "urlToReturn", base64_decode($_GET["urlToReturn"]) );

                        $order = ordGetOrder( (int)$_GET["orderID"] );
						
						$q4 = db_query( "select *  from `".DB_PRFX."orders` where `orderID`=".(int)$_GET["orderID"]);
						if($o = db_fetch_row($q4)) $order["admin_comment"] = $o["admin_comment"];
						
                        $orderContent = ordGetOrderContent( (int)$_GET["orderID"]);
						

						$currencyID = currGetCurrentCurrencyUnitID();
						if ( $currencyID != 0 )
						{
								$currentCurrency = currGetCurrencyByID( $currencyID );
								$currency_code         = $currentCurrency["currency_iso_3"];
								$currency_value         = $currentCurrency["currency_value"];
								$currency_round         = $currentCurrency["roundval"];
						}
						else
						{
								$currency_code        = "";
								$currency_value = 1;
								$currency_round = 2;
						}
						
						db_query("CREATE TABLE IF NOT EXISTS `".DB_PRFX."item_options` (
																				  `itemID` int(11) NOT NULL,
																				  `optionID` int(11) DEFAULT NULL,
																				  `orderID` int(11) DEFAULT NULL,
																				  `variantID` int(11) DEFAULT NULL,
																				  `price_surplus` double DEFAULT '0')");								
								
						foreach($orderContent as $key => $item)
						{
						
							$e = db_query( "select optionID, variantID, price_surplus from `".DB_PRFX."item_options` where itemID=".(int)$item["itemID"] );
							while($erow = db_fetch_row($e))
								$options[] = $erow;
							
							$orderContent[$key]["ExtraParametrs"] = _GetExtraParametrsForItem((int)$item["pr_item"], (int)$item["itemID"], $currency_code);
							$orderContent[$key]["test"] = GetConfigurationByItemId((int)$item["itemID"]);
					
							
						}
						
						

						$total_sum_options = 0;
						$tsoq = db_query( "select sum(price_surplus) from `".DB_PRFX."item_options` where orderID=".(int)$_GET["orderID"] );
						if($tso = db_fetch_row($tsoq))
							$total_sum_options = $tso[0]." ".$currency_code;
                        $smarty->assign( "total_sum_options", $total_sum_options );
							
						
						if(count($orderContent) < 1) 
						{
							unset($orderContent);
							db_query("UPDATE `".DB_PRFX."orders` SET order_amount=0 where orderID=".(int)$_GET["orderID"]);	
							
						}
																			
						
                        $order_status_report = xNl2Br(stGetOrderStatusReport( (int)$_GET["orderID"] ));
                        $order_statuses = ostGetOrderStatues();
						
						
                        $smarty->assign( "message", $_GET["message"] );
                        $smarty->assign( "cancledOrderStatus", ostGetCanceledStatusId() );
                        $smarty->assign( "orderContent", $orderContent );
                        $smarty->assign( "order", $order );
                        $smarty->assign( "https_connection_flag", 1 );
                        $smarty->assign( "order_status_report", $order_status_report );
                        $smarty->assign( "order_statuses", $order_statuses );
                        $smarty->assign( "order_detailed", 1 );
                }
                $smarty->assign( "admin_sub_dpt", "custord_new_orders.tpl.html" );
        }
        }
?>