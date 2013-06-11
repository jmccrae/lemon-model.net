import java.io._
import java.util.Scanner
import java.util.Properties

val props = new Properties()
props.load(new FileReader("settings.ini"))
val storeBnodes = props.getProperty("bnodes") != null
System.err.println("BNode store:" + storeBnodes)

val in = new Scanner(System.in) 

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

// BNodeID -> URI
val bnodeMap = new scala.collection.mutable.HashMap[String,String]()
// BNode -> Saved Triples
val bnodes = new scala.collection.mutable.HashMap[String,List[BNodeEntry]]()

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
    if(isBNode(elems(0)) && !bnodeMap.contains(elems(0))) {
      if(storeBnodes) {
        bnodes.put(elems(0),
          BNodeEntry(form,line) :: 
          bnodes.getOrElse(elems(0),Nil))
      }
    } else {
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
      
      if(isBNode(obj)) {
        if(bnodes.contains(obj)) {
          for(BNodeEntry(label,nt) <- bnodes.getOrElse(obj,Nil)) {
            println(head + " ||| " + label + " ||| " + nt + " |||  ")
          }
          bnodes remove obj
        }
	if(storeBnodes) {
          bnodeMap put (obj,head)
	}
      }
    }
  }
}
System.err.println()
