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
namespace SandstormMedia\Semantic\Command;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The setup controller for the Blog package, for setting up some
 * data to play with.
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class TripleStoreCommandController extends \TYPO3\FLOW3\MVC\Controller\CommandController {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\MVC\Web\Routing\RouterInterface
	 */
	protected $router;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @FLOW3\Inject
	 * @var \SandstormMedia\Semantic\Core\Schema\ClassSchemaResolver
	 */
	protected $classSchemaResolver;

	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;

	/**
	 * @var \SandstormMedia\Semantic\Rdf\TripleStore\StoreConnectorInterface
	 * @FLOW3\Inject
	 */
	protected $storeConnector;

	/**
	 * Sets up a a blog with a lot of posts and comments which is a nice test bed
	 * for profiling.
	 *
	 * @return string
	 */
	public function importCommand() {
		putenv('FLOW3_REWRITEURLS=1');
		$routesConfiguration = $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_ROUTES);
		$this->router->setRoutesConfiguration($routesConfiguration);

		$objectCount = 0;
		foreach ($this->classSchemaResolver->getClassNamesWhichHaveASchema() as $className) {
			$query = $this->persistenceManager->createQueryForType($className);
			$objects = $query->execute();
			foreach ($objects as $object) {
				$this->storeConnector->addOrUpdateObject($object);
				$objectCount++;
			}
		}
		return sprintf('Updated/Created %d objects', $objectCount);
	}
}
?>