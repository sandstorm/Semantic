<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Model\Rdf\Environment;

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
class PrefixMap {
	protected $prefixes = array();

	public function get($prefix) {
		if (!isset($this->prefixes[$prefix])) {
			return NULL;
		}
		return $this->prefixes[$prefix];
	}
	public function set($prefix, $iri) {
		$this->prefixes[(string)$prefix] = (string)$iri;
	}

	public function remove($prefix) {
		unset($this->prefixes[$prefix]);
	}

	public function resolve($curie) {
		if (strpos($curie, ':') === FALSE) {
			return NULL;
		}
		list($prefix, $suffix) = explode(':', $curie, 2);
		if (!isset($this->prefixes[$prefix])) {
			return NULL;
		}
		return $this->prefixes[$prefix] . $suffix;
	}

	public function shrink($iri) {
		foreach ($this->prefixes as $prefix => $prefixIri) {
			if (substr_compare($iri, $prefixIri, 0, strlen($prefixIri)) === 0) {
				return $prefix . ':' . substr($iri, strlen($prefixIri));
			}
		}

		return $iri;
	}
}
?>