#!/bin/python
import sys
import getopt
import StringIO
import re
from rdflib import *
from rdflib.namespace import RDF, RDFS, OWL
from xml.sax.saxutils import escape

suspicious = 0
minor = 0
major = 0
warnOfMessage = StringIO.StringIO()
endOfMessage = StringIO.StringIO()

outputFormat = "txt"

lemon = Namespace("http://www.monnet-project.eu/lemon#")
lexinfo = Namespace("http://www.lexinfo.net/ontology/2.0/lexinfo#")
lexinfoAlt = Namespace("http://lexinfo.net/ontology/2.0/lexinfo#")
oils = Namespace("http://lemon-model.net/oils#")

lexinfoProps = dict([
    (lexinfo.adjunct,lemon.synArg),
    (lexinfo.possessiveAdjunct,lemon.synArg),
    (lexinfo.predicativeAdjunct,lemon.synArg),
    (lexinfo.comparativeAdjunct,lemon.synArg),
    (lexinfo.superlativeAdjunct,lemon.synArg),
    (lexinfo.prepositionalAdjunct,lemon.synArg),
    (lexinfo.attributiveArg,lemon.synArg),
    (lexinfo.clausalArg,lemon.synArg),
    (lexinfo.declarativeClause,lemon.synArg),
    (lexinfo.gerundClause,lemon.synArg),
    (lexinfo.infinitiveClause,lemon.synArg),
    (lexinfo.interrogativeInfinitiveClause,lemon.synArg),
    (lexinfo.possessiveInfinitiveClause,lemon.synArg),
    (lexinfo.prepositionalGerundClause,lemon.synArg),
    (lexinfo.prepositionalInterrogativeCaluse,lemon.synArg),
    (lexinfo.sententialClause,lemon.synArg),
    (lexinfo.subjunctiveClause,lemon.synArg),
    (lexinfo.complement,lemon.synArg),
    (lexinfo.adverbialComplement,lemon.synArg),
    (lexinfo.objectComplement,lemon.synArg),
    (lexinfo.predicativeAdjective,lemon.synArg),
    (lexinfo.predicativeAdverb,lemon.synArg),
    (lexinfo.predicativeNominative,lemon.synArg),
    (lexinfo.copulativeArg,lemon.synArg),
    (lexinfo.copulativeSubject,lemon.synArg),
    (lexinfo.object,lemon.synArg),
    (lexinfo.adpositionalObject,lemon.synArg),
    (lexinfo.prepositionalObject,lemon.synArg),
    (lexinfo.postpositionalObject,lemon.synArg),
    (lexinfo.directObject,lemon.synArg),
    (lexinfo.genitiveObject,lemon.synArg),
    (lexinfo.indirectObject,lemon.synArg),
    (lexinfo.postPositiveArg,lemon.synArg),
    (lexinfo.subject,lemon.synArg),
    (lexinfo.copulativeSubject,lemon.synArg)
])

lemonPropDomains = dict([
        (lemon.entry, lemon.Lexicon),
        (lemon.lexicalForm, lemon.LexicalEntry),
        (lemon.canonicalForm, lemon.LexicalEntry),
        (lemon.otherForm, lemon.LexicalEntry),
        (lemon.abstractForm, lemon.LexicalEntry),
        (lemon.sense, lemon.LexicalEntry),
        (lemon.reference, lemon.LexicalSense),
        (lemon.representation, lemon.Form),
        (lemon.writtenRep, lemon.Form),
        (lemon.senseRelation, lemon.LexicalSense),
        (lemon.broader, lemon.LexicalSense),
        (lemon.narrower, lemon.LexicalSense),
        (lemon.equivalent, lemon.LexicalSense),
        (lemon.incomptabile, lemon.LexicalSense),
        (lemon.condition, lemon.LexicalSense),
        (lemon.constituent, lemon.Node),
        (lemon.context, lemon.LexicalSense),
        (lemon.decomposition, lemon.LexicalEntry),
        (lemon.definition, lemon.LexicalSense),
        (lemon.edge, lemon.Node),
        (lemon.element, lemon.Component),
        (lemon.example, lemon.LexicalSense),
        (lemon.semArg, lemon.LexicalSense),
        (lemon.subjOfProp, lemon.LexicalSense),
        (lemon.objOfProp, lemon.LexicalSense),
        (lemon.isA, lemon.LexicalSense),
        (lemon.extrinsicArg, lemon.LexicalSense),
        (lemon.formVariant, lemon.Form),
        (lemon.generates, lemon.MorphTransform),
        (lemon.isSenseOf, lemon.LexicalSense),
        (lemon.leaf, lemon.Node),
        (lemon.lexicalVariant, lemon.LexicalEntry),
        (lemon.marker, lemon.Argument),
        (lemon.nextTransforn, lemon.MorphTransform),
        (lemon.optional, lemon.Argument),
        (lemon.pattern, lemon.LexicalEntry),
        (lemon.phraseRoot, lemon.LexicalEntry),
        (lemon.propertyDomain, lemon.LexicalSense),
        (lemon.propertyRange, lemon.LexicalSense),
        (lemon.rule, lemon.MorphTransform),      
        (lemon.separator, lemon.Node),
        (lemon.subsense, lemon.LexicalSense),
        (lemon.synArg, lemon.Frame),
        (lemon.synBehavior, lemon.LexicalEntry),
        (lemon.transform, lemon.MorphPattern)
])

