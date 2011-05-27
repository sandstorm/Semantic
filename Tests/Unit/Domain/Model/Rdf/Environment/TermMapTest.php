<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Tests\Unit\Domain\Model\Rdf\Environment;

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

use F3\Semantic\Domain\Model\Rdf\Environment\TermMap;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers F3\Semantic\Domain\Model\Rdf\Environment\TermMap
 */
class TermMapTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 *
	 * @var PrefixMap
	 */
	protected $termMap;

	public function setUp() {
		$this->termMap = new TermMap();
	}

	/**
	 * @test
	 */
	public function termUsingSetCanBeReadAgain() {
		$this->termMap->set('term', 'http://my.iri');
		$this->assertEquals('http://my.iri', $this->termMap->get('term'));
	}

	/**
	 * @test
	 */
	public function getReturnsNullOnUnknownTerm() {
		$this->assertNull($this->termMap->get('notExistingTerm'));
	}

	/**
	 * @test
	 */
	public function removeDeletesTerm() {
		$this->termMap->set('term', 'http://my.iri');
		$this->termMap->remove('term');
		$this->assertNull($this->termMap->get('term'));
	}

	/**
	 * @test
	 */
	public function resolveReturnsIriForKnownTerm() {
		$this->termMap->set('term', 'http://my.iri#foo');
		$iri = $this->termMap->resolve('term');
		$expected = 'http://my.iri#foo';

		$this->assertSame($expected, $iri);
	}

	/**
	 * @test
	 */
	public function resolveReturnsNullForUnknownTerm() {
		$this->assertNull($this->termMap->resolve('http://unknownIri'));
	}

	/**
	 * @test
	 */
	public function shrinkReturnsTerm() {
		$this->termMap->set('term', 'http://my.iri');
		$term = $this->termMap->shrink('http://my.iri');
		$expected = 'term';

		$this->assertSame($expected, $term);
	}

	/**
	 * @test
	 */
	public function shrinkReturnsFullIriForUnknownTerm() {
		$iri = 'http://my.iri#foo:bar';
		$this->assertSame($iri, $this->termMap->shrink($iri));
	}
}
?>