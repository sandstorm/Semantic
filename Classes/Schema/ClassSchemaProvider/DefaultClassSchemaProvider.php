<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Schema\ClassSchemaProvider;

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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class DefaultClassSchemaProvider implements \SandstormMedia\Semantic\Schema\ClassSchemaProviderInterface {

	public function getPropertyNames($className, array $existingPropertyNames) {
		return $existingPropertyNames;
	}

	public function getPropertySchema($className, $propertyName, array $existingPropertySchema) {
		$propertySchema = $existingPropertySchema;

		if (!isset($propertySchema['rdfTripleGenerator'])) {

			if (isset($propertySchema['rdfLinkify'])) {
				$tripleGenerator = 'SandstormMedia\Semantic\Rdf\LinkificationTripleGenerator';
			} elseif (isset($propertySchema['rdfEnrichText'])) {
				$tripleGenerator = 'SandstormMedia\Semantic\Rdf\ContinuousTextTripleGenerator';
			} elseif (!isset($propertySchema['rdfSourceType'])) {
				$tripleGenerator = 'SandstormMedia\Semantic\Rdf\NullTripleGenerator';
			} elseif (in_array($propertySchema['rdfSourceType'], array('string', 'integer', 'float', 'boolean', 'DateTime'))) {
				$tripleGenerator = 'SandstormMedia\Semantic\Rdf\BasicTripleGenerator';
			} elseif ($propertySchema['rdfSourceType'] === 'Doctrine\Common\Collections\ArrayCollection' || $propertySchema['rdfSourceType'] === 'Doctrine\Common\Collections\Collection') {
				$tripleGenerator = 'SandstormMedia\Semantic\Rdf\CollectionTripleGenerator';
			} else {
				$tripleGenerator = 'SandstormMedia\Semantic\Rdf\RelationTripleGenerator';
			}

			$propertySchema['rdfTripleGenerator'] = $tripleGenerator;
		}

		return $propertySchema;
	}

	public function getClassSchema($className, array $existingClassSchema) {
		if (!isset($existingClassSchema['rdfIdentityProvider'])) {
			$existingClassSchema['rdfIdentityProvider'] = 'SandstormMedia\Semantic\Domain\Service\ResourceUriService';
		}
		return $existingClassSchema;
	}

	public function getClassNamesWithSchema(array $existingClassNamesWithSchema) {
		return $existingClassNamesWithSchema;
	}
}
?>