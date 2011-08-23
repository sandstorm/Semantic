<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Schema;

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
class ClassSchemaResolver {
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array<SandstormMedia\Semantic\Schema\ClassSchemaProvider\ReflectionService>
	 */
	protected $classSchemaProviders = array();

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @return void
	 */
	public function initializeObject() {
		foreach ($this->settings['classSchemaResolvers'] as $resolverClassName) {
			$this->classSchemaProviders[] = $this->objectManager->get($resolverClassName);
		}
	}

	public function getPropertyNames($className) {
		$propertyNames = array();
		foreach ($this->classSchemaProviders as $provider) {
			$propertyNames = array_unique(array_merge($propertyNames, $provider->getPropertyNames($className)));
		}
		return $propertyNames;
	}

	public function getClassSchema($className) {
		$classSchema = array();
		foreach ($this->classSchemaProviders as $provider) {
			$classSchema = array_merge($classSchema, $provider->getClassSchema($className));
		}
		return $classSchema;
	}
	
	public function getClassNamesWhichHaveASchema() {
		$classNamesWithSchema = array();
		foreach ($this->classSchemaProviders as $provider) {
			$classNamesWithSchema = array_merge($classNamesWithSchema, $provider->getClassNamesWithSchema());
		}
		return $classNamesWithSchema;
	}

	public function getPropertySchema($className, $propertyName) {
		$result = array();
		foreach ($this->classSchemaProviders as $provider) {
			$result = array_merge($result, $provider->getPropertySchema($className, $propertyName));
		}
		return $result;
	}
}
?>