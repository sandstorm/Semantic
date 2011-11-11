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

namespace SandstormMedia\Semantic\Core\Schema\ClassSchemaProvider;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class DefaultProvider implements \SandstormMedia\Semantic\Core\Schema\ClassSchemaProviderInterface {

	public function getPropertyNames($className, array $existingPropertyNames) {
		return $existingPropertyNames;
	}

	public function getPropertySchema($className, $propertyName, array $existingPropertySchema) {
		$propertySchema = $existingPropertySchema;

		if (!isset($propertySchema['rdfTripleGenerator'])) {

			if (isset($propertySchema['rdfLinkify'])) {
				$tripleGenerator = 'SandstormMedia\Semantic\Linkification\TripleGenerator\LinkificationTripleGenerator';
			} elseif (isset($propertySchema['rdfEnrichText'])) {
				$tripleGenerator = 'SandstormMedia\Semantic\Linkification\TripleGenerator\ContinuousTextTripleGenerator';
			} elseif (!isset($propertySchema['rdfSourceType'])) {
				$tripleGenerator = 'SandstormMedia\Semantic\Core\RdfGeneration\NullTripleGenerator';
			} elseif (in_array($propertySchema['rdfSourceType'], array('string', 'integer', 'float', 'boolean', 'DateTime'))) {
				$tripleGenerator = 'SandstormMedia\Semantic\Core\RdfGeneration\BasicTripleGenerator';
			} elseif ($propertySchema['rdfSourceType'] === 'Doctrine\Common\Collections\ArrayCollection' || $propertySchema['rdfSourceType'] === 'Doctrine\Common\Collections\Collection') {
				$tripleGenerator = 'SandstormMedia\Semantic\Core\RdfGeneration\CollectionTripleGenerator';
			} else {
				$tripleGenerator = 'SandstormMedia\Semantic\Core\RdfGeneration\RelationTripleGenerator';
			}

			$propertySchema['rdfTripleGenerator'] = $tripleGenerator;
		}

		return $propertySchema;
	}

	public function getClassSchema($className, array $existingClassSchema) {
		if (!isset($existingClassSchema['rdfIdentityProvider'])) {
			$existingClassSchema['rdfIdentityProvider'] = 'SandstormMedia\Semantic\Core\RdfGeneration\IdentityProvider\ResourceUriService';
		}
		return $existingClassSchema;
	}

	public function getClassNamesWithSchema(array $existingClassNamesWithSchema) {
		return $existingClassNamesWithSchema;
	}
}
?>