lemonPropRanges = dict([
        (lemon.entry, lemon.LexicalEntry),
        (lemon.form, lemon.Form),
        (lemon.canonicalForm, lemon.Form),
        (lemon.otherForm, lemon.Form),
        (lemon.abstractForm, lemon.Form),
        (lemon.sense, lemon.LexicalSense),
        (lemon.isReferenceOf, lemon.LexicalSense),
        (lemon.altRef, lemon.LexicalSense),
        (lemon.prefRef, lemon.LexicalSense),
        (lemon.hiddenRef, lemon.LexicalSense),
        (lemon.senseRelation, lemon.LexicalSense),
        (lemon.broader, lemon.LexicalSense),
        (lemon.narrower, lemon.LexicalSense),
        (lemon.equivalent, lemon.LexicalSense),
        (lemon.incomptabile, lemon.LexicalSense),
        (lemon.condition, lemon.SenseCondition),
        (lemon.context, lemon.SenseContext),
        (lemon.definition, lemon.SenseDefinition),
        (lemon.decomposition, lemon.ComponentList),
        (lemon.edge, lemon.Node),
        (lemon.example, lemon.UsageExample),
        (lemon.semArg, lemon.Argument),
        (lemon.subjOfProp, lemon.Argument),
        (lemon.objOfProp, lemon.Argument),
        (lemon.isA, lemon.Argument),
        (lemon.extrinsicArg, lemon.Argument),
        (lemon.formVariant, lemon.Form),
        (lemon.generates, lemon.Prototype),
        (lemon.isSenseOf, lemon.LexicalEntry),
        (lemon.lexicalVariant, lemon.LexicalEntry),
        (lemon.nextTransform, lemon.MorphTransform),
        (lemon.pattern, lemon.MorphPattern),
        (lemon.phraseRoot, lemon.Node),
        (lemon.propertyDomain, lemon.SenseCondition),
        (lemon.propertyRange, lemon.SenseCondition),
        (lemon.subsense, lemon.LexicalSense),
        (lemon.synArg, lemon.Argument),
        (lemon.synBehavior, lemon.Frame),
        (lemon.transform, lemon.MorphTransform)
])

lemonDataProperties = set([
    lemon.writtenRep,
    lemon.language,
    lemon.optional,
    lemon.representation,
    lemon.rule,
    lemon.separator,
    lemon.value
])

