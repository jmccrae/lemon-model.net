<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

  if(!isset($_GET['search']) && !isset($_GET['text_search'])) {
     include 'header.htmlfrag';
?>
  <div class="pageMid">
  <form action="sparql.php" method="get">
    <h3>Query in SPARQL</h3><br/>
    <textarea name="search" rows="10" cols="50">
PREFIX lemon: &lt;http://lemon-model.net/lemon#&gt;
select * where {
  	?s lemon:writtenRep "cat"@eng
}
    </textarea><br/>
    <input type="submit" value="Search"/><br/>
  </form>
  </div>
<?php
     include 'footer.htmlfrag';
     exit();
  }

  if(isset($_GET['search'])) {
      $ch = curl_init();

      curl_setopt($ch,CURLOPT_URL,'http://vtentacle:8000/sparql/?query='.urlencode($_GET['search']));
      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

      include 'header.htmlfrag';
      echo "<div class='pageMid'>";
      $cr = curl_exec($ch);
      if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
          $xslt = new XSLTProcessor();
          $xslDoc = new DOMDocument();
          $xslDoc->load("sparql2html.xsl");
          $xslt->importStylesheet($xslDoc);
          echo $xslt->transformToXml(new SimpleXMLElement($cr));
      } else {
  	  echo "SPARQL request failed:<br/>";
          echo $cr;

?>
  <form action="sparql.php" method="get">
    <h3>Query in SPARQL</h3><br/>
    <textarea name="search" rows="10" cols="50">
<?php echo $_GET['search'] ?>
    </textarea><br/>
    <input type="submit" value="Search"/><br/>
  </form>
<?php
      }
  
      curl_close($ch);
      echo "</div>";
  } else if(isset($_GET['text_search'])) {
      $allsettings = array("lexica/uby/fn/settings.ini",
          "lexica/uby/ow_deu/settings.ini","lexica/uby/ow_eng/settings.ini",
          "lexica/uby/vn/settings.ini","lexica/uby/WktDE/settings.ini",
          "lexica/uby/WktEN/settings.ini","lexica/uby/wn/settings.ini",
          "lexica/dbpedia_en/settings.ini");
    $displayNames = array(
"WktDE" => "Wiktionary.de",
"WktEN" => "Wiktionary.en",
"fn" => "FrameNet",
"dbpedia_en" => "DBpedia",
"ow_eng" => "OmegaWiki English",
"ow_deu" => "OmegaWiki German",
"wn" => "Princeton WordNet",
"vn" => "VerbNet"
    );
    include 'header.htmlfrag';

    for($i = 0; $i < count($allsettings); ++$i) {
        $settings=parse_ini_file($allsettings[$i]);
        $res=$settings["name"];

        if(isset($_GET['res']) && $_GET['res'] != $res) {
            continue;
        }

        $con = mysqli_connect("127.0.0.1",$settings["user"],$settings["password"],$settings["database"]);

        if(!$con) {
            echo 'Could not connect: ' . mysqli_error();
            continue;
        }

        if(isset($_GET['text_search'])) {
            $search = mysqli_real_escape_string($con, $_GET['text_search']);
        } else {
            echo "No search params";
            exit();
        }

        $limit=20;

        $offset=0;

        if(isset($_GET['offset']) && is_numeric($_GET['offset'])) {
            $offset = mysqli_real_escape_string($con, $_GET['offset']);
        }

        echo "<h2>".$displayNames[$res]."</h2>";
        $result = mysqli_query($con,"select uri, label from $res where match (label) against ('".$search."') order by length(label) asc limit 20 offset ". $offset);
        #echo("select uri, label from $res where match (label) against ('".$search."') order by length(label) asc limit 20 offset ". $offset);

        echo "<table>";
        while($row = mysqli_fetch_array($result)) {
            echo "<tr><td><a href='" . $row['uri'] . "'>" . $row['uri'] . "</a></td><td>";
            echo $row['label'] . "</td></tr>";
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
    }
  }
  include 'footer.htmlfrag';

?>
