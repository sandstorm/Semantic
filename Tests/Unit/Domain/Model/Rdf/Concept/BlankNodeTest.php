<?php
declare(ENCODING = 'utf-8');
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

use \SandstormMedia\Semantic\Core\Rdf\Concept\BlankNode;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers SandstormMedia\Semantic\Core\Rdf\Concept\BlankNode
 */
class BlankNodeTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function toNTReturnsNTriplesNotation() {
		$blankNode = new BlankNode();
		$this->assertStringStartsWith('_:b', $blankNode->toNT());
	}

	/**
	 * @test
	 */
	public function toStringTest() {
		$blankNode = new BlankNode();
		$this->assertStringStartsWith('_:b', (string)$blankNode);
	}

	/**
	 * @test
	 */
	public function valueOfReturnsValueStartingWithB() {
		$blankNode = new BlankNode();
		$this->assertStringStartsWith('b', $blankNode->valueOf());
	}

	/**
	 * @test
	 */
	public function isEqualWithItself() {
		$blankNode = new BlankNode();
		$this->assertTrue($blankNode->equals($blankNode));
	}

	/**
	 * @test
	 */
	public function isNotEqualWithAnotherBlankNode() {
		$blankNode = new BlankNode();
		$otherBlankNode = new BlankNode();
		$this->assertFalse($blankNode->equals($otherBlankNode));
	}

	/**
	 * @test
	 */
	public function isNotEqualWithANamedNode() {
		$blankNode = new BlankNode();
		$namedNode = new \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode('http://foo.bar');
		$this->assertFalse($blankNode->equals($namedNode));
	}
}
?>