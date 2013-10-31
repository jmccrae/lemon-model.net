<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

include 'header.htmlfrag';
if(array_key_exists('document',$_POST)) {
    $temp = tempnam("/tmp","lexicon");

    $out = fopen($temp,"w");
    fwrite($out,$_POST['document']);
    fclose($out);

    $format = $_POST['format'];
    if($format != "xml" && $format != "turtle") {
        $format = "xml";
    }
    $cmd = "python " . getcwd() . "/lemon-validator.py -f$format -ohtml $temp";
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        fclose($pipes[0]);

        $content = stream_get_contents($pipes[1]);
        $errs = stream_get_contents($pipes[2]);
?>
<h1>Lemon Validator</h1>
<h3>You submitted</h3>
<button value="Show/Hide" onclick="javascript:$('#code').toggle();">Show/Hide</button>
<div id="code" style="display:none;">
<pre>
<?php echo htmlspecialchars($_POST['document']); ?>
</pre>
</div>
<h3>Result</h3>
<?php
        echo $content;
        echo $errs;
        fclose($pipes[1]);
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo "<h1>500 Internal Server Error</h1>";
        exit();
    }
    unlink($temp);
?>
<?php
    

} else {
?>
<h1>Lemon Validator</h1>

<a href="lemon-validator.py">Download validator</a>

Enter your lexicon here:
<form action="index.php" method="post">
  <input type="radio" name="format" value="xml" checked>RDF/XML</input>
  <input type="radio" name="format" value="turtle">Turtle/N-Triples</input><br/>
  <textarea class="code-textarea" name="document">
<rdf:RDF xmlns:lemon="http://www.monnet-project.eu/lemon#"
         xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
</rdf:RDF>
</textarea><br/>
  <input type="submit" name="submit" value="Validate"/>
</form>
<?php
}
include 'footer.htmlfrag';
?>
