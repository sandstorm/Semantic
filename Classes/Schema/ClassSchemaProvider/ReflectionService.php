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

	public function getPropertySchema($className, $propertyName) {
		return array_map(function($value) {
			if (is_array($value) && count($value) === 1) {
				return $value[0];
			} else {
				return $value;
			}
		}, $this->reflectionService->getPropertyTagsValues($className, $propertyName));
	}

	public function getClassSchema($className) {
		return array();
	}
	
	public function getClassNamesWithSchema() {
		return $this->reflectionService->getClassNamesByTag('rdfType');
	}
}
?>