<?php
/*
  Статьи для ShopCMS

  Автор: qjmann ( soulmare@gmail.com http://trickywebs.org.ua )
  Лицензия: MIT - http://www.opensource.org/licenses/mit-license.php
  hash=1252259593
*/


// Check if module is installed

// Get table names
$q = db_query('SHOW TABLES');
$tables = array();
while($row = db_fetch_row($q))
  $tables[$row[0]] = true;

if(!isset($tables[DB_PRFX . 'articles'])) { // Create database structure
  $sql = 'CREATE TABLE `' . DB_PRFX . 'articles` (
  `AID` int(11) NOT NULL AUTO_INCREMENT,
  `update_date` date DEFAULT NULL,
  `title` text,
  `textToPrePublication` text,
  `textToPublication` mediumtext,
  `meta_description` text default \'\',
  `meta_keywords` text default \'\',
  `ordering` int(11) NOT NULL DEFAULT \'0\',
  `uri` VARCHAR(255) NULL,
  PRIMARY KEY (`AID`),
  UNIQUE KEY `uri_uniq` (`uri`)
  ) ENGINE=MyISAM DEFAULT CHARSET=cp1251';
  db_query($sql);
  die('<font color="green">Первый запуск модуля, инсталляция. Обновите страницу.</font>');
}

        if (!strcmp($sub, "articles"))
        {
        if ( CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(18,$relaccess))) //unauthorized
        {
                          $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
                        } else {

                function _getUrlToSubmit()
                {
                        $url = ADMIN_FILE."?dpt=modules&sub=articles";
                        if ( isset($_GET["offset"]) )
                                $url .= "&offset=".$_GET["offset"];
                        if ( isset($_GET["show_all"]) )
                                $url .= "&show_all=".$_GET["show_all"];
                        return $url;
                }

                function _getUrlToDelete()
                {
                        return _getUrlToSubmit();
                }

                if (isset($_GET["save_successful"])) //show successful save confirmation message
                        $smarty->assign("configuration_saved", 1);

                //current time
                $s = dtConvertToStandartForm( get_current_time() );
                $smarty->assign( "current_date", $s );

                if ( isset($_POST["articles_save"]) )
                {
                        if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                        {
                                Redirect( _getUrlToSubmit()."&safemode=yes" );
                        }

                        $picture = "";

                        $AID = articlesAddArticles($_POST["title"], $_POST["uri"], $_POST["textToPrePublication"],
                                        $_POST["textToPublication"], $_POST["meta_description"], $_POST["meta_keywords"] );

                        Redirect( _getUrlToSubmit()."&save_successful=yes" );
                }

                if ( isset($_GET["edit"]) )
                {
                $edit_articles = articlesGetArticlesToEdit($_GET["edit"]);
                $edit_articles["textToPrePublication"] = html_spchars($edit_articles["textToPrePublication"]);
                $edit_articles["textToPublication"] = html_spchars($edit_articles["textToPublication"]);
                $smarty->assign( "edit_articles", $edit_articles );
                $smarty->assign( "edit_articles_id", (int)$_GET["edit"]);
                $smarty->assign( "articles_editor", 1);
                }

                if ( isset($_GET["add_articles"]) )
                {
                $smarty->assign( "articles_editor", 1);
                }

                if ( isset($_POST["update_articles"]) )
                {
                        if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                        {
                                Redirect( _getUrlToSubmit()."&safemode=yes" );
                        }

                        articlesUpdateArticles($_POST["edit_articles_id"], $_POST["title"], $_POST["uri"], $_POST["textToPrePublication"], $_POST["textToPublication"], $_POST["meta_description"], $_POST["meta_keywords"] );
                        if ( isset($_POST["send"]) ) //send articles to subscribers
                                articlesSendArticles($_POST["edit_articles_id"]);
                        Redirect( _getUrlToSubmit()."&save_successful=yes" );
                }

                if (isset($_GET["delete"]))
                {
                        if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
                        {
                                Redirect( _getUrlToDelete()."&safemode=yes" );
                        }
                        articlesDeleteArticles($_GET["delete"]);
                        Redirect( _getUrlToDelete() );
                }


                $callBackParam        = array();
                $articles_posts                = array();
                $navigatorHtml = GetNavigatorHtml(ADMIN_FILE."?dpt=modules&sub=articles", 20,
                                                'articlesGetAllArticles', $callBackParam, $articles_posts, $offset, $count );
                $smarty->assign( "navigator", $navigatorHtml );
                $smarty->assign( "articles_posts", $articles_posts );

                $smarty->hassign( "urlToSubmit", _getUrlToSubmit() );
                $smarty->hassign( "urlToDelete", _getUrlToDelete() );

                //set sub-department template
                $smarty->assign( "admin_sub_dpt", "modules_articles.tpl.html" );
        }
        }
?>
