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
	protected $annotations = array();

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
	public function getStringWithAnnotations($text) {
		$output = '';
		$length = strlen($text);

		$beginAtPosition = array();
		$endAtPosition = array();
		if ($this->annotations === NULL || count($this->annotations) === 0) return $text;
		
		foreach ($this->annotations as $annotation) {
			$beginAtPosition[$annotation['offset']] = $annotation;
			$endAtPosition[$annotation['offset'] + $annotation['length']] = $annotation;
		}
		// TODO: check that ranges do not overlap!
		// TODO: below code could still be broken...
		for ($i = 0; $i < $length; $i++) {
			if (isset($endAtPosition[$i])) {
				$output .= $text[$i];
				$output .= '</span>';
			} elseif (isset($beginAtPosition[$i])) {
				$annotation = $beginAtPosition[$i];
				$output .= '<span about="' . $annotation['uri'] . '">';
				$output .= $text[$i];
			} else {
				$output .= $text[$i];
			}
		}

		return $output;
	}

	public function setAnnotations($annotations) {
		$this->annotations = $annotations;
	}

	public function getAnnotations() {
		return $this->annotations;
	}
}
?>