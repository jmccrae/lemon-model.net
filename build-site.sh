#!/bin/bash

mkdir -p htdocs/

cd src/

for fileBody in *.body
do
  target=../htdocs/${fileBody%.body}
  cat >$target < header.htmlfrag
  cat >>$target $fileBody
  cat >>$target < footer.htmlfrag
done

for fileBody in *.md
do 
  target=../htdocs/${fileBody%.body}.html
  cat >$target < header.htmlfrag
  pandoc -f markdown -t html $fileBody >>$target
  cat >>$target < footer.htmlfrag
done

cd ..

cp -r resources/* htdocs/

cp src/nonlocal-header.htmlfrag htdocs/header.htmlfrag

cp src/nonlocal-footer.htmlfrag htdocs/footer.htmlfrag

cp htaccess htdocs/.htaccess

