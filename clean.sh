#!/bin/bash
# Run only on GreenTentacle!

if [ `hostname` = "greententacle" ]
then 
  rm *.sh
  rm *.html.body
  rm *.htmlfrag
fi

if [ `hostname` = "grim-fandango" ]
then
  echo "Not cleaning on Grim Fandango"
fi
