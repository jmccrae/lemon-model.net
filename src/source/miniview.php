<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini");

$res=$settings["name"];

function convert($nt, $outformat, $prefix, $settings) {
    $rs=preg_replace('/"/','\\"',$settings["rappersettings"]);
    $namespaces="-f 'xmlns:lemon=\"http://www.monnet-project.eu/lemon#\"' -f 'xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"' -f 'xmlns:owl=\"http://www.w3.org/2002/07/owl#\"'  -f 'xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"' -f 'xmlns:lexinfo=\"http://lexinfo.net/ontology/2.0/lexinfo#\"' -f 'xmlns:skos=\"http://www.w3.org/2004/02/skos/core#\"'  " . $rs;
   $cmd = "rapper -q -i turtle -o $outformat -I $prefix $namespaces -";
   $descriptorspec = array(
     0 => array("pipe", "r"),
     1 => array("pipe", "w")
   );

   $process = proc_open($cmd, $descriptorspec, $pipes);

   if (is_resource($process)) {
    fwrite($pipes[0], $nt);
    fclose($pipes[0]);

    $content = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    return $content;
  } else {
    header('HTTP/1.0 500 Internal Server Error');
    echo "<h1>500 Internal Server Error</h1>";
    echo "Rapper was bad... fo' shizzle.";
    exit();
  }
}

if(!isset($_GET['uri'])) {
   header('HTTP/1.0 404 Not Found');
   echo "<h1>404 Not Found</h1>";
   echo "No uri";
   echo "The page that you have requested could not be found.";
   exit();
}	

$con = mysql_connect("localhost",$settings["user"],$settings["password"]);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db($settings["database"],$con);

// 1. Fetch the data from MySQL

$prefix = $settings["prefix"].$res."/";

$uri = $_GET['uri'];

if(!preg_match('/[A-Za-z0-9_+\-%]+/',$uri)) {
   header('HTTP/1.0 404 Not Found');
   echo "<h1>404 Not Found</h1>";
   echo "Bad characters".$_GET['uri']."\n";
   echo "The page that you have requested could not be found.";
   mysql_close($con);
   exit();
}

$uri = str_replace(" ","+",$uri);

$result = mysql_query("select * from $res where uri='$prefix$uri'");

if(!$result) {
   header('HTTP/1.0 404 Not Found');
   echo "<h1>404 Not Found</h1>";
   echo "uri=".$_GET['uri'];
   echo "The page that you have requested could not be found.";
mysql_close($con);
   exit();
}

$row = mysql_fetch_array($result);

if(!$row) {
   header('HTTP/1.0 404 Not Found');
   echo "<h1>404 Not Found</h1>";
   echo "uri=".$_GET['uri'];
   echo "The page that you have requested could not be found.";
mysql_close($con);
   exit();
}


  $xslt = new XSLTProcessor();
  $xslDoc = new DOMDocument();
  $xslDoc->load("rdf2minihtml.xsl");
  $xslt->importStylesheet($xslDoc);
  $rdfxml = convert($row['nt'], "rdfxml-abbrev",$prefix, $settings);
  echo $xslt->transformToXml(new SimpleXMLElement($rdfxml));
  
  
mysql_close($con);

?>
