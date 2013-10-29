<?php
session_start();
date_default_timezone_set("UTC");

$settings=parse_ini_file("settings.ini");
$lang=$settings["language"];

include '../../../header.htmlfrag';
$is_editor=isset($_SESSION["username"]) && in_array($_SESSION["username"],explode(",",$settings["editor"]));
if(!$is_editor) {
?>
    <h1>Forbidden</h1>
    You cannot create entries in this lexicon
<?php
} else {
    $userName= isset($_SESSION["username"]) ? $_SESSION["username"] : $_SERVER["REMOTE_ADDR"];

    if($_GET["lemma"]) {
        $lemma=str_replace("\"","\\\"",$_GET["lemma"]);
        $url=urlencode($lemma);
        $url2=$url;
        $n=1;
        while(file_exists("$url.ttl") || $url == "_index") {
            $url=$url2.$n;
            $n++;
        }
        file_put_contents("$url.ttl","@prefix lemon: <http://www.monnet-project.eu/lemon#>. \n\n<$url> a lemon:LexicalEntry ;\n lemon:canonicalForm <$url#CanonicalForm> . \n\n<$url#CanonicalForm> a lemon:Form ; \n  lemon:writtenRep \"$lemma\"@$lang . \n");
        file_put_contents("_index.ttl","<> lemon:entry <$url> .\n",FILE_APPEND);
        exec("git add $url.ttl");
        $comment = htmlspecialchars(isset($_GET["comment"]) ? $_GET["comment"] : "",ENT_QUOTES);
        $comment = str_replace("!","&#33;",$comment);
        exec("git commit -am \"$comment [$userName at ". date("Y-m-d H:i:s") ."]\"");
        echo "<script>window.location='$url'</script>";
    } else {
?>
    <h1>Add a new entry</h1>
    <form>
    <label for="lemma">Lemma</label><input type="text" name="lemma"/><br/>
    <label for="comment">Comment</label><input type="text" value="Lexical entry added" name="comment" class="source-comment"/><br/>
    <input type="submit" value="Create"/>
    </form>
<?php
    }
}
include '../../../footer.htmlfrag';
?>
