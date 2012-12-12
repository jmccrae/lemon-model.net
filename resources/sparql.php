<?php
  if(!isset($_GET['search'])) {
     include 'header.htmlfrag';
?>
  <div class="pageMid">
  <form action="sparql.php" method="get">
    <h3>Query in SPARQL</h3><br/>
    <textarea name="search" rows="10" cols="50">
PREFIX lemon: &lt;http://www.monnet-project.eu/lemon#&gt;
select * where {
  	?s lemon:writtenRep "Cat"@en
}
    </textarea><br/>
    <input type="submit" value="Search"/><br/>
  </form>
  </div>
<?php
     include 'footer.htmlfrag';
     exit();
  }

  $ch = curl_init();

  curl_setopt($ch,CURLOPT_URL,'http://localhost:9999/sparql/?query='.urlencode($_GET['search']));
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
  
  echo "</div>";


  include 'footer.htmlfrag';

  curl_close($ch);
?>