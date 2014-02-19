lemonUby Modelling
==================

Basic Lexicography
------------------

lemonUby is based on the [lemon](http://lemon-model.net) model as proposed in
the [Monnet project](http://www.monnet-project.eu/). As such lemonUby has the
following maing elements used in the modelling

* Lexicon
* LexicalEntry
* LexicalSense
* Form
* Reference

In addition, synsets are considered the ontological reference and are modelled
as SKOS Concepts, as such a basic example of a synset is as follows:

    <lemon:LexicalEntry rdf:about="WN_LexicalEntry_397">
      <lemon:canonicalForm>
        <lemon:Form rdf:about="WN_LexicalEntry_397#CanonicalForm">
          <lemon:writtenRep xml:lang="eng">cat</lemon:writtenRep>
        </lemon:Form>
      </lemon:canonicalForm>
      <lemon:sense rdf:resource="WN_Sense_573"/>
      <lemon:sense rdf:resource="WN_Sense_574"/>
      <lemon:sense rdf:resource="WN_Sense_575"/>
      <lemon:sense rdf:resource="WN_Sense_576"/>
      <lemon:sense rdf:resource="WN_Sense_577"/>
      <lemon:sense rdf:resource="WN_Sense_578"/>
      <lemon:sense rdf:resource="WN_Sense_579"/>
    </lemon:LexicalEntry>
    <lemon:LexicalSense rdf:about="WN_Sense_573">
      <uby:index>5</uby:index>
      <lemon:reference rdf:resource="WN_Synset_16108"/>
    </lemon:LexicalSense>
    ...
    <skos:Concept rdf:about="WN_Synset_16108">
      <rdfs:comment xml:lang="eng">A whip with nine knotted cords</rdfs:comment>
    </skos:Concept>


Further Examples
----------------

Much of the modelling is based on LMF, in particular the [UBY-LMF](http://www.ukp.tu-darmstadt.de/data/lexical-resources/uby/uby-lmf/)
and the [Lexical Markup Framework](http://www.lexicalmarkupframework.org) in
general. We also recommend that you consult the [cookbook](/learn/cookbook.php)
and in particular the LMF-_lemon_ mapping given in the
[appendix](/lemon-cookbook/node46.html). 

lemonUby Specific modelling
---------------------------

* `uby:index`: This property is used to give the order of senses of a lexical
  entry (1 is the first order)
* `uby:monolingualExternalRef`: These give provenance information about how an
  element was derived from the original resource, for example a sense in the
  WordNet data is labelled as:

        <lemon:LexicalSense rdf:about="WN_Sense_573">
           <uby:monolingualExternalRef>
             <uby:externalReference>[POS: noun] cat%1:06:00::</uby:externalReference>
             <uby:externalSystem>Wordnet 3.0 part of speech and sense key</uby:externalSystem>
           </uby:monolingualExternalRef>`
        </lemon:LexicalSense>`

* `uby:Frequency`: This is used to indicate the frequency of a sense or lexical
  entry in a corpus. It has three properties: `uby:corpus`,
  `uby:frequencyValue`, `uby:generator`
* `uby:SemanticLabel`: This is used to give semantic information about certain
  senses or argument structures, it consists of a string `uby:label` and a
  `uby:type` or `uby:quantification`.
* `uby:equivalent`: This property is used to indicate translations of lexical
  entries that do not have a lexical entry, for [example](http://lemon-model.net/lexica/uby/WktEN/WktEN_sense_1).

