#!/bin/bash

buildsite() {
  case $1 in
  pages)
    mkdir -p htdocs/
    mkdir -p htdocs/learn
    mkdir -p htdocs/download
    cp -r src/eotd/ htdocs/
    mkdir -p htdocs/lexica/uby/
    mkdir -p htdocs/lexica/uby/fn
    mkdir -p htdocs/lexica/uby/ow_eng
    mkdir -p htdocs/lexica/uby/ow_deu
    mkdir -p htdocs/lexica/uby/vn
    mkdir -p htdocs/lexica/uby/WktEN
    mkdir -p htdocs/lexica/uby/WktDE
    mkdir -p htdocs/lexica/uby/wn
    mkdir -p htdocs/lexica/wiktionary_en/en
    mkdir -p htdocs/lexica/wiktionary_en/de
    mkdir -p htdocs/lexica/wiktionary_en/fr
    mkdir -p htdocs/lexica/wiktionary_en/es
    mkdir -p htdocs/lexica/wiktionary_en/nl
    mkdir -p htdocs/lexica/wiktionary_en/ja
    mkdir -p htdocs/lexica/de-gaap/
    mkdir -p htdocs/lexica/dbpedia_en/
    mkdir -p htdocs/lexica/pwn/
    mkdir -p htdocs/lexica/pwn/ara
    mkdir -p htdocs/lexica/pwn/cat
    mkdir -p htdocs/lexica/pwn/dan
    mkdir -p htdocs/lexica/pwn/eus
    mkdir -p htdocs/lexica/pwn/fin
    mkdir -p htdocs/lexica/pwn/fra
    mkdir -p htdocs/lexica/pwn/glg
    mkdir -p htdocs/lexica/pwn/heb
    mkdir -p htdocs/lexica/pwn/ita
    mkdir -p htdocs/lexica/pwn/jap
    mkdir -p htdocs/lexica/pwn/msa
    mkdir -p htdocs/lexica/pwn/por
    mkdir -p htdocs/lexica/pwn/spa
    mkdir -p htdocs/lexica/pwn/sqi
    mkdir -p htdocs/lexica/pwn/tha
    mkdir -p htdocs/lexica/pwn/zho
    cp -r src/source/ htdocs/

    cd src/

    for fileBody in `find . -name \*.html`
    do
      target=../htdocs/${fileBody%.html}.php
      cat >$target < header.htmlfrag
      cat >>$target $fileBody
      cat >>$target < footer.htmlfrag
    done

    for fileBody in `find . -name \*.md`
    do 
      target=../htdocs/${fileBody%.md}.php
      cat >$target < header.htmlfrag
      pandoc -f markdown -t html $fileBody >>$target
      cat >>$target < footer.htmlfrag
    done

    for file in `find . -name local.css`
    do
        cp $file ../htdocs/$file
    done

    cd ..

    cp -r resources/* htdocs/

    cp src/header.htmlfrag htdocs/header.htmlfrag

    cp src/footer.htmlfrag htdocs/footer.htmlfrag

    cp src/*.php htdocs/

    cp htaccess htdocs/.htaccess
	;;
  pwn)

    # Build lexica/pwn
    echo "Make Princeton WordNet"
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
    for lang in ara cat dan eus fin fra glg heb ita jap msa por spa sqi tha zho
    do
        mkdir -p tmp/pwn/$lang
        cp -r src/lexica/pwn/$lang/* tmp/pwn/$lang
        if [ ! -e tmp/pwn/$lang/data/$lang.nt ]
        then
            gunzip tmp/pwn/$lang/data/$lang.nt.gz
        fi
        cp -r fw/* tmp/pwn/$lang
        cd tmp/pwn/$lang
        ./convert.sh
        ./install.sh ../../../htdocs/lexica/pwn/
        cd ../../../ 
    done
    ;;

  dbpedia_en)
    echo "Make DBpedia English"
    mkdir -p tmp/dbpedia_en
    cp -r src/lexica/dbpedia_en/* tmp/dbpedia_en
    if [ ! -e tmp/dbpedia_en/data/dbpedia_wn.nt ]
    then
      bunzip2 tmp/dbpedia_en/data/dbpedia_en.nt.bz2
    fi
    cp -r fw/* tmp/dbpedia_en
    cd tmp/dbpedia_en
    ./convert.sh
    ./install.sh ../../htdocs/lexica/
    cd ../../
    ;;

  degaap)
    # Build de-gaap
    echo "Make DE-GAAP"
    cp -r src/lexica/de-gaap htdocs/lexica/
    ;;

  uby)
# Build Uby
    echo "Make Uby"
    for res in fn ow_deu ow_eng vn WktDE WktEN wn
    do
        mkdir -p tmp/uby/$res
        cp -r src/lexica/uby/$res/* tmp/uby/$res/
        if [ ! -e tmp/uby/$res/data/$res.nt ]
        then
            bunzip2 tmp/uby/$res/data/$res.nt.bz2
        fi
        cp -r fw/* tmp/uby/$res/
        cd tmp/uby/$res
        ./convert.sh
        ./install.sh ../../../htdocs/lexica/uby/
        cd ../../../
    done

    echo "It is highly recommended to rm -fr tmp now"
    ;;

  wiktionary)
      echo "Make wiktionary_en"
      for res in de en es fr ja nl
      do
        mkdir -p tmp/wiktionary_en/$res
        cp -r src/lexica/wiktionary_en/$res/* tmp/wiktionary_en/$res/
        if [ ! -e tmp/wiktionary_en/$res/data/$res.nt ]
        then
            bunzip2 tmp/wiktionary_en/$res/data/$res.nt.bz2
        fi
        cp -r fw/* tmp/wiktionary_en/$res/
        cd tmp/wiktionary_en/$res
        ./convert.sh
        ./install.sh ../../../htdocs/lexica/wiktionary_en/
        cd ../../../
    done
    ;;

  validator)
      mkdir -p htdocs/validator
      cp validator/* htdocs/validator
      ;;
  esac
}

case $1 in 
  all)
     buildsite "pages"
     buildsite "validator"
     buildsite "pwn"
     buildsite "dbpedia_en"
     buildsite "wiktionary"
     buildsite "uby"
     ;;
  *)
     buildsite $1
     ;;
esac

