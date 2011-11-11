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

namespace SandstormMedia\Semantic\Core;



use TYPO3\FLOW3\Annotations as FLOW3;

use \SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class RdfGenerator {

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @FLOW3\Inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;


	/**
	 * @var \SandstormMedia\Semantic\Core\Schema\ClassSchemaResolver
	 * @FLOW3\Inject
	 */
	protected $classSchemaResolver;

	/**
	 * Build a graph for a given object
	 *
	 * @param object $object
	 * @return Graph
	 */
	public function buildGraphForObject($object) {
		$domainModelObjectName = get_class($object);
		$identifier = $this->persistenceManager->getIdentifierByObject($object);
		return $this->buildGraph($domainModelObjectName, $identifier);
	}

	/**
	 *
	 * @param string $domainModelObjectName
	 * @param string $identifier
	 * @return Graph
	 */
	public function buildGraph($domainModelObjectName, $identifier) {
		$object = $this->persistenceManager->getObjectByIdentifier($identifier, $domainModelObjectName);
		if ($object === NULL) {
			throw new \Exception("TODO: Object not found.");
		}
		$rdfGraph = new Graph();
		$rdfSubject = $this->getResourceUriForObject($object);

		$propertyNames = $this->classSchemaResolver->getPropertyNames($domainModelObjectName);

		foreach ($propertyNames as $propertyName) {
			$propertySchema = $this->classSchemaResolver->getPropertySchema($domainModelObjectName, $propertyName);
			$propertyValue = \TYPO3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);

			$this->buildTriplesForProperty($identifier, $propertyName, $propertyValue, $propertySchema, $rdfGraph, $rdfSubject);
		}

		$classSchema = $this->classSchemaResolver->getClassSchema($domainModelObjectName);

		if (isset($classSchema['rdfType'])) {
			$rdfGraph->add(new Triple(
				$rdfSubject,
				new NamedNode('rdf:type'),
				new NamedNode($classSchema['rdfType'])));
		}

		return $rdfGraph;
	}

	protected function buildTriplesForProperty($subjectDomainModelIdentifier, $propertyName, $propertyValue, $propertySchema, Graph $graph, $rdfSubject) {
		if (!isset($propertySchema['rdfType'])) return;

		$rdfPredicate = new NamedNode($propertySchema['rdfType']);

		if (!isset($propertySchema['rdfTripleGenerator'])) {
			throw new \Exception('rdfTripleGenerator not set for object ' . get_class($object), 1314440839);
		}
		$rdfTripleGenerator = $this->objectManager->get($propertySchema['rdfTripleGenerator']);

		if ($rdfTripleGenerator === NULL || !($rdfTripleGenerator instanceof RdfGeneration\TripleGeneratorInterface)) {
			throw new \Exception('rdTripleGenerator not found or no instance of TripleGeneratorInterface: "' . $propertySchema['rdfTripleGenerator'] . '",', 1314440848);
		}
		$rdfTripleGenerator->generate($subjectDomainModelIdentifier, $propertyName, $propertyValue, $propertySchema, $rdfSubject, $rdfPredicate, $graph);

/*
 * 		switch ($propertySchema['rdfSourceType']) {
			case 'Doctrine\Common\Collections\ArrayCollection':
				$collection = $propertyValue;
				if (class_exists($propertySchema['elementType'])) {
					foreach ($collection as $element) {
						$rdfObject = $this->getResourceUriForObject($element);
						$graph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
					}
				} else {
					throw new \Exception('Simple element collection types not yet supported!');
				}
				break;
			default:
				throw new \Exception('TODO: Type ' . $propertySchema['rdfSourceType'] . ' not supported');
		}*/
	}

	/**
	 * @api
	 */
	public function getResourceUriForObject($object) {
		$classSchema = $this->classSchemaResolver->getClassSchema($object);
		if (!isset($classSchema['rdfIdentityProvider'])) {
			throw new \Exception('rdfIdentityProvider not set for object ' . get_class($object), 1314440839);
		}
		$rdfIdentityProvider = $this->objectManager->get($classSchema['rdfIdentityProvider']);

		if ($rdfIdentityProvider === NULL || !($rdfIdentityProvider instanceof RdfGeneration\IdentityProvider\IdentityProviderInterface)) {
			throw new \Exception('rdfIdentityProvider not found or no instance of IdentityProviderInterface: "' . $classSchema['rdfIdentityProvider'] . '",', 1314440848);
		}

		return $rdfIdentityProvider->buildResourceUri($object);
	}
}
?>