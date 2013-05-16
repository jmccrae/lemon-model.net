#!/bin/bash

mkdir -p htdocs/
mkdir -p htdocs/lexica/uby/
mkdir -p htdocs/lexica/de-gaap/
mkdir -p htdocs/lexica/pwn/

cd src/

for fileBody in `find . -name \*.html`
do
  target=../htdocs/$fileBody
  cat >$target < header.htmlfrag
  cat >>$target $fileBody
  cat >>$target < footer.htmlfrag
done

for fileBody in `find . -name \*.md`
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
if [ ! -e tmp/de-gaap/de/de.nt ]
then
    bunzip2 tmp/de-gaap/de/de.nt.gz
fi
if [ ! -e tmp/de-gaap/en/en.nt ]
then
    bunzip2 tmp/de-gaap/en/en.nt.gz
fi
cp -r fw/* tmp/de-gaap/de/
cp -r fw/* tmp/de-gaap/en/
cd tmp/de-gaap/de/
./convert.sh
./install.sh ../../../htdocs/lexica/de-gaap/
cd ../en
./convert.sh
./install.sh ../../../htdocs/lexica/de-gaap/
cd ../../../
cp src/lexica/de-gaap/htaccess htdocs/lexica/de-gaap/.htaccess
cp src/lexica/de-gaap/index.php htdocs/lexica/de-gaap/
cp src/lexica/de-gaap/*.rdf htdocs/lexica/de-gaap/
cp src/lexica/de-gaap/rdf2html.xsl htdocs/lexica/de-gaap/

# Build Uby
for res in fn ow_deu ow_eng vn WktDE WktEN wn
do
    mkdir -p tmp/uby/$res
    cp -r src/lexica/uby/$res/* tmp/uby/$res/
    if [ ! -e tmp/uby/$res/$res.nt ]
    then
        bunzip2 tmp/uby/$res/$res.nt.bz2
    fi
    cp -r fw/* tmp/uby/$res/
    cd tmp/uby/$res
    ./convert.sh
    ./install.sh ../../../htdocs/lexica/uby/
    cd ../../../
done

echo "It is highly recommended to rm -fr tmp now"
