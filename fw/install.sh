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

source 'settings.ini'

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
  cp *.php *.htmlfrag *.xsl data/*.php settings.ini $path/$res
  cp license-$res.nt $path/$res
  cp htaccess $path/$res/.htaccess
  gzip data/$res.nt
  mv data/$res.nt.gz $path/$res
  cat < header.htmlfrag > $path/$res/license.php
  cat < license-$res.htmlfrag >> $path/$res/license.php
  cat < footer.htmlfrag >> $path/$res/license.php
else
  echo "Warning: No data found in data/$res.nt"
fi

if [ -e category.rdf ]
then
  xsltproc rdf2html.xsl category.rdf > category.htmlfrag
  cp header.htmlfrag category.php
  cat < category.html >> category.php
  cat < footer.htmlfrag >> category.php
  rm category.htmlfrag  
  rapper -i rdfxml -o turtle > category.ttl
  cp category.* $path
else
  echo "Warning: No category.rdf file"
fi

if [ -e welcome.htmlfrag ]
then
  cat < header.htmlfrag > welcome.php 
  cat < welcome.htmlfrag >> welcome.php 
  cat < footer.htmlfrag >> welcome.php 
  mv welcome.php $path/$res/index.php
else
  echo "Warning: No welcome.htmlfrag file"
fi

if [ -z $HTML_ONLY ]
then
    echo "Loading MySQL, this may take some time"
    cfg.section.database
    zcat data/$name.sql.gz | mysql -u$user -p$password $database
fi

