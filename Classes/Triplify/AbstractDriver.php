<?php
/*                                                                        *
 * This script belongs to the "SandstormMedia.Semantic" package.          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3          *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * © 2011 Sandstorm Media UG (haftungsbeschränkt)                         *
 *        http://sandstorm-media.de                                       */
namespace SandstormMedia\Semantic\Triplify;
use TYPO3\FLOW3\Annotations as FLOW3;
use SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
use SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
use SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
use SandstormMedia\Semantic\Core\Rdf\Concept\Graph;

/**
 */
class AbstractDriver {
	/**
	 * @var \PDO
	 */
	protected $pdoConnection;

	/**
	 * @var \SandstormMedia\Semantic\Core\Rdf\Concept\Dataset
	 */
	protected $rdfDataset;

	/**
	 * @var string
	 */
	protected $baseUri;

	/**
	 * @var array
	 * @api
	 */
	protected $objects = array();

	/**
	 * @var array
	 */
	protected $namedGraphs = array();

	/**
	 * Key is a URI, and value is TRUE if a RDF Type Statement has been already
	 * built for this graph. This is an optimization to only add rdf:type once.
	 *
	 * @var array
	 */
	private $rdfTypeStatementAlreadyBuilt = array();

	/**
	 * This is a helper to identify which Graphs are assigned to
	 * which resources.
	 *
	 * @var array
	 */
	private $namedGraphCache = array();

	/**
	 * Default constructor.
	 *
	 * @param \PDO $pdoConnection
	 * @param \SandstormMedia\Semantic\Core\Rdf\Concept\Dataset $rdfDataset
	 */
	public function __construct(\PDO $pdoConnection, \SandstormMedia\Semantic\Core\Rdf\Concept\Dataset $rdfDataset, $baseUri) {
		$this->pdoConnection = $pdoConnection;
		$this->rdfDataset = $rdfDataset;
		$this->baseUri = rtrim($baseUri, '/');
	}

	/**
	 * Start Triplifying.
	 *
	 * @return void
	 */
	public function run() {
		foreach ($this->objects as $objectName => $uriPattern) {
			foreach ($this->getQueries($objectName) as $sqlQuery) {
				$this->runSqlQuery($objectName, $uriPattern, $sqlQuery);
			}
		}
	}

	/**
	 * Executes one specified query.
	 *
	 * @param string $objectName
	 * @param string $uriPattern
	 * @param string $sqlQuery
	 * @return void
	 */
	protected function runSqlQuery($objectName, $uriPattern, $sqlQuery) {
		$results = $this->pdoConnection->query($sqlQuery);
		while($result = $results->fetch(\PDO::FETCH_ASSOC)) {
			$subject = $this->buildUri($result, $uriPattern);
			$dataTypeTriple = $this->addRdfTypeStatementIfNeeded($objectName, $subject);
			if ($dataTypeTriple !== FALSE) {
				$this->addAndRegisterNamedGraphs($objectName, $result, $dataTypeTriple);
			}

			foreach ($result as $columnIdentifier => $value) {
				if ($columnIdentifier[0] === '_') {
					continue;
				}

				$rdfSubject = new NamedNode($subject);

				if (preg_match('/(.*)->(.*)\(\)/', $columnIdentifier, $matches)) {
					$rdfPredicate = new NamedNode($matches[1]);
					$postProcessingMethodName = $matches[2];
					$rdfObject = $this->$postProcessingMethodName($value);
				} elseif (preg_match('/(.*)->(.*)/', $columnIdentifier, $matches)) {
					$rdfPredicate = new NamedNode($matches[1]);
					$targetObjectName = $matches[2];

					$rdfObject = new NamedNode(
						$this->buildUri(
								// To build the URI for the foreign reference,
								// we hardcode the _id column.
							array('_id' => $value),
							$this->objects[$targetObjectName]
						)
					);
				} else {
					if ($value === '') {
						// We do not need to add triples for empty values
						continue;
					}
					$rdfPredicate = new NamedNode($columnIdentifier);
					$rdfObject = new Literal($value);
				}

				$triple = new Triple($rdfSubject, $rdfPredicate, $rdfObject);
				$this->addTripleToDataset($triple);
			}
		}
	}

