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

use \F3\Semantic\Domain\Model\UriReference;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class UriReferenceTest extends \F3\FLOW3\Tests\UnitTestCase {

	protected $mockSettings = array('namespaces' => array(
		'sioc' => 'http://rdfs.org/sioc/ns#',
		'dcterms' => 'http://purl.org/dc/terms/',
		'sioctypes' => 'http://rdfs.org/sioc/types#'
	));

	/**
	 * @test
	 */
	public function resourceUriCanBeSetInConstructor() {
		$uriReference = new UriReference('http://foo.bar');
		$this->assertEquals('http://foo.bar', $uriReference->getUri());
	}

	/**
	 * @test
	 */
	public function curieEnclosedInBracketsCanBeSetInConstructor() {
		$uriReference = new UriReference('[dcterms:title:a]');
		$uriReference->injectSettings($this->mockSettings);
		$uriReference->initializeObject();

		$this->assertEquals('http://purl.org/dc/terms/title:a', $uriReference->getUri());
	}

	/**
	 * @test
	 * @expectedException \Exception
	 */
	public function curieEnclosedInBracketsThrowsExceptionIfPrefixNotFound() {
		$uriReference = new UriReference('[someNonExistingPrefix:title:a]');
		$uriReference->injectSettings($this->mockSettings);
		$uriReference->initializeObject();

		$this->assertEquals('http://purl.org/dc/terms/title:a', $uriReference->getUri());
	}

	/**
	 * @test
	 */
	public function toStringReturnsUri() {
		$uriReference = new UriReference('http://foo.bar');
		$this->assertEquals('http://foo.bar', (string)$uriReference);
	}

	/**
	 * @test
	 */
	public function asN3ReturnsWrappedUri() {
		$uriReference = new UriReference('http://foo.bar');
		$this->assertEquals('<http://foo.bar>', $uriReference->asN3());
	}
}
?>