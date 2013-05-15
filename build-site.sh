#!/bin/bash

mkdir -p htdocs/

cd src/

for fileBody in *.html
do
  target=../htdocs/$fileBody
  cat >$target < header.htmlfrag
  cat >>$target $fileBody
  cat >>$target < footer.htmlfrag
done

for fileBody in *.md
do 
  target=../htdocs/${fileBody%.md}.html
  cat >$target < header.htmlfrag
  pandoc -f markdown -t html $fileBody >>$target
  cat >>$target < footer.htmlfrag
done

cd ..

cp -r resources/* htdocs/

cp src/nonlocal-header.htmlfrag htdocs/header.htmlfrag

cp src/nonlocal-footer.htmlfrag htdocs/footer.htmlfrag

cp htaccess htdocs/.htaccess

# Build lexica/pwn
mkdir -p tmp/pwn
cp -r src/lexica/pwn/* tmp/pwn
if [ ! -e tmp/pwn/data/pwn.rdf ]
then
  bunzip2 tmp/pwn/data/pwn.rdf.bz2
fi
cd tmp/pwn
./convert.sh
./install.sh
cd ../../
