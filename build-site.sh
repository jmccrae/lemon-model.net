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
cp -r fw/* tmp/pwn
cd tmp/pwn
./convert.sh
./install.sh ../../htdocs/lexica/
cd ../../

# Build de-gaap
mkdir -p tmp/de-gaap
cp -r src/lexica/de-gaap/* tmp/de-gaap
bunzip2 tmp/de-gaap/de/de.nt.gz
bunzip2 tmp/de-gaap/en/en.nt.gz
cp -r fw/* tmp/de-gaap/de/
cp -r fw/* tmp/de-gaap/en/
cd tmp/de-gaap/de/
./convert.sh
./install.sh ../../../htdocs/lexica/de-gaap/
cd ../en
./convert.sh
./install.sh ../../../htdocs/lexica/de-gaap/
cd ../../

echo "It is highly recommended to rm -fr tmp now"
