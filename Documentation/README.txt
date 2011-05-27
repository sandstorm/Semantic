Implementation of: RDF Interfaces 1.0 Working Draft
===================================================

We implement the RDF Interfaces 1.0 Working Draft (10. May 2011) with the following modifications:

* All readonly properties can only be set through the constructor.
* toString() -> __toString()

2.2.2 -- Graphs:
* Method "forEach" was removed, but you can instead use a "foreach" loop over the graph to get all triples.
* TODO: implement methods "match", "merge", "removeMatches"

2.3.1 -- RDFNode:
* "interfaceName" property omitted, you should use instanceof instead
* "equals" does not take *any* object, but only takes other "RDFNode" instances.
* "getNominalValue" has been omitted, as there is a "valueOf" method.

2.3.4 -- Literals:
* property "datatype" has been named "dataType", for better consistency.

2.4.1 Triple Filters
* ... should be implemented as Closure, and receive the Input triple, should return TRUE or FALSE.

2.4.2 Triple Callbacks
* ... should be implemented as Closure, and receive the Input triple and the Graph object, do NOT return anything.

2.4.3. Triple Actions
* ... are not explicitely implemented; as this is just a special form of "Triple Callback". At all places where "Triple Actions" can
  be added in the specification, you can instead add "Triple Callbacks".

3.2.1. Prefix Maps
* TODO: implement "setDefault", "addAll"
* the methods "get", "set" and "remove" exist explicitely; we could also use magic methods or ArrayAccess there... Not sure about what to use -- TODO

3.2.2. Term Maps
* TODO: implement "setDefault", "addAll"
* the methods "get", "set" and "remove" exist explicitely; we could also use magic methods or ArrayAccess there... Not sure about what to use -- TODO

3.2.3. Profiles
* TODO: implement "importProfile, setDefaultPrefix, setDefaultVocabulary"

3.3.1. RDF Environment
* We do NOT need support for all these *create*-Functions, as one should use Dependency Injection / new in our case.
* That's why we do not have an implementation of "RDF Environment".

1. Introduction
1.1 Conformance
2. RDF Concept Interfaces
2.1 Overview
2.2 Data Structures
2.2.1 Triples						OK
2.2.2 Graphs						OK
2.3 Basic Node Types
2.3.1 Nodes							OK
2.3.2 Named Nodes					OK
2.3.3 Blank Nodes					OK
2.3.4 Literals						OK
2.4 Additional Interfaces
2.4.1 Triple Filters				IMPLEMENTED VIA CLOSURES
2.4.2 Triple Callbacks				IMPLEMENTED VIA CLOSURES
2.4.3 Triple Actions				IMPLEMENTED VIA CLOSURES
3. RDF Environment Interfaces
3.1 Overview
3.2 Terms, Prefixes and Profiles
3.2.1 Prefix Maps					OK
3.2.2 Term Maps						NOT IMPLEMENTED YET
3.2.3 Profiles						OK
3.3 High level API
3.3.1 RDF Environment
4. RDF Data Interfaces
4.1 Overview
4.2 Parsing and Serializing Data
4.2.1 Data Parsers
4.2.2 Data Serializers
4.2.3 Additional Interfaces
A. Acknowledgements
B. References
B.1 Normative references
B.2 Informative references



Further implementation notes
============================

We have furthermore added Domain\Model\Rdf\Environment\DefaultProfile, which sets some
default prefix mappings as specified by the standard, AND imports all namespaces from the
Settings.
This one is a SINGLETON, which is USED INSIDE NamedNode construction to expand CURIEs to IRIs.
Thus, you can set CURIEs as well on NamedNode instances, which are then transparently converted
to IRIs. This is an *addition* to the standard!