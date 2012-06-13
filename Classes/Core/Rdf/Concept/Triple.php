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
	 * @var RdfNode
	 */
	protected $context;

	/**
	 * @param RdfNode $subject
	 * @param RdfNode $predicate
	 * @param RdfNode $object
	 */
	public function __construct(RdfNode $subject, RdfNode $predicate, RdfNode $object, RdfNode $context = NULL) {
		$this->subject = $subject;
		$this->predicate = $predicate;
		$this->object = $object;
		$this->context = $context;
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
	 * @return RdfNode
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * Sets the context.
	 *
	 * @return void
	 */
	public function setContext(RdfNode $context = NULL) {
		$this->context = $context;
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
	 * Return the NQuads notation for this triple.
	 *
	 * @return string
	 */
	public function __toString() {
		$output = '';
		$output .= $this->subject->toNQuads();
		$output .= ' ';
		$output .= $this->predicate->toNQuads();
		$output .= ' ';
		$output .= $this->object->toNQuads();
		if ($this->context !== NULL) {
			$output .= ' ';
			$output .= $this->context->toNQuads();
		}
		$output .= ' .';
		$output .= chr(10);

		return $output;
	}

}
?>