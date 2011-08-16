<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Domain\Model\Rdf\Concept;

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
 * A NamedNodes is the RDF node for URIs.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class NamedNode extends RdfNode {

	/**
	 * @var SandstormMedia\Semantic\Domain\Model\Rdf\Environment\ProfileInterface
	 * @inject
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
	public function toNT() {
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