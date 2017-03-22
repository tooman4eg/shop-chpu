<?php
/*
  Модуль "XML Sitemap для ShopCMS"
  2011 (c) http://trickywebs.org.ua/
  Техническая поддержка - soulmare@gmail.com
  Лицензия - MIT http://www.opensource.org/licenses/mit-license.php
*/

// Function to ping Sitemaps
function pingSitemaps($url_xml, $engine, $url_engine) {
   $status = 0;
   if( $fp=@fsockopen($engine, 80) )
   {
      $req =  'GET ' . $url_engine . '' .
              urlencode( $url_xml ) . " HTTP/1.1\r\n" .
              "Host: $engine\r\n" .
              "User-Agent: Mozilla/5.0 (compatible; " .
              PHP_OS . ") PHP/" . PHP_VERSION . "\r\n" .
              "Connection: Close\r\n\r\n";
      fwrite( $fp, $req );
      while( !feof($fp) )
      {
         if( @preg_match('~^HTTP/\d\.\d (\d+)~i', fgets($fp, 128), $m) )
         {
            $status = intval( $m[1] );
            break;
         }
      }
      fclose( $fp );
   }
   return( $status );
}

// NEW CODE (Sitemap Index)
function countRows($table_name, $enabled) {
  if ($enabled == 1) $en = " where enabled = 1";
  $q = db_query("select count(*) from ".$table_name."".$en);
  $r = db_fetch_row($q);
  return($r[0]);
}
function indexSitemapNew($modxError, $handle, $xmlFile, $file_num) {
  if (!$modxError) {
    if (fwrite($handle, '</urlset>') === false) {
      $modxError = true;
      $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
    }
  }
  fclose($handle);
  $xmlFile = dirname($_SERVER['SCRIPT_FILENAME'])."/sitemap".$file_num.".xml";
  $res = @fopen($xmlFile, 'w');
  // Write file header
  $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $str .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
  fwrite($res, $str);
  return $res;
}

ini_set('display_errors',1);
error_reporting(E_ALL);

// Check engine version
if(is_dir(dirname($_SERVER['SCRIPT_FILENAME']) . '/core'))
  define('MODX_MODERN_ENGINE', 1);
else
  define('MODX_MODERN_ENGINE', 0);

// Max count of URLs per write loop
define('MODX_WRITE_URLS_MAX', 1000);

// Max count of URLs per one XML-file
define('MODX_WRITE_FILE_MAX', 50000);

// Compatibility with Friendly URLs module (autodetection)
define('MODX_FRIENDLY_URLS_COMPATIBILITY', 1);

// Use Friendly URLs module
if(file_exists(dirname($_SERVER['SCRIPT_FILENAME']) . '/url_rewriter.php'))
  require_once(dirname($_SERVER['SCRIPT_FILENAME']) . '/url_rewriter.php');

// Verifying checkboxes
if(isset($_POST["tagsCatalog"])) {
	if(isset($_POST['includeCatalog']) || isset($_POST['includeNews']) || isset($_POST['includeArticles']) || isset($_POST['includeStatpages'])) {
		$modxError = false;
	} else {
		$modxError = true;
		$smarty->assign('resultError', 'Сгенерирован пустой XML-файл');
	}
}

