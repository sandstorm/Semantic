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

use \SandstormMedia\Semantic\Core\Rdf\Concept\Literal;
use \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class LiteralTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function nominalValueCanBeSetInConstructor() {
		$literal = new Literal('someString');
		$this->assertEquals('someString', $literal->valueOf());
		$this->assertNull($literal->getDataType());
		$this->assertNull($literal->getLanguage());
		$this->assertSame('someString', (string)$literal);
	}

	/**
	 * @test
	 */
	public function integerNominalValueIsRetrievableWithoutTypeConversion() {
		$literal = new Literal(42);
		$this->assertsame(42, $literal->valueOf());
		$this->assertSame('42', (string)$literal);
	}

	/**
	 * @test
	 */
	public function valueOfTypeDateTimeSetsTheDataTypeAppropriately() {
		$literal = new Literal(new \DateTime('2010-10-02T10:11:35+01:00'));
		$this->assertEquals('2010-10-02T10:11:35+01:00', $literal->valueOf());
		$this->assertEquals('http://www.w3.org/2001/XMLSchema#dateTime', (string)$literal->getDataType());
	}

	/**
	 * @test
	 */
	public function languageCanBeSetInConstructorAndIsNormalizedToLowerCase() {
		$literal = new Literal('someString', 'de-DE');
		$this->assertEquals('de-de', $literal->getLanguage());
	}

	/**
	 * @test
	 */
	public function dataTypeCanBeSetInConstructor() {
		$dataType = new \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode('http://foo,bar/named');
		$literal = new Literal(42, NULL, $dataType);
		$this->assertSame($dataType, $literal->getDataType());
	}

	public function dataProviderForNT() {
		return array(
			array(
				'title' => 'Simple Literal',
				'literal' => 'my simple Literal',
				'expected' => '"my simple Literal"'
			),
			array(
				'title' => 'Literal with newlines',
				'literal' => 'Literal' . "\r\n" . "\n" . 'with some text after two newlines.',
				'expected' => '"Literal\n\nwith some text after two newlines."'
			),

			array(
				'title' => 'Literal with Data Type',
				'literal' => new \DateTime('2010-10-02T10:11:35+01:00'),
				'expected' => '"2010-10-02T10:11:35+01:00"^^<http://www.w3.org/2001/XMLSchema#dateTime>'
			),
		);
	}

	/**
	 * @test
	 * @dataProvider dataProviderForNT
	 */
	public function toNTReturnsNT($title, $literal, $expected) {
		$literal = new Literal($literal);
		$this->assertEquals($expected, $literal->toNT(), $title);
	}

	public function equalNodesDataProvider() {
		return array(
			array(
				'Simple Comparison',
				'node1' => new Literal('foo.bar'),
				'node2' => new Literal('foo.bar', NULL, NULL),
			),
			array(
				'Comparison with language; with normalizing language',
				'node1' => new Literal('foo.bar', 'de_DE'),
				'node2' => new Literal('foo.bar', 'de_de', NULL),
			),
			array(
				'Comparison with data type',
				'node1' => new Literal('foo.bar', 'de_DE', new NamedNode('http://my.datatype')),
				'node2' => new Literal('foo.bar', 'de_de', new NamedNode('http://my.datatype')),
			),
		);
	}

	/**
	 * @test
	 * @dataProvider equalNodesDataProvider
	 */
	public function equalsReturnsTrueForEqualNodes($title, $node1, $node2) {
		$this->assertTrue($node1->equals($node2), $title);
	}

	public function notEqualNodesDataProvider() {
		return array(
			array(
				'Different nominalValues',
				'node1' => new Literal('foo.bar'),
				'node2' => new Literal('foo.bar '),
			),
			array(
				'Different language',
				'node1' => new Literal('foo.bar', 'de_de'),
				'node2' => new Literal('foo.bar', 'de_ch'),
			),
			array(
				'Different data type',
				'node1' => new Literal('foo.bar', NULL, new NamedNode('http://my.datatype')),
				'node2' => new Literal('foo.bar', NULL, new NamedNode('http://my.datatype/baz')),
			),
			array(
				'Different data type (one null, one not null',
				'node1' => new Literal('foo.bar', NULL, new NamedNode('http://my.datatype')),
				'node2' => new Literal('foo.bar'),
			),
			array(
				'Different type',
				'node1' => new Literal('http://my.datatype/baz'),
				'node2' => new NamedNode('http://my.datatype/baz')
			),
		);
	}

	/**
	 * @test
	 * @dataProvider notEqualNodesDataProvider
	 */
	public function equalsReturnsFalseForNotEqualNodes($title, $node1, $node2) {
		$this->assertFalse($node1->equals($node2), $title);
	}
}
?>