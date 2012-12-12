#!/bin/bash

for file in cookbook.html download.html index.html api.html source.html 5mins.html lemon.html
do
  cat >$file < header.htmlfrag
  cat >>$file "$file.body"
  cat >>$file < footer.htmlfrag
done


