<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
date_default_timezone_set("UTC");

if(!file_exists("settings.ini")) {
    exit(404);
}

$settings=parse_ini_file("settings.ini");

$error="";

$is_editor=isset($_SESSION["username"]) && in_array($_SESSION["username"],explode(",",$settings["editor"]));
$userName= isset($_SESSION["username"]) ? $_SESSION["username"] : $_SERVER["REMOTE_ADDR"];

function validate($triples,$prefix,$res,$uri) {
    global $error;
    $cmd = "python ../../validate.py $prefix$res/$uri";
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        fwrite($pipes[0], $triples);
        fclose($pipes[0]);

        $content = stream_get_contents($pipes[1]);
        $error=nl2br(htmlspecialchars(stream_get_contents($pipes[2])));
        fclose($pipes[1]);

        return $content;
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo "<h1>500 Internal Server Error</h1>";
        exit();
    }
}



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

$data = file_get_contents($uri.".ttl");

if(!$data) {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>404 Not Found</h1>";
    echo "uri=".$_GET['uri']."\n";
    echo "The page that you have requested could not be found.";
    exit();
}

$source_editor_uri=$uri;

include '../../../header.htmlfrag';

if(!$is_editor) {
?>
    <h1>Forbidden</h1>
    You do not have permission to edit this page
<?php
} else {
    if(isset($_GET["contents"])) {
        $valid_contents = validate($_GET["contents"],$settings["prefix"],$settings["name"],$uri == "_index" ? "" : $uri);
        if($valid_contents != "" && $error == "") {
            # This may be a bit error prone, but oh well
            $valid_contents = str_replace("<".$settings["prefix"].$settings["name"]."/","<",$valid_contents);
            file_put_contents("$uri.ttl",$valid_contents);
            exec("git commit -am \"Lexical entry modified by $userName at ". date("Y-m-d H:i:s") ."\"");
            echo "<script>window.location='index.php?uri=$uri';</script>";
        } 
    } 
    if($uri == "_index") {
?>
    <h1>Editing lexicon</h1>
<?php
    } else {
?>
    <h1>Editing entry: <?php echo $uri; ?></h1>
<?php 
    } 
    echo $error;
?>
    <form>
        <textarea name="contents" class="code-textarea"><?php echo isset($_GET["contents"]) ? $_GET["contents"] : $data; ?></textarea><br/>
        <input type="text" value="<?php echo $uri?>" name="uri" style="display:none;"/>
        <input type="submit" value="Update"/>
    </form>
<?php
}
include '../../../footer.htmlfrag';
?>
