Lemon (Source) Data Publishing Framework
========================================

This is the basic publishing framework for publishing data on lemon-model.net

To use this create copy of this folder and move it some path, e.g.,  `wn/`

# settings.ini

Next create a `settings.ini` file as follows

     [resource]
     name=wn
     rappersettings="-f xmlns:lemonwn=\"http://lemon-model.net/lexica/pwn/\""
     prefix=http://lemon-model.net/lexica/
     lexicon=WordNet

     [database]
     database=db
     user=user
     password=pwd

Where the resource is to be published at, for example

     http://lemon-model.net/lexica/wn/

And the main Lexicon object(s) are at

     http://lemon-model.net/lexica/wn/WordNet*

(Note, no other URI in the resource should match this regex)

And the `rappersettings` are any extra parameters (normally name prefixes) that should be passed to rapper.

# license-$res.{nt,htmlfrag}

Next create two files `license-wn.htmlfrag` and `license-wn.nt` (replacing `wn` with the name of the resource) that describe the license in HTML (without `<html>` or `<body>`) and N-Triples, e.g.,

license-wn.htmlfrag
    <p>
      License Blurb
    </p>

license-wn.nt
    <> <http://purl.org/dc/terms/source> <http://wordnet.princeton.edu/wordnet/> .
    <> <http://purl.org/dc/terms/license> <http://wordnet.princeton.edu/wordnet/license/> .

# Data conversion

Next create a directory called `data` and copy the resource file to either `data/wn.nt` or `data/wn.rdf`

Run `convert.sh`

# category.rdf (optional)

If you are using extra categories in the resource at `$prefix$name/category#` create their description at `category.rdf` in (RDF/XML)

# welcome.htmlfrag

Create the content in HTML of the welcome page at `welcome.htmlfrag`

# Install

Run
    install.sh




