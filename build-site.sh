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

cd ..

cp -r resources/* htdocs/


