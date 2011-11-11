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
class TermMap {
	protected $terms = array();

	public function get($term) {
		if (!isset($this->terms[$term])) {
			return NULL;
		}
		return $this->terms[$term];
	}
	public function set($term, $iri) {
		$this->terms[(string)$term] = (string)$iri;
	}

	public function remove($term) {
		unset($this->terms[$term]);
	}

	public function resolve($term) {
		if (!isset($this->terms[$term])) {
			return NULL;
		}
		return $this->terms[$term];
	}

	public function shrink($iri) {
		foreach ($this->terms as $term => $termIri) {
			if ($iri === $termIri) {
				return $term;
			}
		}

		return $iri;
	}
}
?>