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