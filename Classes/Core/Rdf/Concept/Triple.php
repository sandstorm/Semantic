<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Core\Rdf\Concept;

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
 * Triple, containing Subject, Predicate and Object.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Triple {

	/**
	 * @var RdfNode
	 */
	protected $subject;

	/**
	 * @var RdfNode
	 */
	protected $predicate;

	/**
	 * @var RdfNode
	 */
	protected $object;

	/**
	 * @param RdfNode $subject
	 * @param RdfNode $predicate
	 * @param RdfNode $object
	 */
	public function __construct(RdfNode $subject, RdfNode $predicate, RdfNode $object) {
		$this->subject = $subject;
		$this->predicate = $predicate;
		$this->object = $object;
	}
	/**
	 * @return RdfNode
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @return RdfNode
	 */
	public function getPredicate() {
		return $this->predicate;
	}

	/**
	 * @return RdfNode
	 */
	public function getObject() {
		return $this->object;
	}

	/**
	 * @param Triple $otherTriple
	 * @return boolean TRUE if $this equals $otherTriple, FALSE otherwise.
	 */
	public function equals(Triple $otherTriple) {
		return ($otherTriple->getSubject()->equals($this->subject)
			&& $otherTriple->getPredicate()->equals($this->predicate)
			&& $otherTriple->getObject()->equals($this->object));
	}

	/**
	 *Return the NTriples notation for this triple.
	 *
	 * @return string
	 */
	public function __toString() {
		$output = '';
		$output .= $this->subject->toNT();
		$output .= ' ';
		$output .= $this->predicate->toNT();
		$output .= ' ';
		$output .= $this->object->toNT();
		$output .= '.';
		$output .= chr(10);

		return $output;
	}

}
?>