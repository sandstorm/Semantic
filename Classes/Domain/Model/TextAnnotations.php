<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Domain\Model;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @entity
 */
class TextAnnotations {

	/**
	 * @var string
	 */
	protected $objectUuid;

	/**
	 * @var string
	 */
	protected $propertyName;

	/**
	 * @var array
	 */
	protected $annotations;

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

	// TODO: fix method below
	public function getStringWithAnnotations() {
		$output = '';
		$length = strlen($this->string);

		for ($i = 0; $i < $length; $i++) {
			if (isset($this->endOfData[$i])) {
				foreach ($this->endOfData[$i] as $storage) {
					$output .= '</span>'; // HACK; which creates not correctly nested HTML as soon as ranges OVERLAP!!
				}
			}

			if (isset($this->beginOfData[$i])) {
				foreach ($this->beginOfData[$i] as $storage) {
					$output .= '<span
						about="' . $storage->subject . '"
						typeof="' . $storage->type . '">'; // TODO: HTML Special Chars // use TagBuilder!
				}
			}

			$output .= $this->string[$i];
		}

		return $output;
	}

	public function setAnnotations($annotations) {
		$this->annotations = $annotations;
	}
}
?>