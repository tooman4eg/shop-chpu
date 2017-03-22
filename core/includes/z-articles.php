<?php
/*
  Статьи для ShopCMS

  Автор: qjmann ( soulmare@gmail.com http://trickywebs.org.ua )
  Основано на коде ShopCMS модуля новостей, Copyright (c) by ADGroup
*/

        $articles_array = articlesGetArticlesToCustomer();
        $smarty->assign( "articles_array", $articles_array );
        if ( isset($_POST["subscribe"]) )
        {
                $error = subscrVerifyEmailAddress($_POST["email"]);
              if ( $_POST["modesubs"] == 0 ) {
              if ( $error == "" )
                {

                        if( _subscriberIsSubscribed ( $_POST["email"] )){

                        subscrUnsubscribeSubscriberByEmail2( $_POST["email"] );
                        $smarty->assign( "un_pol", 1);

                        }else{

                        $smarty->assign( "un_pol", 2);

                        }
                }
                else
                        $smarty->assign( "error_message", $error );
              }else{
                if ( $error == "" )
                {
                        $smarty->assign( "subscribe", 1 );
                        subscrAddUnRegisteredCustomerEmail( $_POST["email"] );
                }
                else
                        $smarty->assign( "error_message", $error );
                        }

        $smarty->assign( "main_content_template", "subscribe.tpl.html" );
        }

        if ( isset($_POST["email"]) )
                $smarty->hassign( "email_to_subscribe", $_POST["email"] );
        else
                $smarty->assign( "email_to_subscribe", "Email" );

        if ( isset($_GET["articles"]) ) { // Get articles listing
          global $articlesCountTotal;
          $pre_articles_array = articlesGetPreArticlesToCustomer(isset($_GET['offset']) ? intval($_GET['offset']) : 0);
          $smarty->assign("pre_articles_array", $pre_articles_array);
          $smarty->assign("main_content_template", "show_articles.tpl.html");
          $smarty->assign("arItemsTotal", $articlesCountTotal);
          $smarty->assign("arPagesTotal", ceil($articlesCountTotal / CONF_NEWS_COUNT_IN_NEWS_PAGE));
          $smarty->assign("arOffset", isset($_GET['offset']) ? intval($_GET['offset']) : 0);
        }
    
        if ( isset($_GET["fullarticles"]) ){
        
	    $fullarticles_array = articlesGetFullArticlesToCustomer($_GET["fullarticles"]);

	    if ( $fullarticles_array )
                {
                        $smarty->assign( "articles_full_array", $fullarticles_array );
                        $smarty->assign( "main_content_template", "show_full_articles.tpl.html" );
                        $metaTags = Array();
                        if($fullarticles_array['meta_description'])
                          $metaTags[] = "<meta name=\"description\" content=\"{$fullarticles_array['meta_description']}\">";
                        if($fullarticles_array['meta_keywords'])
                          $metaTags[] = "<meta name=\"keywords\" content=\"{$fullarticles_array['meta_keywords']}\">";
                        if(count($metaTags))
                          $smarty->assign( "page_meta_tags", implode("\n    ", $metaTags));
                }
                else
                {
                        header("HTTP/1.0 404 Not Found");
                        header("HTTP/1.1 404 Not Found");
                        header("Status: 404 Not Found");
                        die(ERROR_404_HTML);
                }

        }

?>
