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

namespace SandstormMedia\Semantic\Rdf\TripleStore;




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