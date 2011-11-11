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

namespace SandstormMedia\Semantic\Core\Rdf\Environment;



use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Profile implements ProfileInterface {

	/**
	 * @var SandstormMedia\Semantic\Core\Rdf\Environment\PrefixMap
	 * @FLOW3\Inject
	 */
	protected $prefixes;

	/**
	 * @var SandstormMedia\Semantic\Core\Rdf\Environment\TermMap
	 * @FLOW3\Inject
	 */
	protected $terms;

	public function getPrefixes() {
		return $this->prefixes;
	}

	public function getTerms() {
		return $this->terms;
	}

	public function resolve($curieOrTerm) {
		if (strpos($curieOrTerm, ':') === FALSE) {
			return $this->terms->resolve($curieOrTerm);
		} else {
			return $this->prefixes->resolve($curieOrTerm);
		}
	}

	public function setPrefix($prefix, $iri) {
		$this->prefixes->set($prefix, $iri);
	}

	public function setTerm($term, $iri) {
		$this->terms->set($term, $iri);
	}
}
?>