lemonURIs = set([
  lemon.abstractForm,
  lemon.altRef,
  lemon.Argument,
  lemon.broader,
  lemon.canonicalForm,
  lemon.Component,
  lemon.ComponentList,
  lemon.condition,
  lemon.constituent,
  lemon.context,
  lemon.decomposition,
  lemon.definition,
  lemon.edge,
  lemon.element,
  lemon.entry,
  lemon.equivalent,
  lemon.example,
  lemon.extrinsicArg,
  lemon.Form,
  lemon.formVariant,
  lemon.Frame,
  lemon.generates,
  lemon.hiddenRef,
  lemon.incompatible,
  lemon.isA,
  lemon.isReferenceOf,
  lemon.isSenseOf,
  lemon.language,
  lemon.leaf,
  lemon.LemonElement,
  lemon.LexicalEntry,
  lemon.lexicalForm,
  lemon.LexicalSense,
  lemon.lexicalVariant,
  lemon.Lexicon,
  lemon.marker,
  lemon.MorphPattern,
  lemon.MorphTransform,
  lemon.narrower,
  lemon.nextTransform,
  lemon.Node,
  lemon.objOfProp,
  lemon.onMorpheme,
  lemon.optional,
  lemon.otherForm,
  lemon.Part,
  lemon.pattern,
  lemon.Phrase,
  lemon.PhraseElement,
  lemon.phraseRoot,
  lemon.prefRef,
  lemon.property,
  lemon.propertyDomain,
  lemon.propertyRange,
  lemon.PropertyValue,
  lemon.Prototype,
  lemon.reference,
  lemon.representation,
  lemon.rule,
  lemon.semArg,
  lemon.sense,
  lemon.SenseCondition,
  lemon.SenseContext,
  lemon.SenseDefinition,
  lemon.senseRelation,
  lemon.separator,
  lemon.subjOfProp,
  lemon.subsense,
  lemon.synArg,
  lemon.synBehavior,
  lemon.SynRoleMarker,
  lemon.topic,
  lemon.transform,
  lemon.tree,
  lemon.UsageExample,
  lemon.value,
  lemon.Word,
  lemon.writtenRep
])

def leniter(iterator):
    """leniter(iterator): return the length of an iterator, consuming it."""
    if hasattr(iterator, "__len__"):
        return len(iterator)
    nelements = 0
    for _ in iterator:
        nelements += 1
    return nelements

def computeTypes(g,elem):
    ct = set()
    for pred, obj in g.predicate_objects(elem):
        if pred in lemonPropDomains.keys():
            ct.add(lemonPropDomains[pred])
        elif pred in lexinfoProps.keys():
            ct.add(lemonPropDomains[lexinfoProps[pred]])
        elif pred in lemonDataProperties and not isinstance(obj,Literal):
            err("DP_INVALID_OBJ","URI as object of " + pred)
        elif pred not in lemonDataProperties and isinstance(obj,Literal):
            err("OP_INVALID_OBJ","Literal as object of " + pred)
    for subj, pred in g.subject_predicates(elem):
        if pred in lemonPropRanges:
            ct.add(lemonPropRanges[pred])
        elif pred in lexinfoProps.keys():
            ct.add(lemonPropRanges[lexinfoProps[pred]])
    return ct


def validateLemonElement(g,types,elem):
    def inlemon(uri): return uri.startswith(lemon) and uri != lemon.Word and uri != lemon.Phrase and uri != lemon.Part

    if elem in types.keys():
        computedTypes = set(types[elem]) | computeTypes(g,elem)
    else:
        computedTypes = computeTypes(g,elem)
    if len(filter(inlemon,computedTypes)) != 1: 
        warn("MULT_TYPES",elem + " has multiple types: " + str([str(t)[35:] for t in computedTypes]))
    for type in computedTypes:
        if type == lemon.LexicalEntry:
            validateLexicalEntry(g,types,elem)
        if type == lemon.LexicalSense:
            validateLexicalSense(g,types,elem)
        if type == lemon.Form:
            validateForm(g,types,elem)
        if type == lemon.Definition:
            validateDefinition(g,types,elem)
        if type == lemon.Component:
            validateComponent(g,types,elem)
        if type == lemon.Lexicon:
            validateLexicon(g,types,elem)
        if type == lemon.MorphTransform:
            validateMorphTransform(g,types,elem)
        if type == lemon.UsageExample:
            validateUsageExample(g,types,elem)

def validateLexicalEntry(g,types,elem):
    ncanonicalForms = leniter(g.objects(elem,lemon.canonicalForm)) 
    if ncanonicalForms == 0:
        err("ENTRY_NO_CAN_FORM","Lexical Entry " + elem + " does not have a canonical form")
    elif ncanonicalForms > 1:
        err("ENTRY_MANY_CAN_FORMS","Lexical Entry " + elem + " has multiple canonical forms")
    nlanguages = leniter(g.objects(elem,lemon.language))
    if nlanguages == 0:
        note("ENTRY_NO_LANG","Lexical Entry " + elem + " does not have a language")
    elif nlanguages > 1:
        err("ENTRY_MANY_LANG","Lexical Entry " + elem + " has multiple languages")
    nlabels = leniter(g.objects(elem,RDFS.label))
    if nlabels == 0:
        note("ENTRY_NO_RDFS_LABEL","Lexical Entry " + elem + " does not have a RDFS label")

