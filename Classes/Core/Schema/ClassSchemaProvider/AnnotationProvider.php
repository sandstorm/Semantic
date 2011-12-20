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
class AnnotationProvider implements \SandstormMedia\Semantic\Core\Schema\ClassSchemaProviderInterface {

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @FLOW3\Inject
	 */
	protected $reflectionService;

	public function getPropertyNames($className, array $existingPropertyNames) {
		return array_merge($existingPropertyNames, $this->reflectionService->getClassPropertyNames($className));
	}

	protected function cleanupValues($values) {
		return array_map(function($value) {
			if (is_array($value) && count($value) === 1) {
				return $value[0];
			} elseif (is_array($value) && count($value) === 0) {
				return NULL;
			} else {
				return $value;
			}
		}, $values);
	}

	protected function filterValues($values) {
		$filteredValues = array();
		foreach ($values as $key => $value) {
			if (strpos($key, 'rdf') === 0) { // only keep the element if it *starts* with rdf
				$filteredValues[$key] = $value;
			}
		}
		return $filteredValues;
	}

	public function getPropertySchema($className, $propertyName, array $existingPropertySchema) {
		$values = array();

		$classSchema = $this->reflectionService->getClassSchema($className);
		if (!$classSchema) return $existingPropertySchema;
		if (!$classSchema->hasProperty($propertyName)) return $existingPropertySchema;
		$propertySchema = $classSchema->getProperty($propertyName);
		if ($propertySchema) {
			$values['rdfSourceType'] = $propertySchema['type'];
			$values['rdfSourceElementType'] = $propertySchema['elementType'];
		}
		return array_merge($existingPropertySchema, $this->filterValues($values));
	}

	public function getClassSchema($className, array $existingClassSchema) {
		return $existingClassSchema;
	}

	public function getClassNamesWithSchema(array $existingClassNamesWithSchema) {
		return $existingClassNamesWithSchema;
	}
}
?>