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
 * A Blank node is an RDF node without a name.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class BlankNode extends RdfNode {

	/**
	 * @var string
	 */
	protected $nominalValue;

	/**
	 * @test
	 */
	public function __construct() {
		$this->nominalValue = uniqid('b');
	}

	/**
	 * Return the NQuads notation for this Node
	 *
	 * @return string
	 */
	public function toNQuads() {
		return '_:' . $this->nominalValue;
	}

	/**
	 * Return a string repesentation of this RDF Node.
	 */
	public function __toString() {
		return $this->toNQuads();
	}

	/**
	 * Comparator.
	 *
	 * @param RdfNode $otherNode the oher node to test Equality with.
	 * @return boolean TRUE if $otherNode equals $this, FALSE otherwise.
	 */
	public function equals(RdfNode $otherNode) {
		if (!$otherNode instanceof BlankNode) {
			return FALSE;
		}
		return $otherNode->valueOf() === $this->nominalValue;
	}

	/**
	 * Return the internal value of this Node.
	 *
	 * @return mixed
	 */
	public function valueOf() {
		return $this->nominalValue;
	}
}
?>