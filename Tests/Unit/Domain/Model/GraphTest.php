<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Tests\Unit\Domain\Model;

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

use \F3\Semantic\Domain\Model\Literal;
use \F3\Semantic\Domain\Model\Graph;
use \F3\Semantic\Domain\Model\Triple;
/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class GraphTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function addExecutesTripleActions() {
		$passedTriple = NULL;
		$graph = new Graph(array(function($triple) use (&$passedTriple) {
			$passedTriple = $triple;
		}));

		$mockTriple = $this->getMockTriple();
		$returnValue = $graph->add($mockTriple);

		$this->assertEquals($mockTriple, $passedTriple, 'Triple actions on add not executed.');
		$this->assertSame($graph, $returnValue);
	}

	public function addActionAddsTheActionToTheListOfActions

	protected function getMockTriple() {
		return $this->getMock('F3\Semantic\Domain\Model\Triple', array(), array(), '', FALSE);
	}
}
?>