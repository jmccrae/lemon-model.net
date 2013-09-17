#!/bin/bash

die () {
    echo >&2 "$@"
    exit 1
}

MACHINE_TYPE=`uname -m`
if [ ${MACHINE_TYPE} == "x86_64" ]
then
  JAVA_OPTS=-Xmx4g
elif [ ${MACHINE_TYPE} == "ia64" ]
then
  JAVA_OPTS=-Xmx4g
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
namespaces=('--feature' 'xmlns:lemon="http://www.monnet-project.eu/lemon#"' '--feature' 'xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"' '--feature' 'xmlns:owl="http://www.w3.org/2002/07/owl#"' '--feature' 'xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"' '--feature' 'xmlns:lexinfo="http://lexinfo.net/ontology/2.0/lexinfo#"')
IFS=" " read -a rs <<< $rappersettings
   
if [ -z $HTML_ONLY ]
then
    if [ ! -e data/$res.nt ]
    then
      echo "RDF/XML => NT"
      rapper -i rdfxml -o ntriples -I "$prefix" data/$res.rdf > data/$res.nt || die "Rapper failed"
      if [ -e data/$res.sa.nt ]
      then
            cat <data/$res.sa.nt >> data/$res.nt
      fi
    fi

    if [ ! -e data/$res.rdf ]
    then
      echo "NT => RDF/XML"
      rapper -i turtle -o rdfxml-abbrev -I "$prefix" ${namespaces[@]} ${rs[@]} data/$res.nt > data/$res.rdf || die "Rapper failed"
    fi

    lexiconURI="$prefix$lexicon"
    lexiconFile=$lexicon

    if [ ! -e data/$lexiconFile.nt ]
    then
      echo "Extracting Lexicon"	
      grep "$lexiconURI" data/$res.nt > data/$lexiconFile.nt
    fi


    if [ ! -e data/$res-prep.nt ]
    then
     echo "NT => Tricolumns"
     $scala prep-import.scala data/$res.nt | grep -v "$lexiconURI" > data/$res-prep.nt
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
else
    echo "Skipping data for $name"
fi

if [ ! -e $lexiconFile.php ] && [ -e data/$lexiconFile.nt ]
then
  echo "Converting Lexicon"
  rapper -i ntriples -o rdfxml-abbrev -I $prefix ${namespaces[@]} ${rs[@]} data/$lexiconFile.nt > data/$lexiconFile.rdf
  rapper -i ntriples -o turtle -I $prefix ${namespaces[@]} ${rs[@]} data/$lexiconFile.nt > data/$lexiconFile.ttl
  split -d -l 10000 data/$lexiconFile.nt data/$lexiconFile-split.
  rm -f data/*.htmlfrag
  splitFiles=`ls data/$lexiconFile-split.*`
  for splitFile in $splitFiles
  do
    rapper -q -i ntriples -o rdfxml-abbrev -I $prefix ${namespaces[@]} ${rs[@]} $splitFile | xsltproc rdf2html.xsl - > $splitFile.htmlfrag
    rm $splitFile
  done
  i=0
  iform=`printf "%02d" $i`
  splitFile="data/$lexiconFile-split.$iform.htmlfrag"
  ref="$lexiconFile.$iform.php"
  while [ -e $splitFile ] 
  do
    oldSplitFile=$splitFile
    oldRef=$ref
    echo $splitFile;
    i=$[$i+1]
    iform=`printf "%02d" $i`
    splitFile="data/$lexiconFile-split.$iform.htmlfrag"
    ref="$lexiconFile.$iform.php"
    if [ -e $splitFile ]
    then
      echo "<a href=\"$oldRef\">Previous</a>" | cat >> $splitFile
      echo "<a href=\"$ref\">Next</a>" | cat >> $oldSplitFile
    fi
  done 
  i=0
  iform=`printf "%02d" $i`
  splitFile="data/$lexiconFile-split.$iform.htmlfrag"
  cp header.htmlfrag data/$lexiconFile.php
  cat < $splitFile >> data/$lexiconFile.php
  rm $splitFile
  cat footer.htmlfrag >> data/$lexiconFile.php
  i=1
  iform=`printf "%02d" $i`
  splitFile="data/$lexiconFile-split.$iform.htmlfrag"
  while [ -e $splitFile ] 
  do
    cp header.htmlfrag data/$lexiconFile.$iform.php
    cat < $splitFile >> data/$lexiconFile.$iform.php
    rm $splitFile
    cat footer.htmlfrag >> data/$lexiconFile.$iform.php
    i=$[$i+1]
    iform=`printf "%02d" $i`
    splitFile="data/$lexiconFile-split.$iform.htmlfrag"
  done
fi	
