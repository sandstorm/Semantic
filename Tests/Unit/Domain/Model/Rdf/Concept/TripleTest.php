<?php
/*                                                                        *
 * This script belongs to the "SandstormMedia.Semantic" package.          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3          *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * © 2011 Sandstorm Media UG (haftungsbeschränkt)                         *
 *        http://sandstorm-media.de                                       */

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

use \SandstormMedia\Semantic\Core\Rdf\Concept\Triple;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers SandstormMedia\Semantic\Core\Rdf\Concept\Triple
 */
class TripleTest extends \TYPO3\FLOW3\Tests\UnitTestCase {

	protected $mockSubject;

	protected $mockPredicate;

	protected $mockObject;

	protected $mockContext;

	public function setUp() {
		$this->mockSubject = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$this->mockPredicate = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$this->mockObject = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$this->mockContext = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
	}
	/**
	 * @test
	 */
	public function constructorArgumentsCanBeReadAgain() {
		$triple = new Triple($this->mockSubject, $this->mockPredicate, $this->mockObject, $this->mockContext);
		$this->assertEquals($this->mockSubject, $triple->getSubject());
		$this->assertEquals($this->mockPredicate, $triple->getPredicate());
		$this->assertEquals($this->mockObject, $triple->getObject());
		$this->assertEquals($this->mockContext, $triple->getContext());
	}

	/**
	 * @test
	 */
	public function toStringReturnsNQuadsRepresentation() {
		$triple = new Triple($this->mockSubject, $this->mockPredicate, $this->mockObject, $this->mockContext);

		$this->mockSubject->expects($this->any())->method('toNQuads')->will($this->returnValue('S'));
		$this->mockPredicate->expects($this->any())->method('toNQuads')->will($this->returnValue('P'));
		$this->mockObject->expects($this->any())->method('toNQuads')->will($this->returnValue('O'));
		$this->mockContext->expects($this->any())->method('toNQuads')->will($this->returnValue('Q'));
		$this->assertEquals('S P O Q .' . chr(10), $triple->__toString());
	}

	/**
	 * @test
	 */
	public function toNQuadsOptionalRendersContext() {
		$tripleWithContext = new Triple($this->mockSubject, $this->mockPredicate, $this->mockObject, $this->mockContext);
		$tripleWithoutContext = new Triple($this->mockSubject, $this->mockPredicate, $this->mockObject);

		$this->mockSubject->expects($this->any())->method('toNQuads')->will($this->returnValue('S'));
		$this->mockPredicate->expects($this->any())->method('toNQuads')->will($this->returnValue('P'));
		$this->mockObject->expects($this->any())->method('toNQuads')->will($this->returnValue('O'));
		$this->mockContext->expects($this->any())->method('toNQuads')->will($this->returnValue('Q'));

		$this->assertEquals('S P O Q .' . chr(10), $tripleWithContext->__toString());
		$this->assertEquals('S P O .' . chr(10), $tripleWithoutContext->__toString());
	}

	/**
	 * @test
	 */
	public function equalsCheckTestsSubjectPredicateAndObjectForEquality() {
		$subjectsAreEqual = TRUE;
		$subject1 = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$subject2 = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$subject1->expects($this->any())->method('equals')->with($subject2)->will($this->returnValue($subjectsAreEqual));

		$predicatesAreEqual = TRUE;
		$predicate1 = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$predicate2 = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$predicate1->expects($this->any())->method('equals')->with($predicate2)->will($this->returnValue($predicatesAreEqual));

		$objectsAreEqual = TRUE;
		$object1 = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$object2 = $this->getMock('SandstormMedia\Semantic\Core\Rdf\Concept\RdfNode');
		$object1->expects($this->any())->method('equals')->with($object2)->will($this->returnValue($objectsAreEqual));

		$triple1 = new Triple($subject1, $predicate1, $object1);
		$triple2 = new Triple($subject2, $predicate2, $object2);

		$this->assertTrue($triple2->equals($triple1));
	}
}
?>