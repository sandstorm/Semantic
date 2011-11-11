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

namespace SandstormMedia\Semantic\Linkification;



use TYPO3\FLOW3\Annotations as FLOW3;
use SandstormMedia\Semantic\Linkification\Domain\Model\ExternalReference;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class PersistentObjectConverter extends \TYPO3\FLOW3\Property\TypeConverter\PersistentObjectConverter {
	protected $priority = 10000;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\ExternalReferenceRepository
	 * @FLOW3\Inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\TextAnnotationsRepository
	 * @FLOW3\Inject
	 */
	protected $textAnnotationsRepository;

	/**
	 * @var TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;

	/**
	 * All properties in the source array except __identity are sub-properties.
	 *
	 * @param mixed $source
	 * @return array
	 * @author Sebastian Kurf‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚àö√´‚Äö√†√∂‚Äö√†√á‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö√ë‚Äö√Ñ‚Ä†‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö‚Ä†‚àö√°¬¨¬®¬¨¬Æ¬¨¬®¬¨√Ü‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚Äö√Ñ‚Ä†¬¨¬®¬¨‚Ä¢rst <sebastian@typo3.org>
	 */
	public function getSourceChildPropertiesToBeConverted($source) {
		$result = parent::getSourceChildPropertiesToBeConverted($source);
		foreach ($result as $key => $value) {
			if (preg_match('/_metadata$/', $key) || preg_match('/_continuousTextMetadata$/', $key)) {
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
	 * @param \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration
	 * @return object the target type
	 * @author Sebastian Kurf‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚àö√´‚Äö√†√∂‚Äö√†√á‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö√ë‚Äö√Ñ‚Ä†‚Äö√Ñ√∂‚àö‚Ä†‚àö‚àÇ‚Äö√Ñ√∂‚àö‚Ä†‚àö√°¬¨¬®¬¨¬Æ¬¨¬®¬¨√Ü‚Äö√Ñ√∂‚àö√ë‚àö‚àÇ‚Äö√†√∂‚Äö√Ñ‚Ä†¬¨¬®¬¨‚Ä¢rst <sebastian@typo3.org>
	 */
	public function convertFrom($source, $targetType, array $subProperties = array(), \TYPO3\FLOW3\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		$object = parent::convertFrom($source, $targetType, $subProperties, $configuration);

		if (is_array($source)) {
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

						$externalReference->setValue($value);
						$this->externalReferenceRepository->add($externalReference);
					} else {
						// Update
						$externalReference->setValue($value);
						$this->externalReferenceRepository->update($externalReference);
					}

				} elseif (preg_match('/^(.*)_continuousTextMetadata$/', $key, $matches)) {
					$uuid = $this->persistenceManager->getIdentifierByObject($object);
					$propertyName = $matches[1];

					$value = json_decode($value, TRUE);

					$textAnnotation = $this->textAnnotationsRepository->findOneByUuidAndPropertyName($uuid, $propertyName);

					if (count($value) == 0 && $textAnnotation !== NULL) {
						$this->textAnnotationsRepository->remove($textAnnotation);
					} elseif ($textAnnotation === NULL) {
						$textAnnotation = new \SandstormMedia\Semantic\Linkification\Domain\Model\TextAnnotations();
						$textAnnotation->setObjectUuid($uuid);
						$textAnnotation->setPropertyName($propertyName);

						$textAnnotation->setAnnotations($value);
						$this->textAnnotationsRepository->add($textAnnotation);
					} else {
						// Update
						$textAnnotation->setAnnotations($value);
						$this->textAnnotationsRepository->update($textAnnotation);
					}

				}
			}
		}

		return $object;
	}
}
?>