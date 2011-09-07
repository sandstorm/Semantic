<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Tests\Unit\Domain\Model\Rdf\Environment;

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

use SandstormMedia\Semantic\Core\Rdf\Environment\PrefixMap;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers SandstormMedia\Semantic\Core\Rdf\Environment\PrefixMap
 */
class PrefixMapTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	/**
	 *
	 * @var PrefixMap
	 */
	protected $prefixMap;

	public function setUp() {
		$this->prefixMap = new PrefixMap();
	}

	/**
	 * @test
	 */
	public function prefixUsingSetCanBeReadAgain() {
		$this->prefixMap->set('prefix', 'http://my.iri');
		$this->assertEquals('http://my.iri', $this->prefixMap->get('prefix'));
	}

	/**
	 * @test
	 */
	public function getReturnsNullOnUnknownPrefix() {
		$this->assertNull($this->prefixMap->get('notExistingPrefix'));
	}

	/**
	 * @test
	 */
	public function removeDeletesPrefix() {
		$this->prefixMap->set('prefix', 'http://my.iri');
		$this->prefixMap->remove('prefix');
		$this->assertNull($this->prefixMap->get('prefix'));
	}

	/**
	 * @test
	 */
	public function resolveReturnsIriForKnownPrefix() {
		$this->prefixMap->set('prefix', 'http://my.iri#');
		$curie = $this->prefixMap->resolve('prefix:foo:bar');
		$expected = 'http://my.iri#foo:bar';

		$this->assertSame($expected, $curie);
	}

	/**
	 * @test
	 */
	public function resolveReturnsNullForUnknownPrefix() {
		$this->assertNull($this->prefixMap->resolve('http://my.iri#foo:bar'));
	}

	/**
	 * @test
	 */
	public function shrinkReturnsCurieForKnownPrefix() {
		$this->prefixMap->set('prefix', 'http://my.iri#');
		$curie = $this->prefixMap->shrink('http://my.iri#some:property');
		$expected = 'prefix:some:property';

		$this->assertSame($expected, $curie);
	}

	/**
	 * @test
	 */
	public function shrinkReturnsFullIriForUnknownPrefix() {
		$iri = 'http://my.iri#foo:bar';
		$this->assertSame($iri, $this->prefixMap->shrink($iri));
	}
}
?>