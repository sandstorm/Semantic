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
 * Base class for RDF Nodes.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class RdfNode {

	/**
	 * Return the N-Quads notation for this Node.
	 *
	 *
	 * @return string
	 */
	abstract public function toNQuads();

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