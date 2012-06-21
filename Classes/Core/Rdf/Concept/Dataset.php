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
 * A SPARQL Dataset related to the definition mentioned in the paper of the N-Quads syntax.
 * It is itself a graph (the default graph) and includes a set of named graphs.
 *
 * http://sw.deri.org/2008/07/n-quads/
 * http://www.w3.org/TR/rdf-sparql-query/#rdfDataset
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Dataset extends Graph implements \IteratorAggregate {

	/**
	 *
	 * @var array<Graph>
	 */
	protected $graphs = array();

	/**
	 * Adds a named graph to the collection.
	 *
	 * @param NamedGraph $graph
	 * @return Dataset
	 */
	public function addGraph(Graph $graph) {
		if (isset($this->graphs[$graph->getName()->valueOf()])) {
			throw new \SandstormMedia\Semantic\Exception('Each named graph must be unique inside a NamedGraphCollection. NamedGraphUri: ' . $graph->getName()->valueOf(), 1339428519);
		}
		$this->graphs[$graph->getName()->valueOf()] = $graph;
		return $this;
	}

	/**
	 * Creates a named graph with the given name.
	 *
	 * @param RdfNode $name
	 * @return Dataset
	 */
	public function createGraph(RdfNode $name) {
		return $this->addGraph(new Graph($name));
		return this;
	}

	/**
	 * Returns if the dataset contains a graph with the given name.
	 *
	 * @param RdfNode $name
	 * @return boolean
	 */
	public function hasGraph(RdfNode $name) {
		return isset($this->graphs[$name->valueOf()]);
	}

	/**
	 * Returns a named graph of the dataset.
	 *
	 * @param RdfNode $name
	 * @return mixed NamedGraph/NULL
	 */
	public function getGraph(RdfNode $name) {
		return $this->graphs[$name->valueOf()];
	}

	/**
	 * Removes a graph from the dataset.
	 *
	 * @param NamedGraph $graphToRemove
	 * @return Dataset
	 */
	public function removeGraph(Graph $graphToRemove) {
		unset($this->graphs[$graphToRemove->getName()->valueOf()]);
		return $this;
	}

	/**
	 * Removes the graph from the dataset that belongs to the given name.
	 *
	 * @param RdfNode $name
	 * @return Dataset
	 */
	public function removeGraphByName(RdfNode $name) {
		unset($this->graphs[$name->valueOf()]);
		return $this;
	}

	/**
	 * Adds a triple to the dataset. If no name for a graph is given,
	 * it is added to the default graph. If a name is given and
	 * a corresponding graph exists, it is added there.
	 *
	 * @see SandstormMedia\Semantic\Core\Rdf\Concept.Graph::add()
	 * @return Dataset
	 */
	public function add(Triple $triple, RdfNode $name = NULL) {
		if ($name === NULL) return parent::add($triple);
		if (!$this->hasGraph($name)) throw new \SandstormMedia\Semantic\Exception('Cannot a triple to named graph: ' . $name->valueOf() . ' as it is not known by the dataset', 1339428520);
		$this->getGraph($name)->add($triple);
		return $this;
	}

    /**
     * Returns the whole Dataset in NQuads representation.
     *
     * @see SandstormMedia\Semantic\Core\Rdf\Concept.Graph::toNQuads()
     * @return string
     */
	public function toNQuads() {
		$outputAsNQuads = parent::toNQuads();
		foreach ($this->graphs as $graph) {
			$outputAsNQuads .= $graph->toNQuads();
		}
		return $outputAsNQuads;
	}
}
?>