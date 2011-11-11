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

namespace SandstormMedia\Semantic\Linkification\Domain\Model;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An "external reference" contains for a given object and property a URI which should be used instead.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Entity
 */
class ExternalReference {

	/**
	 * @var string
	 */
	protected $objectUuid;

	/**
	 * @var string
	 */
	protected $propertyName;

	/**
	 * @var string
	 */
	protected $value;

	public function getObjectUuid() {
		return $this->objectUuid;
	}
	public function setObjectUuid($objectUuid) {
		$this->objectUuid = $objectUuid;
	}

	public function getPropertyName() {
		return $this->propertyName;
	}
	public function setPropertyName($propertyName) {
		$this->propertyName = $propertyName;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}
}
?>