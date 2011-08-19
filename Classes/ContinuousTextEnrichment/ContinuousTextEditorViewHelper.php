<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\ContinuousTextEnrichment;

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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ContinuousTextEditorViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'input';

	/**
	 * @var \SandstormMedia\Semantic\Schema\ClassSchemaResolver
	 * @inject
	 */
	protected $classSchemaResolver;

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\TextAnnotationsRepository
	 * @inject
	 */
	protected $textAnnotationsRepository;

	/**
	 * @param string $property
	 * @return string
	 */
	public function render($property) {
		$annotation = NULL;
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
			$formObjectName = get_class($formObject);
			$annotation = $this->textAnnotationsRepository->findOneByObjectAndPropertyName($formObject, $property);

			$propertySchema = $this->classSchemaResolver->getPropertySchema(get_class($formObject), $property);
			if (!isset($propertySchema['rdfEnrichText'])) {
				return '';
			}
		} else {
			return '';
		}

		$this->tag->addAttribute('type', 'hidden');
		if ($annotation !== NULL) {
			$this->tag->addAttribute('value', json_encode($annotation->getAnnotations()));
		}
		$this->tag->addAttribute('class', 'sm-semantic continuousText');

		return $this->tag->render();

	}
}
?>