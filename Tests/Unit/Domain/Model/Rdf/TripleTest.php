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

use \F3\Semantic\Domain\Model\Rdf\Triple;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @covers F3\Semantic\Domain\Model\Rdf\Triple
 */
class TripleTest extends \F3\FLOW3\Tests\UnitTestCase {

	protected $mockSubject;

	protected $mockPredicate;

	protected $mockObject;

	public function setUp() {
		$this->mockSubject = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$this->mockPredicate = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$this->mockObject = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
	}
	/**
	 * @test
	 */
	public function constructorArgumentsCanBeReadAgain() {
		$triple = new Triple($this->mockSubject, $this->mockPredicate, $this->mockObject);
		$this->assertEquals($this->mockSubject, $triple->getSubject());
		$this->assertEquals($this->mockPredicate, $triple->getPredicate());
		$this->assertEquals($this->mockObject, $triple->getObject());
	}

	/**
	 * @test
	 */
	public function toStringReturnsNTriplesRepresentation() {
		$triple = new Triple($this->mockSubject, $this->mockPredicate, $this->mockObject);

		$this->mockSubject->expects($this->any())->method('toNT')->will($this->returnValue('S'));
		$this->mockPredicate->expects($this->any())->method('toNT')->will($this->returnValue('P'));
		$this->mockObject->expects($this->any())->method('toNT')->will($this->returnValue('O'));
		$this->assertEquals('S P O.' . chr(10), $triple->__toString());
	}

	/**
	 * @test
	 */
	public function equalsCheckTestsSubjectPredicateAndObjectForEquality() {
		$subjectsAreEqual = TRUE;
		$subject1 = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$subject2 = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$subject1->expects($this->any())->method('equals')->with($subject2)->will($this->returnValue($subjectsAreEqual));

		$predicatesAreEqual = TRUE;
		$predicate1 = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$predicate2 = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$predicate1->expects($this->any())->method('equals')->with($predicate2)->will($this->returnValue($predicatesAreEqual));

		$objectsAreEqual = TRUE;
		$object1 = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$object2 = $this->getMock('F3\Semantic\Domain\Model\Rdf\RdfNode');
		$object1->expects($this->any())->method('equals')->with($object2)->will($this->returnValue($objectsAreEqual));

		$triple1 = new Triple($subject1, $predicate1, $object1);
		$triple2 = new Triple($subject2, $predicate2, $object2);

		$this->assertTrue($triple2->equals($triple1));
	}
}
?>