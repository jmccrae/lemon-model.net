#!/bin/bash

if [ "$#" -eq  1 ] 
then
  path=$1
else
  if [[ -z "$lemon_fw_path" ]]
  then
    path=$lemon_fw_path
  else
    path=/var/www/lemon-model.net/htdocs/lexica/uby/
  fi
fi

die () {
    echo >&2 "$@"
    exit 1
}

if [ ! -e settings.ini ]
then
  die "You must have a settings.ini file"
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

if [ ! -e "license-$name.htmlfrag" ]
then
  die "Please create license-$name.htmlfrag"
fi

if [ ! -e "license-$name.nt" ]
then
  die "Please create license-$name.nt"
fi

if [ -e data/$res.nt ]
then
  mkdir -p $path/$res
  cp *.php *.htmlfrag *.xsl data/*.html settings.ini $path/$res
  cp license-$res.nt $path/$res
  cp htaccess $path/$res/.htaccess
  gzip -c data/$res.nt > data/$res.nt.gz
  mv data/$res.nt.gz $path/$res
  cat < header.htmlfrag > $path/$res/license.html
  cat < license-$res.htmlfrag >> $path/$res/license.html
  cat < footer.htmlfrag >> $path/$res/license.html
else
  die "No data found in data/$res.nt"
fi

if [ -e category.rdf ]
then
  xsltproc rdf2html.xsl category.rdf > category.htmlfrag
  cp header.htmlfrag category.html
  cat < category.html >> category.html
  cat < footer.htmlfrag >> category.html
  rm category.htmlfrag  
  rapper -i rdfxml -o turtle > category.ttl
  cp category.* $path
else
  echo "Warning: No category.rdf file"
fi

if [ -e welcome.htmlfrag ]
then
  cat < header.htmlfrag > welcome.html 
  cat < welcome.htmlfrag >> welcome.html 
  cat < footer.htmlfrag >> welcome.html 
  mv welcome.html $path/$res/index.html
else
  echo "Warning: No welcome.htmlfrag file"
fi

if [ -z $HTML_ONLY ]
then
    echo "Loading MySQL, this may take some time"
    cfg.section.database
    zcat data/$name.sql.gz | mysql -u$user -p$password $database
fi

