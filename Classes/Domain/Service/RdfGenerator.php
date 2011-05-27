<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "Semantic".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \F3\Semantic\Domain\Model\Rdf\Concept\Graph;
use \F3\Semantic\Domain\Model\Rdf\Concept\NamedNode;
use \F3\Semantic\Domain\Model\Rdf\Concept\Literal;
use \F3\Semantic\Domain\Model\Rdf\Concept\Triple;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class RdfGenerator {

	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \F3\Semantic\Domain\Service\ResourceUriService
	 * @inject
	 */
	protected $resourceUriService;

	/**
	 * @var F3\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var \F3\Semantic\Schema\ClassSchemaResolver
	 * @inject
	 */
	protected $classSchemaResolver;

	public function buildGraphForObject($object) {
		$domainModelObjectName = get_class($object);
		$identifier = $this->persistenceManager->getIdentifierByObject($object);
		return $this->buildGraph($domainModelObjectName, $identifier);
	}
	public function buildGraph($domainModelObjectName, $identifier) {
		$object = $this->persistenceManager->getObjectByIdentifier($identifier, $domainModelObjectName);
		if ($object === NULL) {
			throw new \Exception("TODO: Object not found.");
		}
		$rdfGraph = new Graph();
		$rdfSubject = $this->resourceUriService->buildResourceUri($object);

		$propertyNames = $this->classSchemaResolver->getPropertyNames($domainModelObjectName);

		foreach ($propertyNames as $propertyName) {
			$propertySchema = $this->classSchemaResolver->getPropertySchema($domainModelObjectName, $propertyName);
			$propertyValue = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);

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

	protected function buildTriplesForProperty($identifier, $propertyName, $propertyValue, $propertySchema, Graph $graph, $rdfSubject) {
		if (!isset($propertySchema['rdfType'])) return;

		$rdfPredicate = new NamedNode($propertySchema['rdfType']);

		$possibleExternalRdfReference = $this->externalReferenceRepository->findOneByUuidAndPropertyName($identifier, $propertyName);
		if ($possibleExternalRdfReference) {
			$rdfObject = new NamedNode($possibleExternalRdfReference->getValue());
			$graph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
			return;
		}

		switch ($propertySchema['type']) {
			case 'string':
			case 'DateTime':
				$rdfObject = new Literal($propertyValue);

				$graph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
				break;
			case 'Doctrine\Common\Collections\ArrayCollection':
				$collection = $propertyValue;
				if (class_exists($propertySchema['elementType'])) {
					foreach ($collection as $element) {
						$rdfObject = $this->resourceUriService->buildResourceUri($element);
						$graph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
					}
				} else {
					throw new \Exception('Simple element collection types not yet supported!');
				}
				break;
			default:
				throw new \Exception('TODO: Type ' . $propertySchema['type'] . ' not supported');
		}
	}
}
?>