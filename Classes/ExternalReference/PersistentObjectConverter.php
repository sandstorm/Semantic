<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\ExternalReference;

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

use F3\Semantic\Domain\Model\ExternalReference;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class PersistentObjectConverter extends \F3\FLOW3\Property\TypeConverter\PersistentObjectConverter {
	protected $priority = 10;

	/**
	 * @var F3\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var F3\FLOW3\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * All properties in the source array except __identity are sub-properties.
	 *
	 * @param mixed $source
	 * @return array
	 * @author Sebastian Kurf‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚àö√´‚Äö√†√∂‚Äö√†√á‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö√ë‚Äö√Ñ‚Ä†‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö‚Ä†‚àö√°¬¨¬®¬¨¬Æ¬¨¬®¬¨√Ü‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚Äö√Ñ‚Ä†¬¨¬®¬¨‚Ä¢rst <sebastian@typo3.org>
	 */
	public function getProperties($source) {
		$result = parent::getProperties($source);
		foreach ($result as $key => $value) {
			if (preg_match('/_metadata$/', $key)) {
				unset($result[$key]);
			}
		}
		return $result;
	}


	/**
	 * Convert an object from $source to an entity or a value object.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $subProperties
	 * @param \F3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration
	 * @return object the target type
	 * @author Sebastian Kurf‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚àö√´‚Äö√†√∂‚Äö√†√á‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö√ë‚Äö√Ñ‚Ä†‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö‚Ä†‚àö√°¬¨¬®¬¨¬Æ¬¨¬®¬¨√Ü‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚Äö√Ñ‚Ä†¬¨¬®¬¨‚Ä¢rst <sebastian@typo3.org>
	 */
	public function convertFrom($source, $targetType, array $subProperties = array(), \F3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		$object = parent::convertFrom($source, $targetType, $subProperties, $configuration);

		foreach ($source as $key => $value) {
			$matches = array();
			if (preg_match('/^(.*)_metadata$/', $key, $matches)) {
				$uuid = $this->persistenceManager->getIdentifierByObject($object);
				$propertyName = $matches[1];
				$externalReference = $this->externalReferenceRepository->findOneByUuidAndPropertyName($uuid, $propertyName);

				if ($value == '' && $externalReference !== NULL) {
					$this->externalReferenceRepository->remove($externalReference);
				} elseif ($externalReference === NULL) {
					$externalReference = new ExternalReference();
					$externalReference->setObjectUuid($uuid);
					$externalReference->setPropertyName($propertyName);

					$this->externalReferenceRepository->add($externalReference);
				}
				$externalReference->setValue($value);
			}
		}

		return $object;
	}
}
?>