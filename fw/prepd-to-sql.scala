import java.io._
import java.util.Properties
import scala.io._
import java.lang.StringBuilder

val props = new Properties()
props.load(new FileReader("settings.ini"))

val TABLE = Option(props.getProperty("name")).getOrElse(throw new IllegalArgumentException)

val twoPart = "(.*)_(.*)".r

val ucTable = TABLE match {
  case "WktDE" => "WktDE"
  case twoPart(p1,p2) => p1.toUpperCase()
  case _ => TABLE.toUpperCase()
} 

def unescapeUnicode(x : String) : String = {
  try {
    var x2 = x;
    while(x2.indexOf("\\u") >= 0) {
      val hexStr = x2.substring(x2.indexOf("\\u")+2,x2.indexOf("\\u")+6)
      val char = Integer.parseInt(hexStr).toChar
      x2 = x2.replaceAll("\\\\u"+hexStr,""+char)
    }
    return x2;
  } catch {
    case _ : Exception => x
  }
}

val prefix = Option(props.getProperty("prefix")) getOrElse { throw new IllegalArgumentException("set prefix in settings.ini") }
val name = Option(props.getProperty("name")) getOrElse { throw new IllegalArgumentException("set name in settings.ini") }
val lexiconID2 = Option(props.getProperty("lexicon")) getOrElse { throw new IllegalArgumentException("set lexicon in settings.ini") }
val lexiconID = prefix + name + "/" + lexiconID2

val in = Source.fromInputStream(System.in)

var lastURI = ""

var labelSB = new StringBuilder()
var ntSB = new StringBuilder()
var bntSB = new StringBuilder()

println("create table  if not exists "+TABLE + "( uri varchar(256) character set binary primary key, label text, nt longtext, back_nt longtext, fulltext(label));")
println("truncate table "+TABLE+";");


def escape(s : String) = s.replaceAll("\\\\u","\\\\\\\\u")

def shortenURI(s : String) = if(s.length > 255) {
  s.substring(0,205) + s.substring(s.length-50,s.length)
  } else {
  s
  }

for(line <- in.getLines) {
  val elems = unescapeUnicode(line).split(" \\|\\|\\| ")
  if(elems.length != 4) {
    System.err.println(">>" + line)
  } else {
    if(elems(0) != lastURI && elems(0) != "") {
      if(!(lastURI contains lexiconID) && lastURI != "") {
        print("insert into " + TABLE + " values('")
        print(shortenURI(lastURI) + "','")
        print(labelSB.toString())
        print("','")
        print(escape(ntSB.toString()))
        print("','")
        print(escape(bntSB.toString()))
        println("');")
      }
      labelSB.setLength(0);
      ntSB.setLength(0)
      bntSB.setLength(0)
      lastURI = elems(0);
    }
    if(elems(1) != "") {
      labelSB.append(" " + elems(1).replaceAll("'","\\\\'"))
    }
    if(elems(2) != "") {
      ntSB.append(elems(2).replaceAll("'","\\\\'") + " \\n ")
    }
    if(elems(3) != " ") {
      bntSB.append(elems(3).replaceAll("'","\\\\'") + " \\n ")
    }
  }
}


println("insert into " + TABLE + " values('"+
  shortenURI(lastURI) + "','" +
  labelSB.toString() + "','" +
  escape(ntSB.toString()) + "','" +
  escape(bntSB.toString()) + "');")

