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
 * A graph contains many triples.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Graph implements \IteratorAggregate {

	/**
	 * A list of triple actions executed when a new triple is added
	 * to the graph.
	 *
	 * @var array<\Closure>
	 */
	protected $actions = array();

	/**
	 *
	 * @var array<Triple>
	 */
	protected $triples = array();


	public function add(Triple $triple) {
		foreach ($this->actions as $tripleAction) {
			$tripleAction($triple, $this);
		}

		$this->triples[spl_object_hash($triple)] = $triple;

		return $this;
	}

	public function addAction(\Closure $tripleAction, $run = FALSE) {
		if ($run === TRUE) {
			foreach ($this->triples as $triple) {
				$tripleAction($triple, $this);
			}
		}
		$this->actions[] = $tripleAction;

		return $this;
	}

	public function addAll(Graph $graph) {
		foreach ($graph as $triple) {
			$this->add($triple);
		}
		return $this;
	}

	public function every(\Closure $tripleFilter) {
		foreach ($this->triples as $triple) {
			if ($tripleFilter($triple) === FALSE) {
				return FALSE;
			}
		}

		return TRUE;
	}

	public function some(\Closure $tripleFilter) {
		foreach ($this->triples as $triple) {
			if ($tripleFilter($triple)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	public function filter(\Closure $tripleFilter) {
		$newGraph = new Graph();

		foreach ($this->triples as $triple) {
			if ($tripleFilter($triple)) {
				$newGraph->add($triple);
			}
		}

		return $newGraph;
	}

	public function toArray() {
		return array_values($this->triples);
	}

	public function remove(Triple $tripleToRemove) {
		unset($this->triples[spl_object_hash($tripleToRemove)]);

		return $this;
	}

	public function getLength() {
		return count($this->triples);
	}

	public function getIterator() {
        return new \ArrayIterator(array_values($this->triples));
    }

	// New API!!
	public function toNt() {
		$outputAsNtriples = '';
		foreach ($this as $triple) {
			$outputAsNtriples .= (string)$triple;
		}
		return $outputAsNtriples;
	}
}
?>