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

use \F3\Semantic\Domain\Model\Triple;
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

		$triples = $this->buildTriples($domainModelObjectName, $identifier);

		$this->view->assign('triples', $triples);
	}

	protected function buildTriples($domainModelObjectName, $identifier) {
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

		$tripleContainer = new \F3\Semantic\Domain\Model\TripleContainer();
		$rdfSubject = $this->buildResourceUri($domainModelObjectName, $identifier);

		foreach ($rdfSchema['properties'] as $propertyName => $rdfPredicate) {
			$propertySchema = $schema->getProperty($propertyName);
			switch ($propertySchema['type']) {
				case 'string':
				case 'DateTime':
					$rdfObject = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);

					$tripleContainer->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
					break;
				case 'Doctrine\Common\Collections\ArrayCollection':
					$collection = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object, $propertyName);
					if (class_exists($propertySchema['elementType'])) {
						foreach ($collection as $element) {
							$rdfObject = $this->buildResourceUri($propertySchema['elementType'], $this->persistenceManager->getIdentifierByObject($element));
							$tripleContainer->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
						}
					} else {
						throw new \Exception('Simple element collection types not yet supported!');
					}
					break;
				default:
					throw new \Exception('TODO: Type ' . $propertySchema['type'] . ' not supported');
			}
		}

		unset($rdfSchema['properties']);

		foreach ($rdfSchema as $rdfPredicate => $rdfObject) {
			$tripleContainer->add(new Triple($rdfSubject, $rdfPredicate, $rdfObject));
		}
		return $tripleContainer;
	}
	protected function buildResourceUri($domainModelObjectName, $identifier) {
		return $this->uriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor('show', array(
					'dataType' => str_replace('\\', '_', $domainModelObjectName),
					'identifier' => $identifier),
				'RdfIdentity'); // TODO: we need some kind of Identity service later.
	}
}
?>