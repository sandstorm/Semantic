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