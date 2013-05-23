Lemon Validator Errors
======================

Errors
======

## INVALID_RDF

The RDF document did not parse. Check the error message for more details.

## DP_INVALID_OBJ

A datatype property was used with a URI or blank node as its object

### Example

    lexicon:form lemon:writtenRep :cat .

This should be a literal, e.g.,

    lexinfo:form lemon:writtenRep "cat"@eng .

## OP_INVALID_OBJ

An object property was used with a literal as its object

### Example

    lexicon:cat lemon:canonicalForm "cat"@eng .

Should be

    lexicon:cat lemon:canonicalForm lexicon:cat_canonicalForm .
    lexicon:cat_canonicalForm lemon:writtenRep "cat"@eng

## ENTRY_NO_CAN_FORM

A lexical entry has no canonical form

## ENTRY_MANY_CAN_FORMS

A lexical entry has more than one canonical form

## ENTRY_MANY_LANG

A lexical entry has more than one language marked

## SENSE_NO_REF

A lexical sense does not have a reference
 
## FORM_NO_REP

A form does not have a written representation

## COMPONENT_MANY_ELEM

A components has multiple elements

## DEFN_NO_VALUE

A definition is given without a `lemon:value`

### Example

    lexicon:sense lemon:definition "This is the definition"@eng

Should be

    lexicon:sense lemon:definition lexicon:sense_defn .
    lexicon:sense_defn lemon:value "This is the definition"@eng

## LEXICON_EMPTY

A lexicon does not contain any entries, please add `lemon:entry` triples between the lexicon and its entries

## LEXICON_NO_LANG

A lexicon is not annotated with a language

### Example

    :lexicon lemon:language "eng"

## MT_NO_RULE

A morphological transform was given without a rule

## MT_NO_PROTOTYPE

A morphological transform does not have a prototype to generate

## EXAMPLE_NO_VALUE

A usage example for a sense did not have a value 

### Example

    lexicon:sense lemon:example "This is the example"@eng

Should be

    lexicon:sense lemon:definition lexicon:sense_ex .
    lexicon:sense_ex lemon:value "This is the example"@eng

## BOOL_BAD_VALUE

A boolean value was not one of `true`,`false`,`1` or `0`

## BAD_LANG

A language annotation was not a valid code

### Example

    lexicon:form lemon:writtenRep "cat"@english

Should be
   
    lexicon:form lemon:writtenRep "cat"@eng

## BAD_RULE

A rule specified in a morphological transform was not valid. In particular the rule must contain the character "~" at least once, and a "/" at most once.

## NO_LANG

A literal was given without a language.

### Example

    lexicon:form lemon:writtenRep "cat"

Should be

    lexicon:form lemon:writtenRep "cat"@eng


## NOT_LEMON_URI

A URI was used starting with the _lemon_ namespace that was not a valid identifier.


## LANG_ON_LANG

A language tag identifier was add to a language tag string

### Example

    :lexicon lemon:language "eng"@eng

Should be

    :lexicon lemon:language "eng"

Warnings
========

## MULT_TYPES

An element had multiple incompatible types in the model

### Example

    lexicon:entry a lemon:LexicalEntry ;
      lemon:reference <http://www.example.com/ontology#entity> .

Here the URI is both stated as a `lemon:LexicalEntry` and inferred to be a `lemon:LexicalSense` (as it is the subject of a `lemon:reference` triple

## SENSE_MANY_REF

A lexical sense has multiple references, this infers ontological equivalence between each reference URI.

## COMPONENT_NO_ELEM

A component does not have a `lemon:element` triple

## BOOL_NO_TYPE

A boolean value was not typed

### Example

    lexicon:argument lemon:optional "true"^^xsd:boolean

## BNODE

Blank nodes are used as identifiers in the model, it is highly recommended that you do not use blank nodes to define a lexicon.

## LANG_ON_RULE

A langauge tag was added to a rule, this should not be necessary

### Example

    lexicon:transform lemon:rule "~s"@eng

Advisories
==========

## ENTRY_NO_LANG

A lexical entry does not have a language marked, it is advised that each lexical entry is marked with a language tag

### Example

    lexicon:entry a lemon:LexicalEntry ;
      lemon:language "eng" .

## ENTRY_NO_RDFS_LABEL

It is recommended for compatibility that a `rdfs:label` annotation is added to each entry.

### Example

    lexicon:entry a lemon:LexicalEntry ;
      lemon:canonicalForm lexicon:form ;
      rdfs:label "cat"@eng .
    lexicon:form lemon:writtenRep "cat"@eng .

## UNRECOGNIZED

A triple in the model was not interpreted as part of the lexicon-ontology model. You should verify that the triple is valid in your lexicon.

