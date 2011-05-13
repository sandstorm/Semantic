<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
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
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
 * @scope singleton
 */
class RdfDataController extends \F3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @inject
	 */
	protected $reflectionService;

	/**
	 * @var \F3\Semantic\Domain\Service\ResourceUriService
	 * @inject
	 */
	protected $resourceUriService;

	/**
	 * Default action of the backend controller.
	 *
	 * @param string $dataType
	 * @param string $identifier
	 * @return string
	 * @skipCsrfProtection
	 */
	public function showAction($dataType, $identifier) {
		$domainModelObjectName = str_replace('_', '\\', $dataType);

		if (!$this->objectManager->isRegistered($domainModelObjectName)) {
			throw new \Exception("TODO: Data Type not found.");
		}

		$graph = $this->buildGraph($domainModelObjectName, $identifier);

		$this->view->assign('graph', $graph);
	}

	protected function buildGraph($domainModelObjectName, $identifier) {
		$object = $this->persistenceManager->getObjectByIdentifier($identifier, $domainModelObjectName);
		if ($object === NULL) {
			throw new \Exception("TODO: Object not found.");
		}

		$schema = $this->reflectionService->getClassSchema($domainModelObjectName);
		if ($schema === NULL) {
			throw new \Exception("TODO: Schema not found.");
		}

		$rdfSchema = $this->settings['PropertyMapping'][$domainModelObjectName];
		if (!$rdfSchema) {
			throw new \Exception("TODO: RDF Schema not found.");
		}

		$rdfGraph = new Graph();
		$rdfSubject = $this->resourceUriService->buildResourceUri($object, $this->uriBuilder);

		foreach ($rdfSchema['properties'] as $propertyName => $propertyConfiguration) {
			$propertySchema = $schema->getProperty($propertyName);

			if (!isset($propertyConfiguration['type'])) {
				continue;
			}
			$rdfPredicate = new NamedNode($propertyConfiguration['type']);
			switch ($propertySchema['type']) {
				case 'string':
				case 'DateTime':
					$rdfObject = new Literal(\F3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName));

					$rdfGraph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
					break;
				case 'Doctrine\Common\Collections\ArrayCollection':
					$collection = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);
					if (class_exists($propertySchema['elementType'])) {
						foreach ($collection as $element) {
							$rdfObject = $this->resourceUriService->buildResourceUri($element, $this->uriBuilder);
							$rdfGraph->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
						}
					} else {
						throw new \Exception('Simple element collection types not yet supported!');
					}
					break;
				default:
					throw new \Exception('TODO: Type ' . $propertySchema['type'] . ' not supported');
			}
		}

		$rdfGraph->add(new Triple(
			$rdfSubject,
			new NamedNode('rdf:type'),
			new NamedNode($rdfSchema['type'])));


		return $rdfGraph;
	}
}
?>