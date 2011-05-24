<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \F3\FLOW3\Package\Package as BasePackage;

/**
 * The TYPO3 Package
 */
class Package extends BasePackage {
	public function boot(\F3\FLOW3\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect('F3\FLOW3\Core\Bootstrap', 'bootstrapReadyRuntime', function($slot) use (&$bootstrap) {
			/*if (!$bootstrap->getObjectManager()->isRegistered('F3\Semantic\TripleStore\DoctrineEventSubscriber')) {
				var_dump("X");
				// Hack if we are in Compile-Time object manager
				return;
			} else {
				var_dump('Y');
			}*/
			$entityManager = $bootstrap->getObjectManager()->get('Doctrine\Common\Persistence\ObjectManager');
			$entityManager->getEventManager()->addEventSubscriber($bootstrap->getObjectManager()->get('F3\Semantic\TripleStore\DoctrineEventSubscriber'));
		});
	}
}

?>