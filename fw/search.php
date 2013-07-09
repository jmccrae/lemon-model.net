<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini");

$res=$settings["name"];

$con = mysql_connect("localhost",$settings["user"],$settings["password"]);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

if(isset($_GET['search'])) {
  $search = mysql_real_escape_string($_GET['search']);
  $ss = preg_split("/ /",$search);
  for($i = 0; $i < sizeof($ss); $i++) {
    while(strlen($ss[$i]) < 4) {
      $ss[$i] = $ss[$i] . "_";
    }
  }
  $search = implode(" ",$ss);
} else {
  echo "No search params";
  exit();
}

mysql_select_db($settings["database"],$con);

include 'header.htmlfrag';

$displayNames = array(
  "pwn" => "Princeton WordNet 3.0",
  "dbpedia_en" => "DBpedia English",
  "fn" => "FrameNet",
  "ow_eng" => "OmegaWiki English",
  "ow_deu" => "OmegaWiki German",
  "WktEN" => "Wiktionary English",
  "WktDE" => "Wiktionary German",
  "wn" => "WordNet"
);


$limit=20;

$offset=0;

if(isset($_GET['offset']) && is_numeric($_GET['offset'])) {
  $offset = mysql_real_escape_string($_GET['offset']);
}

echo "<h2>".$displayNames[$res]."</h2>";
$result = mysql_query("select uri, label from $res where match (label) against ('%".$search."%') order by length(label) asc limit 20 offset ". $offset);

echo "<table>";
while($row = mysql_fetch_array($result)) {
  echo "<tr><td><a href='" . $row['uri'] . "'>" . $row['uri'] . "</a></td><td>";
  echo str_replace("_","",$row['label']) . "</td></tr>";
}
echo "</table>";
$result_count=mysql_num_rows($result);
if($result_count != 0 || $offset != 0) {
    echo "Results: " . ($offset+1) . "-" . ($offset + $result_count) . " ";
} else {
    echo "No Results";
}
if($result_count >= 20) {
  echo "<a href='search.php?search=$search&res=$res&offset=".($offset + 20)."'a>Next</a>";
}

include 'footer.htmlfrag';

?>
