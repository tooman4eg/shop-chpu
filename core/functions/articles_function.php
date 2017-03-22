<?php
/*
  Статьи для ShopCMS

  Автор: qjmann ( soulmare@gmail.com http://trickywebs.org.ua )
  Основано на коде ShopCMS модуля новостей, Copyright (c) by ADGroup
*/

if(!defined('ARTICLES_TABLE'))
  define('ARTICLES_TABLE', DB_PRFX . 'articles');

function articlesGetArticlesToCustomer()
{
        $q = db_query( "select AID, uri, update_date, title, textToPrePublication from ".ARTICLES_TABLE." order by ordering DESC LIMIT 0,".CONF_NEWS_COUNT_IN_CUSTOMER_PART);
        $data = array();

        while( $r=db_fetch_row($q) )
        {
               $r["update_date"]=dtConvertToStandartForm($r["update_date"]);
               $data[] = $r;
        }
        return $data;
}

function articlesGetPreArticlesToCustomer($offset=0)
{
  global $articlesCountTotal;

  $offset_ = (int) $offset;
  $q = db_query( "select SQL_CALC_FOUND_ROWS AID, uri, update_date, title, textToPrePublication from ".ARTICLES_TABLE." order by ordering DESC LIMIT $offset_,".CONF_NEWS_COUNT_IN_NEWS_PAGE);
  $data = array();

  while($r=db_fetch_row($q)) {
    $data[] = $r;
  }

  // Get articles total count
  $q = db_query( 'SELECT FOUND_ROWS()');
  $row = db_fetch_row($q);
  $articlesCountTotal = $row[0];

  foreach($data as $k=>$r)
    $data[$k]["update_date"]=dtConvertToStandartForm($r["update_date"]);

  return $data;
}


function articlesGetFullArticlesToCustomer($articlesUri)
{
        $q = db_query( "select * from ".ARTICLES_TABLE." where uri='".xToText($articlesUri)."'");
        if  ( $r = db_fetch_row($q) )
        {
        $r["update_date"]=dtConvertToStandartForm($r["update_date"]);
		}
        return $r;
}

function articlesGetArticlesToEdit($articlesid)
{
        $q = db_query( "select * from ".ARTICLES_TABLE." where AID=".(int)$articlesid);
        $r=db_fetch_row($q);
        $r["update_date"]=dtConvertToStandartForm($r["update_date"]);
        return $r;
}

function articlesGetAllArticles( $callBackParam, &$count_row, $navigatorParams = null )
{
        if ( $navigatorParams != null )
        {
                $offset                        = $navigatorParams["offset"];
                $CountRowOnPage        = $navigatorParams["CountRowOnPage"];
        }
        else
        {
                $offset = 0;
                $CountRowOnPage = 0;
        }

        $q = db_query( "select AID, uri, update_date, title from ".ARTICLES_TABLE." order by update_date DESC" );

        $i = 0;
        $data = array();
        while( $r=db_fetch_row($q) )
        {
                if ( ($i >= $offset && $i < $offset + $CountRowOnPage) ||
                                $navigatorParams == null  )
                {
                   $r["update_date"]=dtConvertToStandartForm($r["update_date"]);
                   $data[] = $r;
                }
                $i++;
        }
        $count_row = $i;
        return $data;
}

function articlesAddArticles( $title, $uri, $textToPrePublication, $textToPublication, $metaDescription, $metaKeywords )
{
        $stamp = microtime();
        $stamp = explode(" ", $stamp);
        $stamp = $stamp[1];
        db_query( "insert into ".ARTICLES_TABLE." ( update_date, title, uri, textToPrePublication, textToPublication, meta_description, meta_keywords ) ".
                  " values( NOW(), '".xToText(trim($title))."', '".xToText(trim($uri))."', '".xEscSQL($textToPrePublication)."', '".xEscSQL($textToPublication)."', '".xEscSQL($metaDescription)."', '".xEscSQL($metaKeywords)."' ) ");
        return db_insert_id();
}

function articlesUpdateArticles( $id_articles, $title, $uri, $textToPrePublication, $textToPublication, $metaDescription, $metaKeywords )
{
  $sql = "update ".ARTICLES_TABLE.
         " set     update_date=NOW(), ".
         "         title='".xToText($title)."', ".
         "         uri='".xToText($uri)."', ".
         "         textToPrePublication='".xEscSQL($textToPrePublication)."', ".
         "         textToPublication='".xEscSQL($textToPublication)."', ".
         "         meta_description='".xEscSQL($metaDescription)."', ".
         "         meta_keywords='".xEscSQL($metaKeywords)."' ".
         " where AID = ".(int)$id_articles;
  db_query($sql);
}

function articlesDeleteArticles( $articlesid )
{
        db_query( "delete from ".ARTICLES_TABLE." where AID=".(int)$articlesid );
}

?>
