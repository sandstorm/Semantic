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

use SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;
use SandstormMedia\Semantic\Core\Rdf\Concept\Dataset;
use SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
use SandstormMedia\Semantic\Core\Rdf\Concept\Graph;
use SandstormMedia\Semantic\Core\Rdf\Concept\Triple;
/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers SandstormMedia\Semantic\Core\Rdf\Concept\Graph
 */
class DatasetTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 * @expectedException SandstormMedia\Semantic\Exception
	 * @expectedExceptionCode 1339428519
	 */
	public function addGraphThrowsExceptionIfAGraphWithThisNameBelongsToTheGraph() {
		$dataset = new Dataset();
		$graphName = new NamedNode('http://id.foo.org/bar/baz');
		$graph1 = new Graph($graphName);
		$graph2 = new Graph($graphName);
		$dataset->addGraph($graph1)->addGraph($graph2);
	}

	/**
	 * @test
	 * @expectedException SandstormMedia\Semantic\Exception
	 * @expectedExceptionCode 1339428519
	 */
	public function createGraphThrowsExceptionIfAGraphWithThisNameBelongsToTheGraph() {
		$dataset = new Dataset();
		$graphName = new NamedNode('http://id.foo.org/bar/baz');
		$graph = new Graph($graphName);
		$dataset->addGraph($graph)->createGraph($graphName);
	}

}
?>