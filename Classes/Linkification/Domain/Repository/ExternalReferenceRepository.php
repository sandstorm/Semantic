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

namespace SandstormMedia\Semantic\Linkification\Domain\Repository;

/*                                                                        *
 * This script belongs to the FLOW3 package "Conference".                 *
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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("singleton")
 */
class ExternalReferenceRepository extends \TYPO3\FLOW3\Persistence\Repository {

	/**
	 * @var TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;

	public function findOneByObjectAndPropertyName($object, $propertyName) {
		$uuid = $this->persistenceManager->getIdentifierByObject($object);
		return $this->findOneByUuidAndPropertyName($uuid, $propertyName);
	}

	public function findOneByUuidAndPropertyName($uuid, $propertyName) {
		$query = $this->createQuery();
		$query->matching($query->logicalAnd(
			$query->equals('objectUuid', $uuid),
			$query->equals('propertyName', $propertyName)
		));
		return $query->execute()->getFirst();
	}
}
?>