#!/bin/bash
# Not pretty, but it strips the header and copies it to bodytest

h1='header.htmlfrag'; h2=`wc -l $h1`; h=`echo $h2 | cut -d ' ' -f1`; 
f1='footer.htmlfrag'; f2=`wc -l $f1`; f=`echo $f2 | cut -d ' ' -f1`; 

for file in `ls *.html`
do
  if [ $file = "404.html" ] 
     then continue;
  fi
  x2=`wc -l $file`; x=`echo $x2 | cut -d ' ' -f1`; 
  tail -n $((x-h)) $file | head -n $((x-f-h+1)) > "src/$file.body"
done

