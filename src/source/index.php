<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

if(!file_exists("settings.ini")) { // We are not in a user-created lexicon
    include "../header.htmlfrag";
?>
<h1>Lemon Source Editor</h1>

Blurb
<?php
    include "../footer.htmlfrag";
} else {
    $settings=parse_ini_file("settings.ini");

    $res=$settings["name"];
    $is_editor=isset($_SESSION["username"]) && in_array($_SESSION["username"],explode(",",$settings["editor"]));

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

    function convert($nt, $outformat, $prefix, $settings) {
        $rs=preg_replace('/"/','\\"',$settings["rappersettings"]);
        $namespaces="-f 'xmlns:lemon=\"http://www.monnet-project.eu/lemon#\"' -f 'xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"' -f 'xmlns:owl=\"http://www.w3.org/2002/07/owl#\"'  -f 'xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"' -f 'xmlns:lexinfo=\"http://lexinfo.net/ontology/2.0/lexinfo#\"' -f 'xmlns:skos=\"http://www.w3.org/2004/02/skos/core#\"' " . $rs;
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
        $uri = "_index";
    } else {
        $uri = $_GET['uri'];
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

    if(file_exists($uri . "." . $type)) {
        include $uri . "." . $type;
        exit();
    }

    // 1. Fetch the data from MySQL

    $prefix = $settings["prefix"].$res."/";

    if(!preg_match('/[A-Za-z0-9_+\-%]+/',$uri)) {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 Not Found</h1>";
        echo "Bad characters".$_GET['uri']."\n";
        echo "The page that you have requested could not be found.";
        exit();
    }

    $uri = str_replace(" ","+",$uri);

    if(!file_exists($uri . ".ttl")) {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 Not Found</h1>";
        echo "uri=".$_GET['uri']."\n";
        echo "The page that you have requested could not be found.";
        exit();
    }

    $data = file_get_contents($uri.".ttl");

    if(!$data) {
        header('HTTP/1.0 404 Not Found');
        echo "<h1>404 Not Found</h1>";
        echo "uri=".$_GET['uri']."\n";
        echo "The page that you have requested could not be found.";
        exit();
    }

    if($type == "rdf") {
        $rdfxml = convert($data/*.file_get_contents("license-$res.nt")*/, "rdfxml-abbrev",$prefix, $settings); 
        header('Content-type: application/rdf+xml');
        echo $rdfxml;
    } else if($type == "ttl") {
        $turtle = convert($data/*.file_get_contents("license-$res.nt")*/, "turtle",$prefix, $settings); 
        header('Content-type: text/turtle');
        echo $turtle;
    } else if($type == "nt") {
        $nt = convert($data/*.file_get_contents("license-$res.nt")*/, "ntriples",$prefix, $settings); 
        header('Content-type: text/plain');
        echo $nt;
    } else {
        $source_editor_uri=$uri;
        include '../../../header.htmlfrag';
        $xslt = new XSLTProcessor();
        $xslDoc = new DOMDocument();
        $xslDoc->load("rdf2html.xsl");
        $xslt->importStylesheet($xslDoc);
        $rdfxml = convert($data, "rdfxml-abbrev",$prefix, $settings);
        echo $xslt->transformToXml(new SimpleXMLElement($rdfxml));
        if($uri == "_index" && $is_editor) {
?>
  <div>
    <a href="newentry.php">
        <div style="display:inline-block;width:18px;height:15px;background-image:url('/img/glyphicons-halflings.png');background-position:0px -95px"></div>
        Add a new entry
    </a>
  </div>
<?php
        }
        if(file_exists("license-$res.htmlfrag")) {
            include "license-$res.htmlfrag";
        }
        include '../../../footer.htmlfrag';
    }
}
?>
