<?php
/*
  ������ "XML Sitemap ��� ShopCMS"
  2011 (c) http://trickywebs.org.ua/
  ����������� ��������� - soulmare@gmail.com
  �������� - MIT http://www.opensource.org/licenses/mit-license.php
*/


// Check engine version
if(is_dir(dirname($_SERVER['SCRIPT_FILENAME']) . '/core'))
  define('MODX_MODERN_ENGINE', 1);
else
  define('MODX_MODERN_ENGINE', 0);

define('MODX_READ_BUFFER_SIZE', 2048);


function http_404() {
  header('HTTP/1.0 404 Not Found');
  header('Content-Type: text/html; charset=utf-8');
  echo '<html>
  <head>
    <title>404 �������� �� �������</title>
  </head>
  <body>
    <h1>404 �������� �� �������</h1>
    <p>����� ����� �� �������������</p>
  </body>
</html>';
}


function http_200() {
  header('HTTP/1.0 200 OK');
  header('Content-Type: text/xml; charset=utf-8');
}


function http_500($error) {
  header('HTTP/1.0 500 Internal server error');
  header('Content-Type: text/html; charset=utf-8');
  echo '<html>
  <head>
    <title>500 ���������� ������ �������</title>
  </head>
  <body>
    <h1>500 ���������� ������ �������</h1>
    <p>' . htmlspecialchars($error) . '</p>
  </body>
</html>';
}


$xmlFile = dirname($_SERVER['SCRIPT_FILENAME']) . '/sitemap.xml';


if(is_file($xmlFile) && ($fileSize = filesize($xmlFile))) {

  // Read file
  if($handle = @fopen($xmlFile, 'r')) {

    // Response headers
    http_200();
    header('Content-Length: ' . $fileSize);
    
    while(!feof($handle))
      echo fread($handle, MODX_READ_BUFFER_SIZE);

  } else
    http_500('������ ��� �������� XML �����');

} else http_404();


?>