if (!strcmp($sub, "xml_sitemap")) {
  if (CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(13,$relaccess))) //unauthorized
  {
    $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
  } else {

      if(isset($_POST['fACTION']) && ('gen_sitemap' == $_POST['fACTION'])) {

        // NEW CODE (Sitemap Index)
        // Delete old sitemap_x.xml files
        foreach (glob(dirname($_SERVER['SCRIPT_FILENAME'])."/sitemap*.xml") as $del) {
          unlink($del);
        }
        // Count rows in DB
        $total = 0;
        if (isset($_POST['includeCatalog'])) {
          $total = countRows(CATEGORIES_TABLE,'0') - 1;
          $row_counter = $total;
          $total += countRows(PRODUCTS_TABLE,'1');
        }   
        if (isset($_POST['includeStatpages'])) $total += countRows(AUX_PAGES_TABLE,'0');
        if (isset($_POST['includeNews'])) $total += countRows(AUX_PAGES_TABLE,'0') + 1;
        
        // Articles Addon
        if (!defined('ARTICLES_TABLE')) define('ARTICLES_TABLE', DB_PRFX . 'articles');
        if (isset($_POST['includeArticles'])) $total += countRows(ARTICLES_TABLE,'0') + 1;

        if ($total > MODX_WRITE_FILE_MAX) {
          $file_num = 1;
        }
        $xmlFile = dirname($_SERVER['SCRIPT_FILENAME'])."/sitemap".$file_num.".xml";
        // END OF NEW CODE

        if($handle = @fopen($xmlFile, 'w')) { 
        
          // Write file header
          $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
          $str .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
          fwrite($handle, $str);
		  
          // Catalog
          if(isset($_POST['includeCatalog']) && !$modxError) {

            // Get categories tree
            $catTree = catGetCategoryCListMin();
            // Write categories to file
            $str = "  <!-- Categories -->\n";
            foreach($catTree as $cat) {
               $uri = function_exists('fu_make_url') && $cat['uri'] && MODX_FRIENDLY_URLS_COMPATIBILITY ? fu_make_url($cat) : "category_{$cat['categoryID']}.html";
			         $str .= "   <url>\n      <loc>http://" . CONF_SHOP_URL . "/".$uri."</loc>\n      <changefreq>".$_POST['tagsCatalogFreq']."</changefreq>\n      <priority>" . $_POST['tagsCatalog'] . "</priority>\n   </url>\n";
            }
            if(fwrite($handle, $str) === false) {
              $modxError = true;
              $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
            }

            // Products
            fwrite($handle, "  <!-- Products -->\n");
            $limitStart = 0;
            $limitCount = MODX_WRITE_URLS_MAX;
            $prodCountSelected = $limitCount;
            $fieldsList = function_exists('fu_make_url') && MODX_FRIENDLY_URLS_COMPATIBILITY ? 'productID, categoryID, uri, uri_opt_val' : 'productID';
            while($prodCountSelected == $limitCount) {
              $sql = "SELECT $fieldsList, DATE_FORMAT(date_added, '%Y-%m-%d') as date_added, DATE_FORMAT(date_modified, '%Y-%m-%d') as date_modified
                      FROM " . PRODUCTS_TABLE . "
                      WHERE categoryID != 0
                            AND enabled = 1
                      LIMIT $limitStart, $limitCount";
              $result = db_query($sql); 

              // Update counters
              $limitStart += $limitCount;
              $prodCountSelected = db_num_rows($result['resource']);

              // NEW CODE (Sitemap Index)
              $row_counter += $prodCountSelected;
              if ($row_counter > MODX_WRITE_FILE_MAX) {
                $file_num++;
                $handle = indexSitemapNew($modxError, $handle, $xmlFile, $file_num);
                $row_counter = $prodCountSelected;
              }

              // Write URLs to file
              $str = '';
              while($row = db_fetch_row($result)) {
                $uri = MODX_FRIENDLY_URLS_COMPATIBILITY && function_exists('fu_make_url') && $row['uri'] ? fu_make_url($row) : "product_{$row['productID']}.html";
                $str .= "   <url>\n      <loc>http://" . CONF_SHOP_URL . "/".$uri."</loc>\n";
                if (isset($_POST['lastmod'])) {
                  $str .= "      <lastmod>" . (!$row['date_modified'] ? $row['date_added'] : $row['date_modified']) . "</lastmod>\n";
                }
                $str .= "      <changefreq>".$_POST['tagsProductFreq']."</changefreq>\n      <priority>" . $_POST['tagsProduct'] . "</priority>\n   </url>\n";
              }
              if(fwrite($handle, $str) === false) {
                $modxError = true;
                $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
                break;
              }
            }
          }

          // News
          if(isset($_POST['includeNews']) && !$modxError) {
			     $news_root = FU_NEWS_ROOT ? FU_NEWS_ROOT : "news";
			     fwrite($handle, "  <!-- News -->\n   <url>\n      <loc>http://" . CONF_SHOP_URL . "/" . $news_root . ".html</loc>\n      <changefreq>".$_POST['tagsNewsFreq']."</changefreq>\n      <priority>" . $_POST['tagsNews'] . "</priority>\n   </url>\n");
            $fieldsList = function_exists('fu_make_url_news') && MODX_FRIENDLY_URLS_COMPATIBILITY ? 'NID, uri' : 'NID';
            $sql = "SELECT $fieldsList, add_date
                    FROM " . NEWS_TABLE;
            $result = db_query($sql);

            // NEW CODE (Sitemap Index)
            $row_counter += db_num_rows($result['resource']) + 1;
            if ($row_counter > MODX_WRITE_FILE_MAX) {
              $file_num++;
              $handle = indexSitemapNew($modxError, $handle, $xmlFile, $file_num);
              $row_counter = db_num_rows($result['resource']);
            }

            // Write URLs to file
            $str = '';
            while($row = db_fetch_row($result)) {
              $uri = MODX_FRIENDLY_URLS_COMPATIBILITY && function_exists('fu_make_url_news') && $row['uri'] ? fu_make_url_news($row) : "show_news_{$row['NID']}.html";
              $str .= "   <url>\n      <loc>http://" . CONF_SHOP_URL . "/".$uri."</loc>\n";
              if (isset($_POST['lastmod'])) {
                $str .= "      <lastmod>" . $row['add_date'] . "</lastmod>\n";
              }
              $str .= "      <changefreq>".$_POST['tagsNews2Freq']."</changefreq>\n      <priority>" . $_POST['tagsNews'] . "</priority>\n   </url>\n";
            }
            if(fwrite($handle, $str) === false) {
              $modxError = true;
              $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
            }
          }

		  // Articles
		  if(isset($_POST['includeArticles']) && !$modxError) {  
			fwrite($handle, "  <!-- Articles -->\n   <url>\n      <loc>http://" . CONF_SHOP_URL . "/" . ARTICLES_ROOT . ".html</loc>\n      <changefreq>".$_POST['tagsArticlesFreq']."</changefreq>\n      <priority>" . $_POST['tagsArticles'] . "</priority>\n   </url>\n");
            $sql = "SELECT uri, update_date
                    FROM " . ARTICLES_TABLE;
            $result = db_query($sql);

            // NEW CODE (Sitemap Index)
            $row_counter += db_num_rows($result['resource']) + 1;
            if ($row_counter > MODX_WRITE_FILE_MAX) {
              $file_num++;
              $handle = indexSitemapNew($modxError, $handle, $xmlFile, $file_num);
              $row_counter = db_num_rows($result['resource']);
            }

            // Write URLs to file
            $str = '';
            while($row = db_fetch_row($result)) {
			  $uri = function_exists('fu_make_url_articles') && MODX_FRIENDLY_URLS_COMPATIBILITY ? fu_make_url_articles($row['uri']) : ARTICLES_ROOT."/{$row['uri']}.html";
              $str .= "   <url>\n      <loc>http://" . CONF_SHOP_URL . "/".$uri."</loc>\n      <changefreq>".$_POST['tagsArticles2Freq']."</changefreq>\n";
              if (isset($_POST['lastmod'])) {
                $str .= "      <lastmod>" . $row['update_date'] . "</lastmod>\n";
              }
              $str .= "      <priority>" . $_POST['tagsArticles'] . "</priority>\n   </url>\n";
            }
            if(fwrite($handle, $str) === false) {
              $modxError = true;
              $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
            }
          }

          // Statpages
          if(isset($_POST['includeStatpages']) && !$modxError) {
            fwrite($handle, "  <!-- Statpages -->\n");
            $sql = "SELECT aux_page_ID
                    FROM " . AUX_PAGES_TABLE;
            $result = db_query($sql);

            // NEW CODE (Sitemap Index)
            $row_counter += db_num_rows($result['resource']);
            if ($row_counter > MODX_WRITE_FILE_MAX) {
              $file_num++;
              $handle = indexSitemapNew($modxError, $handle, $xmlFile, $file_num);
              $row_counter = db_num_rows($result['resource']);
            }

            // Write URLs to file
            $str = '';
            while($row = db_fetch_row($result)) {
              $uri = function_exists('fu_make_url_pages') && MODX_FRIENDLY_URLS_COMPATIBILITY ? fu_make_url_pages($row['aux_page_ID']) : "page_{$row['aux_page_ID']}.html";
              $str .= "   <url>\n      <loc>http://" . CONF_SHOP_URL . "/".$uri."</loc>\n      <changefreq>".$_POST['tagsPagesFreq']."</changefreq>\n      <priority>" . $_POST['tagsPages'] . "</priority>\n   </url>\n";
            }
            if(fwrite($handle, $str) === false) {
              $modxError = true;
              $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
            }
          }

          // Write file footer
          if(!$modxError) {
            if(fwrite($handle, '</urlset>') === false) {
              $modxError = true;
              $smarty->assign('resultError', sprintf('Ошибка при записи в файл %s', $xmlFile));
            }
          }

          fclose($handle);

          // NEW CODE (Sitemap Index)
          $xmlFile = dirname($_SERVER['SCRIPT_FILENAME'])."/sitemap.xml";
          if (!$modxError) {
            $size = filesize($xmlFile);
            $resultFile = array("http://".CONF_SHOP_URL."/sitemap.xml" => sprintf('(%d Кб)', round($size/1000)));
          }
          
          if($total > MODX_WRITE_FILE_MAX) {
            if ($handle = @fopen($xmlFile, 'w')) {
              $str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
              for ($i=1; $i < $file_num + 1; $i++) { 
                $str .= "   <sitemap>\n      <loc>http://".CONF_SHOP_URL."/sitemap".$i.".xml</loc>\n      <lastmod>".strftime("%Y-%m-%d", time()+intval(CONF_TIMEZONE)*3600)."</lastmod>\n   </sitemap>\n";
                $f = dirname($_SERVER['SCRIPT_FILENAME'])."/sitemap".$i.".xml";
                $size = filesize($f);
                $resultFile["http://".CONF_SHOP_URL."/sitemap".$i.".xml"] = sprintf('(%d Кб)', round($size/1000));
              }
              $str .= "</sitemapindex>";
              fwrite($handle, $str);
            } 
            fclose($handle);
          }
          // END OF NEW CODE
          
          if (!$modxError) {
			
			if (isset($_POST["pingSE"])) {
				$resultMsg = "------------------------------------------<br \>";
				// Ping to Google Sitemaps
				if ( 200 === ($status=pingSitemaps('http://'.CONF_SHOP_URL.'/sitemap.xml','www.google.com','/webmasters/sitemaps/ping?sitemap=')) ) {
					$resultMsg .= "Пинг Google Sitemaps - <span style=\"color:green;\">OK</span>";
				} else {
					$resultMsg .= "Пинг Google Sitemaps - <span style=\"color:red;\">FAIL</span> (status code: $status)";
				}
				//Ping to Bing Webmaster
				if ( 200 === ($status=pingSitemaps('http://'.CONF_SHOP_URL.'/sitemap.xml','www.bing.com','/webmaster/ping.aspx?siteMap=')) ) {
					$resultMsg .= "<br \>Пинг Bing Webmasters - <span style=\"color:green;\">OK</span>";
				} else {
					$resultMsg .= "<br \>Пинг Bing Webmasters - <span style=\"color:red;\">FAIL</span> (status code: $status)";
				}
			}

		    $smarty->assign('resultMsg', $resultMsg);
		  }

        } else {
          $modxError = true;
          $smarty->assign('resultError', sprintf('Не могу создать или перезаписать файл %s', $xmlFile));
        }
        
    }
	
	// New code (Time-Date of XML-generator)
  $xmlFile = dirname($_SERVER['SCRIPT_FILENAME'])."/sitemap".$file_num.".xml";
	if (file_exists($xmlFile)) {
        $sitemap_date_modification = date("d.m.Y H:i:s.", filemtime($xmlFile));
    } else { 
        $ErrorEmpty = true;
        $smarty->assign('ErrorEmpty', sprintf('Файла %s не существует, требуется сгенерировать', $xmlFile));
	}
        $smarty->assign('time_sitemap_modification', $sitemap_date_modification); 

    $smarty->assign('admin_sub_dpt', 'xml_sitemap.tpl.html');
    $smarty->assign('resultFile', $resultFile);

	// New Code (XML Sitemap Tags)
	if(isset($_POST['tagsCatalog']) && !$modxError) {
    _setSettingOptionValue("CONF_SITEMAP_CATALOG_ON", $_POST["includeCatalog"]);
    _setSettingOptionValue("CONF_SITEMAP_PAGES_ON", $_POST["includeStatpages"]);
    _setSettingOptionValue("CONF_SITEMAP_NEWS_ON", $_POST["includeNews"]);

    _setSettingOptionValue("CONF_SITEMAP_CATALOG", $_POST["tagsCatalog"]);
    _setSettingOptionValue("CONF_SITEMAP_PRODUCT", $_POST["tagsProduct"]);
    _setSettingOptionValue("CONF_SITEMAP_PAGES", $_POST["tagsPages"]);
    _setSettingOptionValue("CONF_SITEMAP_NEWS", $_POST["tagsNews"]);

    _setSettingOptionValue("CONF_SITEMAP_CATALOG_FREQ", $_POST["tagsCatalogFreq"]);
    _setSettingOptionValue("CONF_SITEMAP_PRODUCT_FREQ", $_POST["tagsProductFreq"]);
    _setSettingOptionValue("CONF_SITEMAP_PAGES_FREQ", $_POST["tagsPagesFreq"]);
    _setSettingOptionValue("CONF_SITEMAP_NEWS_FREQ", $_POST["tagsNewsFreq"]);
    _setSettingOptionValue("CONF_SITEMAP_NEWS2_FREQ", $_POST["tagsNews2Freq"]);

    _setSettingOptionValue("CONF_SITEMAP_LASTMOD", $_POST["lastmod"]);
    _setSettingOptionValue("CONF_SITEMAP_PING", $_POST["pingSE"]);

    // Articles Addon
		_setSettingOptionValue("CONF_SITEMAP_ARTICLES", $_POST["tagsArticles"]);
    _setSettingOptionValue("CONF_SITEMAP_ARTICLES_ON", $_POST["includeArticles"]);
    _setSettingOptionValue("CONF_SITEMAP_ARTICLES_FREQ", $_POST["tagsArticlesFreq"]);
    _setSettingOptionValue("CONF_SITEMAP_ARTICLES2_FREQ", $_POST["tagsArticles2Freq"]);
	}
	$tagsarr = array('0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9');
  $tagsarr_freq = array('always','hourly','daily','weekly','monthly','yearly','never');
	$smarty->assign('tagsArr', $tagsarr);
  $smarty->assign('tagsArrFreq', $tagsarr_freq);
   
  }
}
?>
