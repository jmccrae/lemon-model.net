<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini")

$res=$settings["name"];

function getBestSupportedMimeType($mimeTypes = null) {
    // Values will be stored in this array
    $AcceptTypes = Array ();

    // Accept header is case insensitive, and whitespace isn’t important
    $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
    // divide it into parts in the place of a ","
    $accept = explode(',', $accept);
    foreach ($accept as $a) {
        // the default quality is 1.
        $q = 1;
        // check if there is a different quality
        if (strpos($a, ';q=')) {
            // divide "mime/type;q=X" into two parts: "mime/type" i "X"
            list($a, $q) = explode(';q=', $a);
        }
        // mime-type $a is accepted with the quality $q
        // WARNING: $q == 0 means, that mime-type isn’t supported!
        $AcceptTypes[$a] = $q;
    }
    arsort($AcceptTypes);

    // if no parameter was passed, just return parsed data
    if (!$mimeTypes) return $AcceptTypes;

    $mimeTypes = array_map('strtolower', (array)$mimeTypes);

    // let’s check our supported types:
    foreach ($AcceptTypes as $mime => $q) {
       if ($q && in_array($mime, $mimeTypes)) return $mime;
    }
    // no mime-type found
    return null;
}

function convert($nt, $outformat, $prefix) {
   $namespaces="-f 'xmlns:lemon=\"http://www.monnet-project.eu/lemon#\"' -f 'xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"' -f 'xmlns:owl=\"http://www.w3.org/2002/07/owl#\"'  -f 'xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" -f 'xmlns:lexinfo=\"http://lexinfo.net/ontology/2.0/lexinfo#\" " . $settings["rappersettings"];
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


// Perform content negotiation

if(!isset($_GET['uri'])) {
   header('HTTP/1.0 404 Not Found');
   echo "<h1>404 Not Found</h1>";
   echo "No uri";
   echo "The page that you have requested could not be found.";
   exit();
}	

if(isset($_GET['type'])) {
   $type = $_GET['type'];
   if($type != 'rdf' && $type != 'ttl' && $type != 'nt' && $type != 'html') {
     $type = 'html';
   }
} else {
   $accept_type = getBestSupportedMimeType(Array ('application/rdf+xml', 'text/turtle', 'application/xhtml+xml', 'text/html', 'text/plain'));
   if($accept_type == 'application/rdf+xml') {
     $type = 'rdf';
   } elseif($accept_type == 'text/turtle') {
     $type = 'ttl';
   } elseif($accept_type == 'text/plain') {
     $type = 'nt';
   } else {
     $type = 'html';
   }
}

if(file_exists($_GET['uri'] . "." . $type)) {
	include $_GET['uri'] . "." . $type;
	exit();
}

$con = mysql_connect("localhost",$settings["user"],$settings["password"]);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

mysql_select_db($settings["database"],$con);

// 1. Fetch the data from MySQL

$prefix = $settings["prefix"].$res."/";

$result = mysql_query("select * from $res where uri='" . mysql_real_escape_string($prefix . ($_GET['uri']))."'");

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



if($type == "rdf") {
  $rdfxml = convert($row['nt'] . $row['back_nt'] .file_get_contents("license-$res.nt"), "rdfxml-abbrev",($prefix.($_GET['uri']))); 
  header('Content-type: application/rdf+xml');
  echo $rdfxml;
} else if($type == "ttl") {
  $turtle = convert($row['nt'] . $row['back_nt'] .file_get_contents("license-$res.nt"), "turtle",$prefix); 
  header('Content-type: text/turtle');
  echo $turtle;
} else if($type == "nt") {
  header('Content-type: text/plain');
  echo $row['nt'];
  echo $row['back_nt'];
  echo file_get_contents("license-$res.nt");
} else {
  include 'header.htmlfrag';
  $xslt = new XSLTProcessor();
  $xslDoc = new DOMDocument();
  $xslDoc->load("rdf2html.xsl");
  $xslt->importStylesheet($xslDoc);
  $rdfxml = convert($row['nt'], "rdfxml-abbrev",$prefix);
  echo $xslt->transformToXml(new SimpleXMLElement($rdfxml));
  if(!preg_match("/^\s*$/",$row['back_nt'])) {
    $xslt = new XSLTProcessor();
    $xslBlDoc = new DOMDocument();
    $xslBlDoc->load("rdfbl2html.xsl");
    $xslt->importStylesheet($xslBlDoc);
    $rdfxml = convert($row['back_nt'], "rdfxml-abbrev",$prefix);
    echo $xslt->transformToXml(new SimpleXMLElement($rdfxml));
  }
  ?>
        </div>
        <div class="pageBottom">
            <br/><div width="1000px" style="border-top: #ddd 3px ridge;">&nbsp;</div>
            <?php
  include "license-$res.htmlfrag";
  include 'footer.htmlfrag';
}

mysql_close($con);
?>
