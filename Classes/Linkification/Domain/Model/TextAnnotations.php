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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Entity
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