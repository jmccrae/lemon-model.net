#!/bin/python
import sys
from rdflib import *
from rdflib.plugins.parsers.notation3 import BadSyntax

def main(argv):
    g = Graph()

    try:
        g.parse(sys.stdin,format="turtle",publicID=argv[1])
    except BadSyntax, e:
        sys.stderr.write(str(e))
        exit(-1);

    url_prefix=argv[1];

    for subj, pred, obj in g:
        if not subj.startswith(url_prefix) and not isinstance(subj,BNode):
            g.remove((subj,pred,obj))

    g.serialize(sys.stdout,format="turtle")

if __name__=='__main__': main(sys.argv)
