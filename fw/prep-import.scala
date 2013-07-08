import java.io._
import java.util.Scanner
import java.util.Properties

val props = new Properties()
props.load(new FileReader("settings.ini"))
val storeBnodes = props.getProperty("bnodes") != null
System.err.println("BNode store:" + storeBnodes)


def literal(s : String) : Option[String] = if(s matches "\".*\"(|@[\\w\\d-]+|\\^\\^.*)") {
  Some(s.substring(s.indexOf("\"")+1,s.lastIndexOf("\"")))
} else {
  None
}

def rebuildLiteral(ss : Array[String]) = {
  val sb = new StringBuilder()
  for(i <- 2 until (ss.length-1)) {
    if(sb.length > 0) {
      sb.append(" ") 
    } 
    sb.append(ss(i))
  }
  sb.toString()
}

def isBNode(s : String) = s startsWith "_:"

val prefix = Option(props.getProperty("prefix")) getOrElse { throw new IllegalArgumentException("Set prefix in settings.ini") }

def cleanURI(s : String) = {
  val t = s.replaceAll("^<","").replaceAll(">$","")
  if((t startsWith prefix) && (t contains "#")) {
    t.substring(0,t.indexOf("#"))
  } else {
    t
  }
}

case class BNodeEntry(val label : String, val nt : String)

val bnodeMap = new scala.collection.mutable.HashMap[String,String]()

if(storeBnodes) {
    val bnodeMapTmp = new scala.collection.mutable.HashMap[String,String]()
    val in = new Scanner(new File(args(0)))
    while(in.hasNextLine()) {
        val line = in.nextLine()
        val elems = line split "\\s+"
        if(elems.size == 4) {
            if(isBNode(elems(0)) && isBNode(elems(2))) {
                bnodeMapTmp.put(elems(2),elems(0))
            } else if(isBNode(elems(0)) && elems(2).startsWith("<")) {
                bnodeMapTmp.put(elems(0),cleanURI(elems(2)))
            } else if(isBNode(elems(2))) {
                bnodeMapTmp.put(elems(2),cleanURI(elems(0)))
            }
        }
    }

    for(bnode <- bnodeMapTmp.keys) {
        var s = bnodeMapTmp(bnode)
        while(isBNode(s)) {
            s = bnodeMapTmp(s)
        }
        bnodeMap.put(bnode,s)
    }
}

val in = new Scanner(new File(args(0))) 
var linesRead = 1;

while(in.hasNextLine()) {
  if(linesRead % 1000 == 0) {
    System.err.print(".");
  }
  linesRead = linesRead + 1;
  val line = in.nextLine()
  if(!(line matches "\\s*")) {
    val elems = line split "\\s+"
    if(elems.length < 4) {
      throw new IllegalArgumentException("Bad line: " + line);
    }
    val obj = rebuildLiteral(elems)
    val form = if(elems(1) == "<http://www.monnet-project.eu/lemon#writtenRep>") {
      literal(obj).getOrElse("")
    } else {
      ""
    }
    val head : String = if(isBNode(elems(0)) && bnodeMap.contains(elems(0))) {
      bnodeMap.getOrElse(elems(0), throw new RuntimeException())
    } else {
      cleanURI(elems(0))
    }
    
    println(head + " ||| " + form + " ||| " + line + " |||  ")
    if((obj startsWith ("<" + prefix +"/" + args(0))) && !(obj contains "#")) {
      // Back link
      println(cleanURI(obj) + " |||  |||  ||| " + line)
    }
  }
}
System.err.println()
