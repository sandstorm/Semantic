<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "Semantic".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use SandstormMedia\Semantic\Domain\Model\Rdf\Concept\NamedNode;

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AnnotatedString {

	/**
	 * The underlying string which is being annotated
	 *
	 * @var string
	 */
	protected $string;

	protected $beginOfData;
	protected $endOfData;

	/**
	 * @var SandstormMedia\Semantic\Domain\Model\Rdf\Environment\ProfileInterface
	 * @inject
	 */
	protected $profile;

	public function __construct($string) {
		$this->string = $string;
	}
	public function add(NamedNode $subject, NamedNode $type, $offset, $length) {
		$end = $offset + $length;

		$storage = new \stdClass();
		$storage->subject = $subject;
		$storage->begin = $offset;
		$storage->end = $end;
		$storage->type = $type;

		if (!isset($this->beginOfData[$offset])) {
			$this->beginOfData[$offset] = array();
		}
		$this->beginOfData[$offset][] = $storage;

		if (!isset($this->endOfData[$end])) {
			$this->endOfData[$end] = array();
		}
		$this->endOfData[$end][] = $storage;
	}

	public function getStringWithAnnotations() {
		$output = '';
		$length = strlen($this->string);

		for ($i = 0; $i < $length; $i++) {
			if (isset($this->endOfData[$i])) {
				foreach ($this->endOfData[$i] as $storage) {
					$output .= '</span>'; // HACK; which creates not correctly nested HTML as soon as ranges OVERLAP!!
				}
			}

			if (isset($this->beginOfData[$i])) {
				foreach ($this->beginOfData[$i] as $storage) {
					$output .= '<span
						about="' . $storage->subject . '"
						typeof="' . $storage->type . '">'; // TODO: HTML Special Chars // use TagBuilder!
				}
			}

			$output .= $this->string[$i];
		}

		return $output;
	}
}
?>