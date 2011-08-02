<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Domain\Model\Rdf\Environment;

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
class Profile implements ProfileInterface {

	/**
	 * @var SandstormMedia\Semantic\Domain\Model\Rdf\Environment\PrefixMap
	 * @inject
	 */
	protected $prefixes;

	/**
	 * @var SandstormMedia\Semantic\Domain\Model\Rdf\Environment\TermMap
	 * @inject
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