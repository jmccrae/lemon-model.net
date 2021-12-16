<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$settings=parse_ini_file("settings.ini");

$con = mysqli_connect("localhost",$settings["user"],$settings["password"],$settings["database"]);

if(!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

if(isset($_GET['search'])) {
  $search = mysqli_real_escape_string($con, $_GET['search']);
} else {
  echo "No search params";
  exit();
}

mysqli_select_db($con, "uby_lemon");

include 'wn/header.htmlfrag';

$displayNames = array(
  "wn" => "Princeton WordNet 3.0",
  "fn" => "FrameNet",
  "vn" => "VerbNet",
  "WktDE" => "Wiktionary.de",
  "ow_eng" => "OmegaWiki English",
  "ow_deu" => "OmegaWiki German"
);

$resources = array("wn","fn","vn","WktDE","ow_eng","ow_deu");


if(isset($_GET['res']) && in_array($_GET['res'],$resources)) {
  $resources = array($_GET['res']);
}

$limit=20;

$offset=0;

if(isset($_GET['offset']) && is_numeric($_GET['offset'])) {
  $offset = mysqli_real_escape_string($_GET['offset'],$con);
}



foreach($resources as $res) {
  echo "<h2>".$displayNames[$res]."</h2>";
  $result = mysqli_query($con, "select uri, label from $res where label like '%".$search."%' order by length(label) asc limit 20 offset ". $offset);

  echo "<table>";
  while($row = mysqli_fetch_array($result)) {
    echo "<tr><td><a href='" . $row['uri'] . "'>" . $row['uri'] . "</a></td><td>";
    echo $row['label'] . "</td></tr>";
  }
  echo "</table>";
  $result_count=mysqli_num_rows($result);
  echo "Results: " . $offset . "-" . ($offset + $result_count) . " ";
  if($result_count >= 20) {
    echo "<a href='search.php?search=$search&res=$res&offset=".($offset + 20)."'a>Next</a>";
  }
 }

include 'wn/footer.htmlfrag';

?>
