<?php
session_start();
date_default_timezone_set("UTC");

$settings=parse_ini_file("settings.ini");
$lang=$settings["language"];

include '../../../header.htmlfrag';

$error = "";

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
        file_put_contents("$url.ttl","@prefix lemon: <http://lemon-model.net/lemon#>. \n\n<$url> a lemon:LexicalEntry ;\n lemon:canonicalForm <$url#CanonicalForm> . \n\n<$url#CanonicalForm> a lemon:Form ; \n  lemon:writtenRep \"$lemma\"@$lang . \n");
        file_put_contents("_index.ttl","<> lemon:entry <$url> .\n",FILE_APPEND);
        exec("git add $url.ttl");
        $comment = htmlspecialchars(isset($_GET["comment"]) ? $_GET["comment"] : "",ENT_QUOTES);
        $comment = str_replace("!","&#33;",$comment);
        exec("git commit -am \"$comment [$userName at ". date("Y-m-d H:i:s") ."]\"");
        echo "<script>window.location='$url'</script>";
    } else if($_GET["pattern"]) {
        echo callLemonPatterns($_GET["pattern"]);
        echo $error;
    } else {
?>
    <h1>Add a new entry</h1>

    <span class="newentry" id="newentry-simple-lemma">
    <h3 onclick="$('.newentry').addClass('newentry-hide');$('#newentry-simple-lemma').removeClass('newentry-hide');">
     <div class="hideshowarrow"></div>
        Create entry by lemma</h3>
        <form>
            <label for="lemma">Lemma</label><input type="text" name="lemma"/><br/>
            <label for="comment">Comment</label><input type="text" value="Lexical entry added" name="comment" class="source-comment"/><br/>
            <input type="submit" value="Create"/>
        </form>
    </span>

<!--    <span class="newentry-hide newentry" id="newentry-simple-pattern">
    <h3 onclick="$('.newentry').addClass('newentry-hide');$('#newentry-simple-pattern').removeClass('newentry-hide');">
         <div class="hideshowarrow"></div>
         Create entry by pattern</h3>
        <form>
        <label for="pattern">Pattern</label><textarea name="pattern" class="code-textarea">Lexicon(&lt;<?php echo $settings["prefix"] . $settings["name"]."/&gt;,\"" . $settings["language"] . "\"";?>,_Entry code goes here_)</textarea><br/>
            <label for="comment">Comment</label><input type="text" value="Lexical entry added" name="comment" class="source-comment"/><br/>
            <input type="submit" value="Create"/>
        </form>
    </span>-->
<?php 
    }
}
include '../../../footer.htmlfrag';
?>
