import java.io._
import java.sql.{Array => _,_}
import java.util.{Map => _,List=>_,_}
import java.text.DateFormat
import javax.xml.transform._
import javax.xml.transform.stream._

Class.forName("com.mysql.jdbc.Driver")

val year = args(0).toInt

val iniFiles = Map(
  "src/lexica/uby/WktDE/settings.ini" -> "Uby Wiktionary (German)",
  "src/lexica/uby/WktEN/settings.ini" -> "Uby Wiktionary (English)",
  "src/lexica/uby/ow_eng/settings.ini" -> "Uby OmegaWiki (English)",
  "src/lexica/uby/ow_deu/settings.ini" -> "Uby OmegaWiki (German)",
  "src/lexica/uby/fn/settings.ini" -> "Uby FrameNet",
  "src/lexica/uby/vn/settings.ini" -> "Uby VerbNet",
  "src/lexica/uby/wn/settings.ini" -> "Uby WordNet",
  "src/lexica/pwn/settings.ini" -> "Princeton WordNet")

val urisByFile = (for(iniFile <- iniFiles.keys) yield {
  iniFile -> {
    val props = new Properties()
    props.load(new java.io.FileReader(iniFile))
    val conn = DriverManager.getConnection("jdbc:mysql://127.0.0.1/" + props.getProperty("database") + "?user=" + props.getProperty("user") + "&password=" +
      props.getProperty("password"))
    val stat = conn.createStatement()
    val results = stat.executeQuery("select uri from " + props.getProperty("name"))
    val uris = new collection.mutable.ListBuffer[String]()

    while(results.next()) {
      val name = results.getString("uri")
      if(iniFile == "src/lexica/pwn/settings.ini" && !name.contains("SynSet") && !name.contains("sense")) {
        uris += name
      } else if(name.contains("exicalEntry")) {
        uris += name
      }
    }

    System.err.println("Read " + uris.size + " entries from " + props.getProperty("name"))
    results.close
    stat.close
    conn.close
    uris.toList
  }
}).toMap

def getText(iniFile : String ,uri : String, props : Properties) = {
 val conn = DriverManager.getConnection("jdbc:mysql://127.0.0.1/" + props.getProperty("database") + "?user=" + props.getProperty("user") + "&password=" +
    props.getProperty("password"))
  val stat = conn.createStatement()
  val results = stat.executeQuery("select * from " + props.getProperty("name") + " where uri='"+uri+"'")

  results.next()
  val nt = results.getString("nt") + results.getString("back_nt")

  results.close
  stat.close
  conn.close
  nt
}

class Connector(in : InputStream, out : OutputStream, close : Boolean = false) extends Thread {
  override def run() {
    try {
      var i = 0
      val buf = new Array[Byte](1024)
      while({ i = in.read(buf); i != -1}) {
        out.write(buf,0,i)
      }
    } catch {
      case x : EOFException => {}
    }
    if(close) {
      out.flush
      out.close
    }
  }
}

class StringBufferOutputStream extends OutputStream {
  private val textBuffer = new StringBuffer()

  override def write(b : Int) { textBuffer.append(b.toChar) }
  override def toString = textBuffer.toString
}

def convertNTtoXML(nt : String, props : Properties) = {
  val rs = props.getProperty("rappersettings").replaceAll("^\"","").replaceAll("\"$","").replaceAll("\"","'")
  val cmd = List("rapper","-q","-i","turtle","-o","rdfxml-abbrev","-I",props.getProperty("prefix") + props.getProperty("name") + "/")
  val features = List("--feature","xmlns:lemon='http://lemon-model.net/lemon#'",
  	"--feature","xmlns:rdfs='http://www.w3.org/2000/01/rdf-schema#'",
	"--feature","xmlns:owl='http://www.w3.org/2002/07/owl#'",
	"--feature","xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'",
	"--feature","xmlns:lexinfo='http://lexinfo.net/ontology/2.0/lexinfo#'",
	"--feature","xmlns:skos='http://www.w3.org/2004/02/skos/core#'") ++
	rs.split(" ")

  val pb = new ProcessBuilder((cmd ::: features ::: List("-")):_*)
  val process = pb.start()
  
  val sw = new StringBufferOutputStream()
  val c1 = new Connector(process.getInputStream(),sw,true)
  c1.start
  val c2 = new Connector(process.getErrorStream(),System.err,false)
  c2.start
  val data = new StringBufferInputStream(nt)
  val c3 = new Connector(data,process.getOutputStream(),true)
  c3.start

  process.waitFor()
  c1.join()

  sw.toString()
}
   
val transformerFactory = TransformerFactory.newInstance()
val transformer = transformerFactory.newTransformer(new StreamSource(new File("src/eotd/rdf2eotd.xsl")))

def niceURI(uri : String) = {
  val cut = math.max(uri.lastIndexOf('#'),uri.lastIndexOf('/'))
  if(cut > 0) {
    uri.substring(cut+1)
  } else {
    uri
  }
}

for(day <- 1 to 366) {
  val date = DateFormat.getDateInstance().format(new GregorianCalendar(year,0,day).getTime())
  System.err.println("Generating for " + date)

  val whichDB = iniFiles.keys.toSeq((math.random * iniFiles.size).toInt)

  val whichURI = urisByFile(whichDB)((math.random * urisByFile(whichDB).size).toInt)

  System.err.println(whichURI + " from " + whichDB)

  val props = new Properties()
  props.load(new java.io.FileReader(whichDB))
   
  val text = getText(whichDB,whichURI,props)

  val rdfxml = convertNTtoXML(text,props)
  val out = new PrintWriter("src/eotd/"+date+".htm")

  out.println("<h5><a href='"+whichURI+"'>"+niceURI(whichURI)+ "</a> from " + iniFiles(whichDB) + "</h5>")

  transformer.transform(new StreamSource(new StringReader(rdfxml)),
    new StreamResult(out))

  out.flush
  out.close
}
