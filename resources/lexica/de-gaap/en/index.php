<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

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
	$cmd = "rapper -q -i rdfxml -o $outformat -I $prefix -";
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

$prefix = "http://lemon-model/net/lexica/de-gaap/en";

if(file_exists($_GET['uri'] . "." . $type)) {
	include $_GET['uri'] . "." . $type;
	exit();
} else if(file_exists($_GET['uri']. ".rdf")) {
	$rdfxml = file_get_contents($_GET['uri']. ".rdf");
	if($type == 'ttl') {
		$turtle = convert($rdfxml, "turtle",$prefix); 
		header('Content-type: text/turtle');
		echo $turtle;
	} elseif($type == 'nt') {
		$nt = convert($rdfxml,"ntriples",$prefix);
		header('Content-type: text/plain');
		echo $nt;
	} elseif($type == 'html') {
		include '../../../header.htmlfrag';
		$xslt = new XSLTProcessor();
		$xslDoc = new DOMDocument();
		$xslDoc->load("../rdf2html.xsl");
		$xslt->importStylesheet($xslDoc);
		echo $xslt->transformToXml(new SimpleXMLElement($rdfxml));
		include '../license.htmlfrag';
		include '../../../footer.htmlfrag';
	}	
} else {
   header('HTTP/1.0 404 Not Found');
   echo "<h1>404 Not Found</h1>";
   echo "No uri";
   echo "The page that you have requested could not be found.";
   exit();
}
?>
