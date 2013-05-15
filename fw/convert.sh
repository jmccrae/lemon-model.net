#!/bin/bash

die () {
    echo >&2 "$@"
    exit 1
}

MACHINE_TYPE=`uname -m`
if [ ${MACHINE_TYPE} == "x86_64" ]
then
  JAVA_OPTS=-Xmx2g
elif [ ${MACHINE_TYPE} == "ia64" ]
then
  JAVA_OPTS=-Xmx2g
else
  echo "Not a 64-bit machine, you may run out of memory on some conversions"
fi

if [ ! -e /usr/bin/scala ] && [ -e /home/jmccrae/scala-2.9.2/bin/scala ]
then
	scala='/home/jmccrae/scala-2.9.2/bin/scala'
else
	scala='scala'
fi

# Thanks to http://ajdiaz.wordpress.com/2008/02/09/bash-ini-parser/ 
cfg_parser ()
{
    ini="$(<$1)"                # read the file
    ini="${ini//[/\[}"          # escape [
    ini="${ini//]/\]}"          # escape ]
    IFS=$'\n' && ini=( ${ini} ) # convert to line-array
    ini=( ${ini[*]//;*/} )      # remove comments with ;
    ini=( ${ini[*]/\    =/=} )  # remove tabs before =
    ini=( ${ini[*]/=\   /=} )   # remove tabs be =
    ini=( ${ini[*]/\ =\ /=} )   # remove anything with a space around =
    ini=( ${ini[*]/#\\[/\}$'\n'cfg.section.} ) # set section prefix
    ini=( ${ini[*]/%\\]/ \(} )    # convert text2function (1)
    ini=( ${ini[*]/=/=\( } )    # convert item to array
    ini=( ${ini[*]/%/ \)} )     # close array parenthesis
    ini=( ${ini[*]/%\\ \)/ \\} ) # the multiline trick
    ini=( ${ini[*]/%\( \)/\(\) \{} ) # convert text2function (2)
    ini=( ${ini[*]/%\} \)/\}} ) # remove extra parenthesis
    ini[0]="" # remove first element
    ini[${#ini[*]} + 1]='}'    # add the last brace
    eval "$(echo "${ini[*]}")" # eval the result
}

# read settings.ini
cfg_parser 'settings.ini'

cfg.section.resource

res=$name
prefix="$prefix$res/"

if [ ! -e data/$res.nt ]
then
  echo "RDF/XML => NT"
  rapper -i rdfxml -o ntriples -I "$prefix" data/$res.rdf > data/$res.nt || die "Rapper failed"
  if [ -e data/$res.sa.nt ]
  then
   	cat <data/$res.sa.nt >> data/$res.nt
  fi
fi

lexiconFile="$prefix/$lexicon"

if [ ! -e data/$lexiconFile.nt ]
then
  echo "Extracting Lexicon"	
  grep "$lexiconFile" data/$res.nt > data/$lexiconFile.nt
fi


if [ ! -e data/$res-prep.nt ]
then
 echo "NT => Tricolumns"
 cat data/$res.nt | $scala prep-import.scala $res | grep -v "$lexiconFile" > data/$res-prep.nt
fi

if [ ! -e data/$res-sort.nt ]
then
  echo "Tricolumn sort"
  LC_ALL=C sort data/$res-prep.nt > data/$res-sort.nt
fi

if [ ! -e data/$res.sql.gz ]
then
  echo "Tricolumn => SQL"
  cat data/$res-sort.nt | $scala prepd-to-sql.scala -Dres=$res | gzip > data/$res.sql.gz
fi

if [ ! -e $lexiconFile.html ]
then
	echo "Converting Lexicon"
        namespaces="-f 'xmlns:lemon=\"http://www.monnet-project.eu/lemon#\"' -f 'xmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"' -f 'xmlns:owl=\"http://www.w3.org/2002/07/owl#\"'  -f 'xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" -f 'xmlns:lexinfo=\"http://lexinfo.net/ontology/2.0/lexinfo#\" " . $settings["rappersettings"];
        rappercmd1="rapper -i ntriples -o rdfxml-abbrev -I $prefix $namespaces $rappersettings data/$lexiconFile.nt > data/$lexiconFile.rdf"
        `rappercmd1`
        rappercmd2="rapper -i ntriples -o turtle -I $prefix $namespaces $rappersettings data/$lexiconFile.nt > data/$lexiconFile.ttl"
        `rappercmd2`
	split -d -l 10000 data/$lexiconFile.nt data/$lexiconFile-split.
	splitFiles=`ls data/$lexiconFile-split.*`
	for splitFile in $splitFiles
	do
          rappercmd3="rapper -q -i ntriples -o rdfxml-abbrev -I $prefix $namespaces $rappersettings $splitFile | xsltproc ../site/rdf2html.xsl - > $splitFile.htmlfrag"
          `rappercmd3`
	  rm $splitFile
	done
	i=0
	iform=`printf "%02d" $i`
	splitFile="data/$lexiconFile-split.$iform.htmlfrag"
	ref="$lexiconFile.$iform.html"
	while [ -e $splitFile ] 
	do
		oldSplitFile=$splitFile
		oldRef=$ref
		echo $splitFile;
		i=$[$i+1]
		iform=`printf "%02d" $i`
		splitFile="data/$lexiconFile-split.$iform.htmlfrag"
		ref="$lexiconFile.$iform.html"
		if [ -e $splitFile ]
		then
			echo "<a href=\"$oldRef\">Previous</a>" | cat >> $splitFile
			echo "<a href=\"$ref\">Next</a>" | cat >> $oldSplitFile
		fi
	done 
	i=0
	iform=`printf "%02d" $i`
	splitFile="data/$lexiconFile-split.$iform.htmlfrag"
	cp header.htmlfrag $lexiconFile.html
	cat < $splitFile >> $lexiconFile.html
	rm $splitFile
	cat footer.htmlfrag >> $lexiconFile.html
	i=1
	iform=`printf "%02d" $i`
	splitFile="data/$lexiconFile-split.$iform.htmlfrag"
	while [ -e $splitFile ] 
	do
		cp header.htmlfrag $lexiconFile.$iform.html
		cat < $splitFile >> $lexiconFile.$iform.html
		rm $splitFile
		cat footer.htmlfrag >> site/$lexiconFile.$iform.html
	done
fi	
