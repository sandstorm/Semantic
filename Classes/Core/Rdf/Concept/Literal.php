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

namespace SandstormMedia\Semantic\Core\Rdf\Concept;



/**
 * A literal node is a simple value like a string, a date, ...
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Literal extends RdfNode {

	/**
	 * The value of the literal, is always a simple type.
	 * @var mixed
	 */
	protected $nominalValue;

	/**
	 * @var NamedNode
	 */
	protected $dataType = NULL;

	/**
	 *
	 * @var string
	 */
	protected $language;

	/**
	 *
	 * @param mixed $nominalValue
	 * @param string $language
	 * @param NamedNode $dataType
	 */
	public function __construct($nominalValue, $language = NULL, NamedNode $dataType = NULL) {
		if ($nominalValue instanceof \DateTime) {
			$this->nominalValue = $nominalValue->format(\DateTime::W3C);
			$this->dataType = new NamedNode('http://www.w3.org/2001/XMLSchema#dateTime');
		} else {
			$this->nominalValue = $nominalValue;
			$this->dataType = $dataType;
		}

		if ($language !== NULL) {
			$this->language = strtolower($language);
		}
	}

	public function getDataType() {
		return $this->dataType;
	}

	public function getLanguage() {
		return $this->language;
	}

	public function toNQuads() {
		$nominalValue = str_replace(array("\r\n", "\n"), '\n', $this->nominalValue);
		$output = '"' . $nominalValue . '"';

		if ($this->dataType !== NULL) {
			$output .= '^^' . $this->dataType->toNQuads();
		}
		return $output;
	}

	public function __toString() {
		return (string)$this->nominalValue;
	}

	public function equals(RdfNode $other) {
		if (!$other instanceof Literal) {
			return FALSE;
		}

		if ($other->getDataType() !== NULL && $this->dataType !== NULL) {
			if (!$this->dataType->equals($other->getDataType())) {
				return FALSE;
			}
		} elseif ($other->getDataType() === NULL && $this->dataType === NULL ) {
			// OK; data types are equal
		} else {
			// One data type is NULL, the other one is non-null; so the Literals are not equal
			return FALSE;
		}

		return ($other->valueOf() === $this->nominalValue
			&& $other->getLanguage() === $this->language);
	}

	public function valueOf() {
		return $this->nominalValue;
	}
}
?>