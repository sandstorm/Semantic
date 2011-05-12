<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Model\Rdf\Concept;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
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
}
?>