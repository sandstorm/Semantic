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
class YamlProvider implements \SandstormMedia\Semantic\Core\Schema\ClassSchemaProviderInterface {

	/**
	 * @var array
	 */
	protected $settings;

	public function injectSettings($settings) {
		$this->settings = $settings;
	}

	public function getPropertyNames($className, array $existingPropertyNames) {
		$result = $existingPropertyNames;
		if (isset($this->settings['PropertyMapping'][$className]['properties'])) {
			$result = array_merge($result, array_keys($this->settings['PropertyMapping'][$className]['properties']));
		}
		return $result;
	}

	public function getPropertySchema($className, $propertyName, array $existingPropertySchema) {
		$result = $existingPropertySchema;
		if (isset($this->settings['PropertyMapping'][$className]['properties'][$propertyName])) {
			foreach ($this->settings['PropertyMapping'][$className]['properties'][$propertyName] as $k => $v) {
				$result['rdf' . ucfirst($k)] = $v;
			}
		}

		return $result;
	}

	public function getClassSchema($className, array $existingClassSchema) {
		$result = $existingClassSchema;
		if (isset($this->settings['PropertyMapping'][$className])) {
			foreach ($this->settings['PropertyMapping'][$className] as $k => $v) {
				if ($k === 'properties') continue;

				$result['rdf' . ucfirst($k)] = $v;
			}
		}

		return $result;
	}

	public function getClassNamesWithSchema(array $existingClassNamesWithSchema) {
		if (isset($this->settings['PropertyMapping']) && is_array($this->settings['PropertyMapping'])) {
			return array_merge($existingClassNamesWithSchema, array_keys($this->settings['PropertyMapping']));
		} else {
			return $existingClassNamesWithSchema;
		}
	}
}
?>