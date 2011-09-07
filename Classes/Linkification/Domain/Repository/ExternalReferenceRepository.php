<?php
declare(ENCODING = 'utf-8');
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

/**
 * @scope singleton
 */
class ExternalReferenceRepository extends \TYPO3\FLOW3\Persistence\Repository {

	/**
	 * @var TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @inject
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