	/**
	 * Adds a triple to the dataset. Respects the named graphs, based on the
	 * namedGraphs-Configuration. Uses the namedGraphCache to identify additional graphs.
	 *
	 * @return void
	 */
	public function addTripleToDataset(Triple $triple) {
			// add the triple to the default graph.
		$this->rdfDataset->add($triple);
			// check if the subject is a aggregate root for a named graph.
		if (isset($this->namedGraphCache[$triple->getSubject()->valueOf()])) {
			$cachedConfig = $this->namedGraphCache[$triple->getSubject()->valueOf()];
				// iterate across all registered named graphs
			foreach ($cachedConfig as $graphName => $graphConfig) {
				$graphNameNode = new NamedNode($graphName);
				if (isset($graphConfig['_only']) && isset($graphConfig['_exclude'])) throw new \SandstormMedia\Semantic\Exception('You can not use both _only and _exclude', 1339590681);
				$addToGraph = FALSE;
				if (isset($graphConfig['_only'])) {
					foreach ($graphConfig['_only'] as $predicate) {
						$predicateNode = new NamedNode($predicate);
						if (!$predicateNode->equals($triple->getPredicate())) continue;
						$addToGraph = TRUE;
						break;
					}
				} else if ($graphConfig['exclude']) {
					$addToGraph = TRUE;
					foreach ($graphConfig['_exclude'] as $predicate) {
						$predicateNode = new NamedNode($predicate);
						if (!$predicateNode->equals($triple->getPredicate())) continue;
						$addToGraph = FALSE;
						break;
					}
				}
				if ($addToGraph === TRUE) {
					$this->rdfDataset->add(clone $triple, $graphNameNode);
					if (isset($graphConfig['_descend'][$predicate])) {
						$this->namedGraphCache[$triple->getObject()->valueOf()][$graphName] = $graphConfig['_descend'][$predicate];
					}
				}

			}
		}
	}

	/**
	 * Replace every element in $uriPattern; and use $replacements for that.
	 * Furthermore, replaces the special placeholder BASEURI.
	 *
	 * @param array $result
	 * @param string $uriPattern
	 * @return string
	 */
	protected function buildUri(array $replacements, $uriPattern) {
		$replacements['BASEURI'] = $this->baseUri;
		foreach ($replacements as $search => $replace) {
			$uriPattern = str_replace('{' . $search . '}', $replace, $uriPattern);
		}
		return $uriPattern;
	}

	/**
	 * Adds the rdf type statement for an added uri, if it is not already
	 * added. Returns the added triple or false if already existent.
	 *
	 * @param string $objectName The name of the currently processed object.
	 * @param string $subject The subject of the next triple.
	 * @return mixed Triple/FALSE
	 */
	protected function addRdfTypeStatementIfNeeded($objectName, $subject) {
		if (!isset($this->rdfTypeStatementAlreadyBuilt[$subject]) && $this->getType($objectName) !== NULL) {
			$triple = new Triple(
				new NamedNode($subject),
				new NamedNode('rdf:type'),
				new NamedNode($this->getType($objectName))
			);
			$this->addTripleToDataset($triple);
			$this->rdfTypeStatementAlreadyBuilt[$subject] = TRUE;
			return $triple;
		}
		return FALSE;
	}

	/**
	 * Adds a new named graph based on the $this->namedGraphs configuration. Registers
	 * action handlers on the dataset, that handle the insertion of triples into the named
	 * graph.
	 *
	 * @param string $objectName
	 * @param array $result
	 * @param Triple $rdfTypeTriple
	 */
	protected function addAndRegisterNamedGraphs($objectName, array $result, Triple $rdfTypeTriple) {
		foreach ($this->namedGraphs as $namePattern => $configuration) {
			if (!isset($configuration[$this->getType($objectName)])) return;
			$graphName = new NamedNode($this->buildUri($result, $namePattern));
			$graph = new Graph($graphName);
			$graph->add(clone $rdfTypeTriple);
			$this->rdfDataset->addGraph($graph);
			$this->namedGraphCache[$rdfTypeTriple->getSubject()->valueOf()][$graphName->valueOf()] = $configuration[$this->getType($objectName)];
		}
	}

	/**
	 * Resolves the queries by naming convention.
	 *
	 * @param string $objectName
	 * @return array
	 */
	protected function getQueries($objectName) {
		$variableName = $objectName . 'Queries';
		return $this->$variableName;
	}

	/**
	 * Resolves the object type by naming convention.
	 *
	 * @param string $objectName
	 * @return string
	 */
	protected function getType($objectName) {
		$variableName = $objectName . 'Type';
		return $this->$variableName;
	}

	/**
	 * Convert the given value to an XML DateTime
	 *
	 * @param string $value
	 * @return Literal
	 * @api
	 */
	protected function asDateTime($value) {
		$dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
		return new Literal($dateTime);
	}
}
?>