def validateLexicalSense(g,types,elem):
    nreferences = leniter(g.objects(elem,lemon.reference))
    nsubsense = leniter(g.objects(elem,lemon.subsense))
    if nreferences == 0 and nsubsense == 0:
        err("SENSE_NO_REF","Lexical Sense " + elem + " does not have a reference")
    elif nreferences > 1:
        warn("SENSE_MANY_REF","Lexical Sense " + elem + " has multiple references")

def validateForm(g,types,elem):
    nwrittenrep = leniter(g.objects(elem,lemon.writtenRep))
    if nwrittenrep == 0:
        err("FORM_NO_REP","Form " + elem + " does not have a written representation")

def validateComponent(g,types,elem):
    ncomponents = leniter(g.objects(elem,lemon.element))
    if ncomponents == 0:
        warn("COMPONENT_NO_ELEM","Component " + elem + " does not have an element")
    if ncomponents > 1:
        err("COMPONENT_MANY_ELEM","Component " + elem + " has more than one element")

def validateDefinition(g,types,elem):
    nvalues = leniter(g.objects(elem,lemon.value))
    if nvalues == 0:
        err("DEFN_NO_VALUE","Definition " + elem + " does not have a value")

def validateLexicon(g,types,elem):
    nentries = leniter(g.objects(elem,lemon.entry))
    nlanguages = leniter(g.objects(elem,lemon.language))
    if nentries == 0:
        err("LEXICON_EMPTY","Lexicon " + elem + " contains no values")
    if nlanguages == 0:
        err("LEXICON_NO_LANG","Lexicon " + elem + " does not have a language")
    elif nlanguages > 1:
        err("LEXICON_MANY_LANG","Lexicon " + elem + " has multiple languages")

def validateMorphTransform(g,types,elem):
    nrules = leniter(g.objects(elem,lemon.rule))
    ngenerates = leniter(g.objects(elem,lemon.generates))
    if nrules == 0:
        err("MT_NO_RULE","Morph Transform " + elem + " does not have a rule")
#   Actually OK not to have a prototype, see Ex. 75 http://www.lemon-model.net/lemon-cookbook/node37.html
#    if ngenerates == 0:
#        err("MT_NO_PROTOTYPE","Morph Transform " + elem + " does not generate any prototypes")

def validateUsageExample(g,types,elem):
    nvalues = leniter(g.objects(elem,lemon.value))
    if nvalues == 0:
        err("EXAMPLE_NO_VALUE","Definition " + elem + " does not have a value")

def validateBoolLiteral(lit):
    if lit.datatype != XSD.boolean:
        warn("BOOL_NO_TYPE","Boolean value not marked as xsd:boolean")
    if lit.lower() != "true" and lit.lower() != "false" and lit != "1" and lit != "0":
        err("BOOL_BAD_VALUE","Invalid boolean value: " + lit)

languageRegex = "^(...?)(-[A-Za-z]{4})?(-[A-Za-z]{2}|-[0-9]{3})?((-[A-Za-z0-9]{5,8}|-[0-9][A-Za-z0-9]{3})*)((-[A-WY-Za-wy-z0-9]-\\w{2,8})*)(-[Xx]-\\w{1,8})?$"

def validateLanguage(l):
    if not re.match(languageRegex,l):
        err("BAD_LANG","Invalid language code: " + l)

def validateRule(rule):
    if rule.count("~") != 1 or rule.count("/") > 1:
        err("BAD_RULE","Invalid rule: " + rule)

def validateText(lit):
    if lit.language is None:
        err("NO_LANG","Language tag missing from literal " + lit)
    else:
        validateLanguage(lit.language)


def note(code,msg):
    global suspicious
    global endOfMessage
    if outputFormat == "txt":
        print >>endOfMessage, "[NOTE ] " + msg 
    elif outputFormat == "xml":
        print >>endOfMessage, "<note code=\""+code+"\">" + escape(msg) + "</note>" 
    elif outputFormat == "html":
        print >>endOfMessage, "<div class=\"lemon-validator-note\">" + escape(msg) + " <a href=\"errors.html#" + code.lower() + "\">["+code+"]</a></div>" 
    suspicious = suspicious + 1

