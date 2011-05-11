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

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class LiteralTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function valueCanBeSetInConstructor() {
		$literal = new Literal('someString');
		$this->assertEquals('someString', $literal->getValue());
	}

	/**
	 * @test
	 */
	public function valueOfTypeDateTimeSetsTheDataTypeAppropriately() {
		$literal = new Literal(new \DateTime('2010-10-02T10:11:35+01:00'));
		$this->assertEquals('2010-10-02T10:11:35+01:00', $literal->getValue());
		$this->assertEquals('http://www.w3.org/2001/XMLSchema#dateTime', (string)$literal->getType());
	}

	/**
	 * @test
	 */
	public function languageCanBeSetInConstructor() {
		$this->markTestIncomplete('TODO');
	}

	/**
	 * @test
	 */
	public function dataTypeCanBeSetInConstructor() {
		$this->markTestIncomplete('TODO');
	}

	public function dataProviderForN3() {
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
				'expected' => '"2010-10-02T10:11:35+01:00"^^http://www.w3.org/2001/XMLSchema#dateTime'
			),
		);
	}

	/**
	 * @test
	 * @dataProvider dataProviderForN3
	 */
	public function asN3ReturnsN3($title, $literal, $expected) {
		$literal = new Literal($literal);
		$this->assertEquals($expected, $literal->asN3(), $title);
	}
}
?>