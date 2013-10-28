<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();

if(!file_exists("settings.ini")) {
    exit(404);
}

$settings=parse_ini_file("settings.ini");

$res=$settings["name"];

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

if(isset($_GET["contents"])) {
    if(!file_exists("$uri.talk")) {
        file_put_contents("$uri.talk","");
    }

    if(isset($_SESSION["username"])) {
        $username=$_SESSION["username"];
    } else {
        $username=$_SERVER['REMOTE_ADDR'];
    }
    $id=rand();
    $text=$_GET["contents"];
    file_put_contents("$uri.talk",
        "<!-- COMMENT $id -->\n".
        "<h5 class='talkpage-user'><a href='/user.php?id=$username'>$username</a> wrote</h5>\n".
        "<div class='talkpage-comment'>$text</div>\n".
        "<a href='talk.php?delete=$id&uri=$uri' class='talkpage-delete'>Delete this comment</a>\n".
        "<!-- END $id -->\n",FILE_APPEND);
} else if(isset($_GET["delete"]) && file_exists("$uri.talk")) {
    $id=$_GET["delete"];
    $f = fopen("$uri.talk","r");
    $in_comment = 0;
    $new_contents = "";
    while(!feof($f)) {
        $line = fgets($f);
        if(!$in_comment && $line == "<!-- COMMENT $id -->\n") {
            $in_comment = 1;
        } else if($in_comment && $line == "<!-- END $id -->\n") {
            $in_comment = 0;
        } else if(!$in_comment) {
            $new_contents = $new_contents. $line;
        }
    }
    fclose($f);
    file_put_contents("$uri.talk",$new_contents);
}


$source_editor_uri=$uri;
include '../../../header.htmlfrag';

if($uri == "_index") {
?>
    <h1>Talk page for lexicon</h1>
<?php
} else {
?>
    <h1>Talk page for entry: <?php echo $uri; ?></h1>
<?php
}
if(file_exists("$uri.talk")) {
    include "$uri.talk";
} else {
    echo "No comments yet!";
}
?>
    <form>
        <textarea name="contents" class="comment-textarea"></textarea><br/>
        <input type="text" name="uri" style="display:none;" value="<?php echo $uri;?>"/>
        <input type="submit" value="Comment"/>
    </form>
<?php
include '../../../footer.htmlfrag';
?>
