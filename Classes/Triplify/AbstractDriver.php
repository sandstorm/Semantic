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

/**
 */
class AbstractDriver {
	/**
	 * @var \PDO
	 */
	protected $pdoConnection;

	/**
	 * @var \SandstormMedia\Semantic\Core\Rdf\Concept\Graph
	 */
	protected $rdfGraph;

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
	 * Key is a URI, and value is TRUE if a RDF Type Statement has been already
	 * built for this graph. This is an optimization to only add rdf:type once.
	 *
	 * @var array
	 */
	private $rdfTypeStatementAlreadyBuilt = array();

	/**
	 * @param \PDO $pdoConnection
	 * @param \SandstormMedia\Semantic\Core\Rdf\Concept\Graph $rdfGraph
	 */
	public function __construct(\PDO $pdoConnection, \SandstormMedia\Semantic\Core\Rdf\Concept\Graph $rdfGraph, $baseUri) {
		$this->pdoConnection = $pdoConnection;
		$this->rdfGraph = $rdfGraph;
		$this->baseUri = rtrim($baseUri, '/');
	}

	public function run() {
		foreach ($this->objects as $objectName => $uriPattern) {
			foreach ($this->getQueries($objectName) as $sqlQuery) {
				$this->runSqlQuery($objectName, $uriPattern, $sqlQuery);
			}
		}
	}

	protected function runSqlQuery($objectName, $uriPattern, $sqlQuery) {
		$results = $this->pdoConnection->query($sqlQuery);
		while($result = $results->fetch(\PDO::FETCH_ASSOC)) {
			$subject = $this->buildUri($result, $uriPattern);
			$this->addRdfTypeStatementIfNeeded($objectName, $subject);

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

				$this->rdfGraph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
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

	protected function addRdfTypeStatementIfNeeded($objectName, $subject) {
		if (!isset($this->rdfTypeStatementAlreadyBuilt[$subject]) && $this->getType($objectName) !== NULL) {
			$this->rdfGraph->add(new Triple(
				new NamedNode($subject),
				new NamedNode('rdf:type'),
				new NamedNode($this->getType($objectName))
			));
			$this->rdfTypeStatementAlreadyBuilt[$subject] = TRUE;
		}
	}

	protected function getQueries($objectName) {
		$variableName = $objectName . 'Queries';
		return $this->$variableName;
	}

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