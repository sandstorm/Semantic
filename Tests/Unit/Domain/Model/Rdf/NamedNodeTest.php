<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Tests\Unit\Domain\Model\Rdf;

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

use \F3\Semantic\Domain\Model\Rdf\NamedNode;
use \F3\Semantic\Domain\Model\Rdf\Literal;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers F3\Semantic\Domain\Model\Rdf\NamedNode
 */
class NamedNodeTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function toStringReturnsUri() {
		$namedNode = new NamedNode('http://foo.bar');
		$this->assertEquals('http://foo.bar', (string)$namedNode);
		$this->assertEquals('http://foo.bar', $namedNode->valueOf());
	}

	/**
	 * @test
	 */
	public function asNTReturnsUriEnclosedInBrackets() {
		$namedNode = new NamedNode('http://foo.bar');
		$this->assertEquals('<http://foo.bar>', $namedNode->toNT());
	}

	/**
	 * @test
	 */
	public function equalsReturnsTrueIfIriIsSame() {
		$namedNode1 = new NamedNode('http://foo.bar');
		$namedNode2 = new NamedNode('http://foo.bar');
		$this->assertTrue($namedNode1->equals($namedNode2));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseIfIriIsNotSame() {
		$namedNode1 = new NamedNode('http://foo.bar');
		$namedNode2 = new NamedNode('http://foo.bar#baz');
		$this->assertFalse($namedNode1->equals($namedNode2));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseComparedWithLiteral() {
		$namedNode1 = new NamedNode('http://foo.bar');
		$literal = new Literal('http://foo.bar');
		$this->assertFalse($namedNode1->equals($literal));
	}
}
?>