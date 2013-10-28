<?php
session_start();

$SOURCE_HOME="/var/www/html/source";

include "../header.htmlfrag";
if(!isset($_SESSION["username"])) {
    echo("<h1>Login required</h1>");
    echo("<div class='login_error'>You must be logged-in to create a lexicon, please log in now</div>");
    echo("<div><a href='/login.php'>Log in</a></div>");
} else if(isset($_GET["lexicon_name"]) && isset($_GET["language"])) {
    if(!preg_match("/^[A-Za-z0-9 ]+$/",$_GET["lexicon_name"])) {
        echo("<h1>Could not create lexicon</h1>");
        echo("<div class='login_error'>Please use only ASCII alphanumeric characters in the lexicon name</div>");
    } else if(!preg_match("/^[A-Za-z]{2,3}$/",$_GET["language"])) {
?>
  <h1>Could not create lexicon</h1>
  <div class='login_error'>The language stated was not a valid ISO-639 code, here are the values for common languages:</div>


  <table>
     <tr>
        <th>Language</th>
        <th>Code</th>
     </tr>
     <tr><td>English</td><td>eng</td></tr>
     <tr><td>Chinese</td><td>zho</td></tr>
     <tr><td>Spanish</td><td>spa</td></tr>
     <tr><td>Hindi</td><td>hin</td></tr>
     <tr><td>Arabic</td><td>ara</td></tr>
     <tr><td>Portuguese</td><td>por</td></tr>
     <tr><td>Russian</td><td>rus</td></tr>
     <tr><td>Japanese</td><td>jpn</td></tr>
     <tr><td>German</td><td>deu</td></tr>
     <tr><td>Korean</td><td>kor</td></tr>
     <tr><td>French</td><td>fra</td></tr>
     <tr><td>Turkish</td><td>tur</td></tr>
     <tr><td>Italian</td><td>ita</td></tr>
     <tr><td>Polish</td><td>pol</td></tr>
     <tr><td>Dutch</td><td>nld</td></tr>
  </table>
<?php
    } else if(file_exists($SOURCE_HOME ."/". $_SESSION["username"]."/".$_GET["lexicon_name"])) {
        echo("<h1>Could not create lexicon</h1>");
        echo("<div class='login_error'>You already have a lexicon with this name</div>");
    } else {
        $trgDir = $SOURCE_HOME ."/". $_SESSION["username"]."/".$_GET["lexicon_name"];
        if(!mkdir($trgDir,0777,true)) {
            echo "Failed to make directory";
        } else {
            $lexiconName=$_GET["lexicon_name"];
            $userName=$_SESSION["username"];
            $language=$_GET["language"];
            symlink("../../index.php",$trgDir."/index.php");
            symlink("../../newentry.php",$trgDir."/newentry.php");
            symlink("../../miniview.php",$trgDir."/miniview.php");
            symlink("../../rdf2html.xsl",$trgDir."/rdf2html.xsl");
            symlink("../../rdf2minihtml.xsl",$trgDir."/rdf2minihtml.xsl");
            symlink("../../rdfbl2html.xsl",$trgDir."/rdfbl2html.xsl");
            symlink("../../local.css",$trgDir+"/local.css");
            symlink("../../htaccess",$trgDir+"/.htaccess");

            exec("git init $trgDir/");            
            file_put_contents($trgDir."/_index.ttl","@prefix lemon: <http://www.monnet-project.eu/lemon#> .\n\n<> a lemon:Lexicon ;\n  lemon:language \"$language\" .\n");
            file_put_contents($trgDir."/settings.ini","[resource]\nname=$lexiconName\nrappersettings=\"--feature xmlns:$lexiconName=\\\"http://lemon-model.net/source/$userName/$lexiconName/\\\"\"\n".
                "prefix=http://lemon-model.net/source/$userName/\nlexicon=\n\nlanguage=$language\neditor=$userName\n");
            chdir($trgDir);
            exec("git add _index.ttl");
            exec("git add settings.ini");
            exec("git commit -m \"Lexicon created by $userName at " . date("Y-m-d H:i:s") ."\"");
            echo("OK<script>window.location='/source/$userName/$lexiconName/'</script>");
        }
    }
} else {
?>
<h3>Create a new lexicon</h3>
<form>
  <label for="lexicon_name">Lexicon name</label><input type="text" name="lexicon_name"/><br/>
  <label for="language">Language (ISO code, e.g., "eng")</label><input type="text" name="language"/><br/>
  <input type="submit" value="Create"/>
</form>
<?php
}
include "../footer.htmlfrag"
?>
