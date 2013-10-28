<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

if(!file_exists("settings.ini")) {
    exit(404);
}

$settings=parse_ini_file("settings.ini");

if(!isset($_GET['uri'])) {
    $uri = "_index";
} else {
    $uri = $_GET['uri'];
}   

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

$source_editor_uri=$uri;
include '../../../header.htmlfrag';
echo "<h1>History</h1>";
$output=array();
exec("git log -v $uri.ttl",$output);
foreach($output as $line) {
    echo '<div>'.htmlspecialchars($line).'</div>';
}
include '../../../footer.htmlfrag';
?>
