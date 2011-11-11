<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Rdf\TripleStore;

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

use TYPO3\FLOW3\Annotations as FLOW3;

use Doctrine\ORM\Events;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class DoctrineEventSubscriber implements \Doctrine\Common\EventSubscriber {

	/**
	 * @var \SandstormMedia\Semantic\Rdf\TripleStore\StoreConnectorInterface
	 * @FLOW3\Inject
	 */
	protected $storeConnector;

	public function getSubscribedEvents() {
		return array(Events::postPersist, Events::postUpdate, Events::preRemove);
	}
	public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $lifecycleEventArgs) {
		$this->postUpdate($lifecycleEventArgs);
	}

	public function preRemove(\Doctrine\ORM\Event\LifecycleEventArgs $lifecycleEventArgs) {
		$removedEntity = $lifecycleEventArgs->getEntity();
		$this->storeConnector->removeObject($removedEntity);
	}

	public function postUpdate(\Doctrine\ORM\Event\LifecycleEventArgs $lifecycleEventArgs) {
		$changedEntity = $lifecycleEventArgs->getEntity();
		$this->storeConnector->addOrUpdateObject($changedEntity);
	}
}
?>