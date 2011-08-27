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
class ReflectionService implements \SandstormMedia\Semantic\Schema\ClassSchemaProviderInterface {

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @inject
	 */
	protected $reflectionService;

	public function getPropertyNames($className) {
		return $this->reflectionService->getClassPropertyNames($className);
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

	public function getPropertySchema($className, $propertyName) {
		$values = $this->cleanupValues($this->reflectionService->getPropertyTagsValues($className, $propertyName));

		$propertySchema = $this->reflectionService->getClassSchema($className)->getProperty($propertyName);
		if ($propertySchema) {
			$values['rdfSourceType'] = $propertySchema['type'];
			$values['rdfSourceElementType'] = $propertySchema['elementType'];
		}
		return $this->filterValues($values);
	}

	public function getClassSchema($className) {
		$values = $this->cleanupValues($this->reflectionService->getClassTagsValues($className));
		return $this->filterValues($values);
	}

	public function getClassNamesWithSchema() {
		return $this->reflectionService->getClassNamesByTag('rdfType');
	}
}
?>