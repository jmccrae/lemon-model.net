<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
session_start();
date_default_timezone_set("UTC");

$settings=parse_ini_file("settings.ini");

$is_editor=isset($_SESSION["username"]) && in_array($_SESSION["username"],explode(",",$settings["editor"]));
$userName= isset($_SESSION["username"]) ? $_SESSION["username"] : $_SERVER["REMOTE_ADDR"];

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
if(isset($_GET["revert"]) && $is_editor) {
    $revision = $_GET["revert"];
    exec("git checkout $revision $uri.ttl");
    exec("git commit -am \"Reverted to $revision [$userName at " . date("Y-m-d H:i:s") ."]\"");
    echo "Reverted to $revision";
}

$output=array();
exec("git log --oneline $uri.ttl",$output);
?>
<form>
<table>
    <tr>
        <th>From</th>
        <th>To</th>
        <th>Commit</th>
        <th>Message</th>
    </tr>
<?php
$fromCommit = isset($_GET["from-commit"]) ? $_GET["from-commit"] : "";
$toCommit = isset($_GET["to-commit"]) ? $_GET["to-commit"] : "";
foreach($output as $line) {
    $id = substr($line,0,7);
    $msg = substr($line,8);
    echo "<tr>";
    if($fromCommit === $id) {
        echo "<td><input type='radio' name='from-commit' value='$id' checked/></td>";
    } else {
        echo "<td><input type='radio' name='from-commit' value='$id'/></td>";
    }
    if($toCommit === $id) { 
        echo "<td><input type='radio' name='to-commit' value='$id' checked/></td>";
    } else {
        echo "<td><input type='radio' name='to-commit' value='$id'/></td>";
    }
    echo "<td><span class='history-commit'>$id</span></td>";
    echo "<td>".htmlspecialchars($msg).'</td>';
    echo "</tr>";
}
?>
</table>
<input type="text" name="uri" value="<?php echo $uri;?>" style="display:none;"/>
<?php
    if($fromCommit !== "" && $toCommit !== "") {
        $output = array();
        exec("git diff $fromCommit $toCommit $uri.ttl",$output);
        foreach($output as $line) {
            if(substr($line,0,1) === "-" && substr($line,0,3) !== "---") {
                echo '<del>'.htmlspecialchars(substr($line,1)).'</del><br/>';
            } else if(substr($line,0,1) === "+" && substr($line,0,3) !== "+++") {
                echo '<ins>'.htmlspecialchars(substr($line,1)).'</ins><br/>';
            }
        }
        if($is_editor) {
?>
    <a href="history.php?revert=<?php echo $fromCommit; ?>&uri=<?php echo $uri; ?>">Revert Changes</a><br/>
<?php
        }
    }
?>
<input type="submit" value="Diff"/>
</form>
<?php
include '../../../footer.htmlfrag';
?>
