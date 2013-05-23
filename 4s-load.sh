
#!/bin/bash

killall 4s-httpd

mkdir tmp
cp `find src -name *.bz2` tmp/
cd tmp
for file in `ls *.bz2`
do
    bunzip2 $file
done
4s-import -a -m http://lemon-model.net/lexica/pwn/ lemon pwn.rdf
4s-import -a -m http://lemon-model.net/lexica/de-gaap/ lemon en.nt
4s-import -a -m http://lemon-model.net/lexica/de-gaap/ lemon de.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon WktEN.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon WktDE.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon fn.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon ow_eng.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon ow_deu.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon vn.nt
4s-import -a -m http://lemon-model.net/lexica/uby/ lemon wn.nt

4s-httpd -p 8000 lemon

