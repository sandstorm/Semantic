<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Core\Schema;

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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class ClassSchemaResolver {
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @FLOW3\Inject
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var array<SandstormMedia\Semantic\Core\Schema\ClassSchemaProvider\ReflectionService>
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
		foreach ($this->settings['classSchemaProviders'] as $classSchemaProviderClassName) {
			$this->classSchemaProviders[] = $this->objectManager->get($classSchemaProviderClassName);
		}
	}

	public function getPropertyNames($className) {
		$propertyNames = array();
		foreach ($this->classSchemaProviders as $provider) {
			$propertyNames = $provider->getPropertyNames($className, $propertyNames);
		}
		return $propertyNames;
	}

	public function getClassSchema($classNameOrObject) {
		if (is_object($classNameOrObject) && $classNameOrObject instanceof \Doctrine\ORM\Proxy\Proxy) {
			$classNameOrObject = get_parent_class($classNameOrObject);
		}

		$className = is_object($classNameOrObject) ? get_class($classNameOrObject) : $classNameOrObject;

		$classSchema = array();
		foreach ($this->classSchemaProviders as $provider) {
			$classSchema = $provider->getClassSchema($className, $classSchema);
		}
		return $classSchema;
	}

	public function getClassNamesWhichHaveASchema() {
		$classNamesWithSchema = array();
		foreach ($this->classSchemaProviders as $provider) {
			$classNamesWithSchema = $provider->getClassNamesWithSchema($classNamesWithSchema);
		}
		return $classNamesWithSchema;
	}

	public function getPropertySchema($className, $propertyName) {
		$reflectionClass = new \ReflectionClass($className);
		if ($reflectionClass->implementsInterface('Doctrine\ORM\Proxy\Proxy')) {
			$className = get_parent_class($className);
		}

		$result = array();
		foreach ($this->classSchemaProviders as $provider) {
			$result = $provider->getPropertySchema($className, $propertyName, $result);
		}
		return $result;
	}
}
?>