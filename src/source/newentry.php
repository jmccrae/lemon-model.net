<?php
session_start();

include '../../../header.htmlfrag';
if($_GET["lemma"]) {
    $lemma=str_replace("\"","\\\"",$_GET["lemma"]);
    $url=urlencode($lemma);
    file_put_contents("$url.ttl","@prefix lemon: <http://www.monnet-project.eu/lemon#>. \n\n<$url> a lemon:LexicalEntry ;\n lemon:canonicalForm <#CanonicalForm> . \n\n<#CanonicalForm> a lemon:Form ; \n  lemon:writtenRep \"$lemma\"@eng");
    echo "<script>window.location='$url'</script>";
} else {
?>
    <h1>Add a new entry</h1>
    <form>
        <label for="lemma">Lemma</label><input type="text" name="lemma"/><br/>
        <input type="submit" value="Create"/>
    </form>
<?php
}
    include '../../../footer.htmlfrag';
?>
