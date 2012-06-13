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

namespace SandstormMedia\Semantic\Tests\Unit\Domain\Model\Rdf\Concept;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use \SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
use \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers SandstormMedia\Semantic\Core\Rdf\Concept\Graph
 */
class GraphTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function addTripleAddsTripleToGraph() {
		$mockTriple = $this->getMockTriple();

		$graph = new Graph();
		$returnValue = $graph->add($mockTriple);

		foreach ($graph as $triple) {
			$this->assertSame($mockTriple, $triple, 'Triple is not the same...');
		}
		$this->assertSame($graph, $returnValue, 'Wrong return value');
	}

	/**
	 * @test
	 */
	public function addTripleToNamedGraphSetsContextOnTheTriple() {
		$mockTriple = $this->getMockTriple();
		$name = new NamedNode('http://id.foo.org/barGraph');
		$mockTriple->expects($this->once())->method('setContext')->with($this->equalTo($name));
		$graph = new Graph($name);
		$graph->add($mockTriple);
	}

	/**
	 * @test
	 */
	public function addTripleToUnnamedGraphSetsContextOnTheTripleToNull() {
		$mockTriple = $this->getMockTriple();
		$mockTriple->expects($this->once())->method('setContext')->with($this->equalTo(NULL));
		$graph = new Graph();
		$graph->add($mockTriple);
	}

	/**
	 * @test
	 */
	public function setNameSetsTheContextOnAlreadyAssignedTriples() {
		$mockTriple1 = $this->getMockTriple();
		$mockTriple2 = $this->getMockTriple();
		$mockTriple3 = $this->getMockTriple();

		$name = new NamedNode('http://id.foo.org/barGraph');

		$mockTriple1->expects($this->at(0))->method('setContext')->with($this->equalTo(NULL));
		$mockTriple2->expects($this->at(0))->method('setContext')->with($this->equalTo(NULL));
		$mockTriple3->expects($this->at(0))->method('setContext')->with($this->equalTo(NULL));
		$mockTriple1->expects($this->at(1))->method('setContext')->with($this->equalTo($name));
		$mockTriple2->expects($this->at(1))->method('setContext')->with($this->equalTo($name));
		$mockTriple3->expects($this->at(1))->method('setContext')->with($this->equalTo($name));

		$graph = new Graph();
		$graph->add($mockTriple1);
		$graph->add($mockTriple2);
		$graph->add($mockTriple3);

		$graph->setName($name);
	}

	/**
	 * @test
	 */
	public function addExecutesTripleActions() {
		$passedTriple = NULL;
		$passedGraph = NULL;
		$graph = new Graph();
		$graph->addAction(function($triple, $graph) use (&$passedTriple, &$passedGraph) {
			$passedTriple = $triple;
			$passedGraph = $graph;
		});

		$mockTriple = $this->getMockTriple();
		$returnValue = $graph->add($mockTriple);

		$this->assertEquals($mockTriple, $passedTriple, 'Triple actions on add not executed.');
		$this->assertEquals($graph, $passedGraph, 'Graph not passed as second parameter.');
	}

	/**
	 * @test
	 */
	public function addActionRunsTripleActionOnAllGraphTriplesIfRunParameterIsSet() {
		$passedTriple = NULL;
		$passedGraph = NULL;

		$graph = new Graph();
		$mockTriple = $this->getMockTriple();
		$graph->add($mockTriple);

		$returnValue = $graph->addAction(function($triple, $graph) use (&$passedTriple, &$passedGraph) {
			$passedTriple = $triple;
			$passedGraph = $graph;
		}, TRUE);

		$this->assertEquals($mockTriple, $passedTriple, 'Triple actions not executed on existing elements.');
		$this->assertEquals($graph, $passedGraph, 'Graph not passed as second parameter.');
		$this->assertSame($graph, $returnValue, 'Wrong return value');
	}

	/**
	 * @test
	 */
	public function addAllAddsAllTriplesToGraph() {
		$mockTriple1 = $this->getMockTriple();
		$mockTriple2 = $this->getMockTriple();
		$mockTriple3 = $this->getMockTriple();

		$graph = new Graph();
		$graph->add($mockTriple1);

		$otherGraph = new Graph();
		$otherGraph->add($mockTriple2);
		$otherGraph->add($mockTriple3);

		$returnValue = $graph->addAll($otherGraph);

		$expected = array(
			$mockTriple1,
			$mockTriple2,
			$mockTriple3
		);
		$this->assertEquals($expected, iterator_to_array($graph));

		$this->assertSame($graph, $returnValue, 'Wrong return value');
	}

	/**
	 * @test
	 */
	public function everyReturnsTrueIfTestIsSuccessfulForAllTriples() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$i = 0;
		$parametersToTripleFilter = array();

		$this->assertTrue($graph->every(function($triple) use (&$i, &$parametersToTripleFilter) {
			$parametersToTripleFilter[$i] = $triple;
			$i++;
			return TRUE;
		}));
		$this->assertEquals($mockTriples, $parametersToTripleFilter);
	}

	/**
	 * @test
	 */
	public function everyReturnsFalseIfTestIsFailingForOneTriple() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$i = 0;

		$this->assertFalse($graph->every(function($triple) use (&$i) {
			if ($i == 1) return FALSE;
			$i++;
			return TRUE;
		}));
		$this->assertSame(1, $i);
	}

	/**
	 * @test
	 */
	public function someReturnsFalseIfTestIsFailingForAllTriples() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$i = 0;
		$parametersToTripleFilter = array();

		$this->assertFalse($graph->some(function($triple) use (&$i, &$parametersToTripleFilter) {
			$parametersToTripleFilter[$i] = $triple;
			$i++;
			return FALSE;
		}));
		$this->assertEquals($mockTriples, $parametersToTripleFilter);
	}

	/**
	 * @test
	 */
	public function someReturnsTrueIfTestIsSuccessfulForOneTriple() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$i = 0;

		$this->assertTrue($graph->some(function($triple) use (&$i) {
			if ($i == 1) return TRUE;
			$i++;
			return FALSE;
		}));
		$this->assertSame(1, $i);
	}

	/**
	 * @test
	 */
	public function filterReturnsGraphContainingSubElements() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$i = 0;
		$parametersToTripleFilter = array();

		$subGraph = $graph->filter(function($triple) use (&$i, &$parametersToTripleFilter) {
			$parametersToTripleFilter[$i] = $triple;
			$i++;
			if ($i != 1) return TRUE;
			return FALSE;
		});

		$expected = array(
			$mockTriples[1],
			$mockTriples[2]
		);
		$this->assertSame($expected, iterator_to_array($subGraph));
		$this->assertSame($mockTriples, $parametersToTripleFilter);
		$this->assertSame(3, $i);
	}

	/**
	 * @test
	 */
	public function removeRemovesTheGivenTriple() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$returnValue = $graph->remove($mockTriples[0]);

		$expected = array(
			$mockTriples[1],
			$mockTriples[2]
		);
		$this->assertSame($expected, iterator_to_array($graph));
		$this->assertSame($graph, $returnValue, 'Wrong return value');
	}

	/**
	 * @test
	 */
	public function removeFailsSilentlyIfTargetTripleNotFound() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$graph->remove($this->getMockTriple());

		$expected = array(
			$mockTriples[0],
			$mockTriples[1],
			$mockTriples[2]
		);
		$this->assertSame($expected, iterator_to_array($graph));
	}

	/**
	 * @test
	 */
	public function toArrayReturnsArrayRepresentation() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$actual = $graph->toArray();
		$this->assertSame($mockTriples, $actual);
	}

	/**
	 * @test
	 */
	public function getLengthCountsTheNumberOfTriples() {
		$mockTriples = $this->getMockTriples();
		$graph = $this->createGraphAndAddTriples($mockTriples);

		$this->assertSame(3, $graph->getLength());
	}

	protected function getMockTriple() {
		return $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\Triple', array(), array(), '', FALSE);
	}

	protected function getMockTriples() {
		return array(
			$this->getMockTriple(),
			$this->getMockTriple(),
			$this->getMockTriple()
		);
	}

	protected function createGraphAndAddTriples($triples) {
		$graph = new Graph();
		foreach ($triples as $triple) {
			$graph->add($triple);
		}
		return $graph;
	}
}
?>