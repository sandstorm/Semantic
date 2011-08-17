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
	 * Return the NTriples notation for this Node
	 *
	 * @return string
	 */
	public function toNT() {
		return '_:' . $this->nominalValue;
	}

	/**
	 * Return a string repesentation of this RDF Node.
	 */
	public function __toString() {
		return $this->toNT();
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