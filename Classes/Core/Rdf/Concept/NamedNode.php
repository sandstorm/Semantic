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



use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A NamedNodes is the RDF node for URIs.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class NamedNode extends RdfNode {

	/**
	 * @var SandstormMedia\Semantic\Core\Rdf\Environment\ProfileInterface
	 * @FLOW3\Inject
	 */
	protected $profile;

	/**
	 * The IRI inside this NamedNode.
	 *
	 * @var string
	 */
	protected $nominalValue;

	/**
	 * @param string $iri
	 */
	public function __construct($iri) {
		$this->nominalValue = (string)$iri;
	}

	public function initializeObject() {
		$result = $this->profile->resolve($this->nominalValue);
		if ($result !== NULL) {
			$this->nominalValue = $result;
		}
	}

	/**
	 * @return string the IRI of this node in NTriples notation
	 */
	public function toNQuads() {
		return '<' . $this->nominalValue . '>';
	}

	/**
	 * Comparator.
	 *
	 * @param RdfNode $otherNode the oher node to test Equality with.
	 * @return boolean TRUE if $otherNode equals $this, FALSE otherwise.
	 */
	public function equals(RdfNode $other) {
		if (!$other instanceof NamedNode) {
			return FALSE;
		}
		return $other->valueOf() === $this->nominalValue;
	}

	/**
	 * @return string the IRI for this NamedNode
	 */
	public function valueOf() {
		return $this->nominalValue;
	}

	/**
	 * @return string the IRI of this node
	 */
	public function __toString() {
		return $this->nominalValue;
	}
}
?>