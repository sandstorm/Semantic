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

	/**
	 * The name of the graph. Modern triple stores support named
	 * graphs. Thats their representation.
	 *
	 * @var RdfNode
	 */
	protected $name;

	/**
	 * Default constructor.
	 *
	 * @param RdfNode $name
	 */
	public function __construct(RdfNode $name = NULL) {
		$this->name = $name;
	}

	/**
	 * Returns the name of the graph.
	 *
	 * @return RdfNode
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name of the graph.
	 *
	 * @param RdfNode $name
	 * @return Graph
	 */
	public function setName(RdfNode $name = NULL) {
		$this->name = $name;
		foreach ($this->triples as $triple) {
			$triple->setContext($this->name);
		}
		return $this;
	}

	/**
	 * Adds a triple to the graph. The graph adds it name to the
	 * triple to allow named graphs. If the name is null, the triples
	 * behave as if they belong to the default graph.
	 *
	 * @param Triple $triple
	 * @return \SandstormMedia\Semantic\Core\Rdf\Concept\Graph
	 */
	public function add(Triple $triple) {
		foreach ($this->actions as $tripleAction) {
			$tripleAction($triple, $this);
		}
		$triple->setContext($this->name);
		$this->triples[spl_object_hash($triple)] = $triple;

		return $this;
	}

	/**
	 * Adds an action that gets executed each time a triple
	 * is added to this graph.
	 *
	 * @param \Closure $tripleAction The action that should get executed.
	 * @param boolean $run Wether the added action should get executed, before it is added.
	 */
	public function addAction(\Closure $tripleAction, $run = FALSE) {
		if ($run === TRUE) {
			foreach ($this->triples as $triple) {
				$tripleAction($triple, $this);
			}
		}
		$this->actions[] = $tripleAction;

		return $this;
	}

	/**
	 * Adds all triples of the given graph to this
	 * one.
	 *
	 * @param Graph $graph
	 */
	public function addAll(Graph $graph) {
		foreach ($graph as $triple) {
			$this->add($triple);
		}
		return $this;
	}

	/**
	 * Iterates over all triples and calls the given
	 * closure. If one triple does not match the filter, false
	 * is returned otherwise true.
	 *
	 * @param \Closure $tripleFilter
	 */
	public function every(\Closure $tripleFilter) {
		foreach ($this->triples as $triple) {
			if ($tripleFilter($triple) === FALSE) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Iterates over all triples and calls the given
	 * closure. If any triple matches the filter, true is
	 * returned, otherwise false.
	 *
	 * @param \Closure $tripleFilter
	 * @return boolean
	 */
	public function some(\Closure $tripleFilter) {
		foreach ($this->triples as $triple) {
			if ($tripleFilter($triple)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Applies a filter given as closure to the
	 * graph. Based on the filter triples that match
	 * were added to a new graph that will be returned.
	 *
	 * @param \Closure $tripleFilter
	 * @return Graph
	 */
	public function filter(\Closure $tripleFilter) {
		$newGraph = new Graph();

		foreach ($this->triples as $triple) {
			if ($tripleFilter($triple)) {
				$newGraph->add($triple);
			}
		}

		return $newGraph;
	}

	/**
	 * Returns all triples that belong to the
	 * graph contained in an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return array_values($this->triples);
	}

	/**
	 * Removes a triple from the graph.
	 *
	 * @param Triple $tripleToRemove
	 * @return Graph
	 */
	public function remove(Triple $tripleToRemove) {
		unset($this->triples[spl_object_hash($tripleToRemove)]);

		return $this;
	}

	/**
	 * Returns the number of all assigned triples.
	 *
	 * @return int
	 */
	public function getLength() {
		return count($this->triples);
	}

	/**
	 * Returns a iterator for this graph.
	 *
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 * @return ArrayIterator
	 */
	public function getIterator() {
        return new \ArrayIterator(array_values($this->triples));
    }

	/**
	 * Returns the graph in NQuad or NTriples Syntax, related
	 * to the graphs context.
	 *
	 * @return string
	 */
	public function toNQuads() {
		if ($this->name === NULL) $outputAsNQuads = '# Default graph: ' . chr(10);
		else $outputAsNQuads = '# Graph: ' . $this->name->valueOf() . chr(10);
		foreach ($this as $triple) {
			$outputAsNQuads .= (string)$triple;
		}
		return $outputAsNQuads;
	}
}
?>