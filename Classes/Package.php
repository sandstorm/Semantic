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
namespace SandstormMedia\Semantic;

use \TYPO3\FLOW3\Package\Package as BasePackage;

/**
 * The TYPO3 Package
 */
class Package extends BasePackage {
	public function boot(\TYPO3\FLOW3\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		/*$dispatcher->connect('TYPO3\FLOW3\Core\Bootstrap', 'bootstrapReady', function($slot) use (&$bootstrap) {
			if ($bootstrap->getObjectManager() instanceof \TYPO3\FLOW3\Object\CompileTimeObjectManager) {
				return;
			}
			$entityManager = $bootstrap->getObjectManager()->get('Doctrine\Common\Persistence\ObjectManager');
			$entityManager->getEventManager()->addEventSubscriber($bootstrap->getObjectManager()->get('SandstormMedia\Semantic\TripleStore\DoctrineEventSubscriber'));
		});*/
	}
}

?>