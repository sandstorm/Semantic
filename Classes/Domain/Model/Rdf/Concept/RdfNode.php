<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Model\Rdf\Concept;

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
 */
abstract class RdfNode {

	/**
	 * Return the NTriples notation for this Node
	 *
	 * @return string
	 */
	abstract public function toNT();

	/**
	 * Return a string repesentation of this RDF Node.
	 */
	abstract public function __toString();

	/**
	 * Comparator.
	 *
	 * @param RdfNode $otherNode the oher node to test Equality with.
	 * @return boolean TRUE if $otherNode equals $this, FALSE otherwise.
	 */
	abstract public function equals(RdfNode $otherNode);

	/**
	 * Return the internal value of this Node.
	 *
	 * @return mixed
	 */
	abstract public function valueOf();
}
?>