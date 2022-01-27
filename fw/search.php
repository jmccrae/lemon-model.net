<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini");

$res=$settings["name"];

$con = mysqli_connect("127.0.0.1",$settings["user"],$settings["password"],$settings["database"]);

if(!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

if(isset($_GET['search'])) {
  $search = mysqli_real_escape_string($con,$_GET['search']);
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
  $offset = mysqli_real_escape_string($con,$_GET['offset']);
}

echo "<h2>".$displayNames[$res]."</h2>";
$result = mysqli_query($con,"select uri, label from $res where match (label) against ('%".$search."%') order by length(label) asc limit 20 offset ". $offset);

echo "<table>";
while($row = mysqli_fetch_array($result)) {
  echo "<tr><td><a href='" . $row['uri'] . "'>" . $row['uri'] . "</a></td><td>";
  echo str_replace("_","",$row['label']) . "</td></tr>";
}
echo "</table>";
$result_count=mysqli_num_rows($result);
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
