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

use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode;
use \SandstormMedia\Semantic\Domain\Model\Rdf\Concept\Literal;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode
 */
class NamedNodeTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function toStringReturnsUri() {
		$namedNode = $this->createNamedNode('http://foo.bar');
		$this->assertEquals('http://foo.bar', (string)$namedNode);
		$this->assertEquals('http://foo.bar', $namedNode->valueOf());
	}

	/**
	 * @test
	 */
	public function asNTReturnsUriEnclosedInBrackets() {
		$namedNode = $this->createNamedNode('http://foo.bar');
		$this->assertEquals('<http://foo.bar>', $namedNode->toNT());
	}

	/**
	 * @test
	 */
	public function equalsReturnsTrueIfIriIsSame() {
		$namedNode1 = $this->createNamedNode('http://foo.bar');
		$namedNode2 = $this->createNamedNode('http://foo.bar');
		$this->assertTrue($namedNode1->equals($namedNode2));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseIfIriIsNotSame() {
		$namedNode1 = $this->createNamedNode('http://foo.bar');
		$namedNode2 = $this->createNamedNode('http://foo.bar#baz');
		$this->assertFalse($namedNode1->equals($namedNode2));
	}

	/**
	 * @test
	 */
	public function equalsReturnsFalseComparedWithLiteral() {
		$namedNode1 = $this->createNamedNode('http://foo.bar');
		$literal = new Literal('http://foo.bar');
		$this->assertFalse($namedNode1->equals($literal));
	}

	/**
	 * @test
	 */
	public function conversionToIriIsAttemptedUsingInjectedProfile() {
		$namedNode = $this->getAccessibleMock('SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode', array('dummy'), array('my:curie'));
		$profile = $this->getMock('SandstormMedia\Semantic\Domain\Model\Rdf\Environment\ProfileInterface');
		$namedNode->_set('profile', $profile);

		$profile->expects($this->once())->method('resolve')->with('my:curie')->will($this->returnValue('http://very.long/curie'));

		$namedNode->initializeObject();
		$this->assertEquals('http://very.long/curie', (string)$namedNode);
	}

	/**
	 * @test
	 */
	public function ifCurieCouldNotBeResolvedIriIsUsedWithoutModification() {
		$namedNode = $this->getAccessibleMock('SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode', array('dummy'), array('http://my.iri'));
		$profile = $this->getMock('SandstormMedia\Semantic\Domain\Model\Rdf\Environment\ProfileInterface');
		$namedNode->_set('profile', $profile);

		$profile->expects($this->once())->method('resolve')->with('http://my.iri')->will($this->returnValue(NULL));

		$namedNode->initializeObject();
		$this->assertEquals('http://my.iri', (string)$namedNode);
	}

	protected function createNamedNode($iri) {
		return new NamedNode($iri);
	}
}
?>