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

use F3\Semantic\Domain\Model\Rdf\Environment\Profile;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers F3\Semantic\Domain\Model\Rdf\Environment\Profile
 */
class ProfileTest extends \F3\FLOW3\Tests\UnitTestCase {

	/**
	 *
	 * @var Profile
	 */
	protected $profile;

	protected $mockPrefixMap;
	protected $mockTermMap;

	public function setUp() {
		$this->profile = $this->getAccessibleMock('F3\Semantic\Domain\Model\Rdf\Environment\Profile', array('dummy'));

		$this->mockPrefixMap = $this->getMock('F3\Semantic\Domain\Model\Rdf\Environment\PrefixMap');
		$this->profile->_set('prefixes', $this->mockPrefixMap);

		$this->mockTermMap = $this->getMock('F3\Semantic\Domain\Model\Rdf\Environment\TermMap');
		$this->profile->_set('terms', $this->mockTermMap);
	}

	/**
	 * @test
	 */
	public function getPrefixesReturnsPrefixMap() {
		$this->assertSame($this->mockPrefixMap, $this->profile->getPrefixes());
	}

	/**
	 * @test
	 */
	public function getTermsReturnsTermMap() {
		$this->assertSame($this->mockTermMap, $this->profile->getTerms());
	}

	/**
	 * @test
	 */
	public function resolveDispatchesToPrefixMapIfStringContainsColon() {
		$this->mockPrefixMap->expects($this->once())->method('resolve')->with('foo:bar')->will($this->returnValue('http://my.iri/bar'));

		$this->assertSame('http://my.iri/bar', $this->profile->resolve('foo:bar'));
	}

	/**
	 * @test
	 */
	public function resolveDispatchesToTermMapIfStringDoesNotContainColon() {
		$this->mockTermMap->expects($this->once())->method('resolve')->with('someString')->will($this->returnValue('http://my.iri/bar'));

		$this->assertSame('http://my.iri/bar', $this->profile->resolve('someString'));
	}

	/**
	 * @test
	 */
	public function setPrefixDispatchesToPrefixMap() {
		$this->mockPrefixMap->expects($this->once())->method('set')->with('myPrefix', 'http://the.iri');

		$this->profile->setPrefix('myPrefix', 'http://the.iri');
	}

	/**
	 * @test
	 */
	public function setTermDispatchesToTermMap() {
		$this->mockTermMap->expects($this->once())->method('set')->with('myTerm', 'http://the.iri');

		$this->profile->setTerm('myTerm', 'http://the.iri');
	}
}
?>