def warn(code,msg):
    global minor
    global warnOfMessage
    if outputFormat == "txt":
        print >>warnOfMessage, "[WARN ] " + msg
    elif outputFormat == "xml":
        print >>warnOfMessage, "<warn code=\""+code+"\">" + escape(msg) + "</warn>"
    elif outputFormat == "html":
        print >>warnOfMessage, "<div class=\"lemon-validator-warn\">" + escape(msg) + " <a href=\"errors.html#" + code.lower() + "\">["+code+"]</a></div>"
    minor = minor + 1

def err(code,msg):
    global major
    if outputFormat == "txt":
        print("[ERROR] " + msg)
    elif outputFormat == "xml":
        print("<error code=\""+code+"\">" + escape(msg) + "</error>")
    elif outputFormat == "html":
        print("<div class=\"lemon-validator-error\">" + escape(msg) + " <a href=\"errors.html#" + code.lower() + "\">["+code+"]</a></div>")
    major = major + 1

def main(argv):
    global outputFormat
    global endOfMessage
    global warnOfMessage
    optlist, args = getopt.getopt(argv[1:],"f:o:")
    optdict = dict(optlist)
    if len(args) != 1:
        print("Usage:\n\t./lemon-validator.py [-f xml|turtle] [-o txt|xml|html] file:/lemon-file.rdf")
        exit()

    g = Graph()

    if "-o" in optdict.keys():
        outputFormat = optdict["-o"]
        if outputFormat != "txt" and outputFormat != "xml" and outputFormat != "html":
            print("Invalid argument -o " + outputFormat)
            exit()
        if outputFormat == "xml":
            print("<report>")

    try:
        if "-f" in optdict.keys():
            g.parse(args[0],format=optdict["-f"])
        else:
            g.parse(args[0])
    except Exception as e: 
        err("INVALID_RDF","Failed to parse as RDF: " + str(e))
        exit()

    types = {}

    for subj, pred, obj in g:
        if pred == RDF.type:
            if subj in types.keys():
                types[subj].append(obj)
            else:
                types[subj] = [obj]
        elif pred == RDFS.subPropertyOf:
            if subj in types.keys():
                types[subj].add(obj)
            else:
                types[subj] = set([obj])
        if subj.startswith(lemon) and subj not in lemonURIs:
            err("NOT_LEMON_URI","Not a lemon URI : " + subj)
        elif obj.startswith(lemon) and obj not in lemonURIs:
            err("NOT_LEMON_URI","Not a lemon URI: " + obj)
        elif pred.startswith(lemon) and pred not in lemonURIs:
            err("NOT_LEMON_URI","Not a lemon URI: " + pred)
        if isinstance(subj,BNode) and pred != RDF.first and pred != RDF.rest:
            warn("BNODE","Blank node usage: _:bnode " + pred + " " + obj);

    checked = {}

    for subj, pred, obj in g:
        if pred in lemonDataProperties and isinstance(obj,Literal):
            if pred == lemon.optional:
                validateBoolLiteral(obj)
            elif pred == lemon.language:
                if obj.language is not None:
                    err("LANG_ON_LANG","Language tag on language identifier")
                validateLanguage(obj)
            elif pred == lemon.rule:
                if obj.language is not None:
                    warn("LANG_ON_RULE","Language tag on morphological rule")
                validateRule(obj)
            else:
                validateText(obj)
        if pred.startswith(lemon):
            if subj not in checked:
                validateLemonElement(g,types,subj)
                checked[subj] = True
        elif pred.startswith(RDF.uri) or pred.startswith(RDFS.uri) or pred.startswith(OWL) or pred.startswith(lexinfo) or pred.startswith(lexinfoAlt) or pred.startswith(oils):
            True
        elif pred in types.keys() and OWL.AnnotationProperty in types[pred]:
            True
        else:
            note("UNRECOGNIZED","Unrecognized triple: " + str(subj) + " " + str(pred) + " " + str(obj))
        
    print(warnOfMessage.getvalue())
    print(endOfMessage.getvalue())

    if outputFormat == "txt":
        print("There were " + str(suspicious) + " advisories, " + str(minor) + " warnings and " + str(major) + " errors")
    elif outputFormat == "xml":
        print("</report>")
    elif outputFormat == "html" and suspicious == 0 and minor == 0 and major == 0:
        print("<div>No errors</div>")

if __name__=='__main__': main(sys.argv)
