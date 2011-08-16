<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\ExternalReference;

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
class ExternalReferenceEditorViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'input';

	/**
	 * @var SandstormMedia\Semantic\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var \SandstormMedia\Semantic\Schema\ClassSchemaResolver
	 * @inject
	 */
	protected $classSchemaResolver;

	/**
	 * @param string $property
	 * @return string
	 */
	public function render($property) {

		$metadata = NULL;
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
			$metadata = $this->externalReferenceRepository->findOneByObjectAndPropertyName($formObject, $property);

			$propertySchema = $this->classSchemaResolver->getPropertySchema(get_class($formObject), $property);
			if (!isset($propertySchema['rdfLinkify'])) {
				return '';
			}
		} else {
			return '';
		}

		$this->tag->addAttribute('type', 'hidden');
		if ($metadata) {
			$this->tag->addAttribute('value', $metadata->getValue());
		}
		$this->tag->addAttribute('class', 'sm-semantic externalReference');

		return $this->tag->render();
	}
}
?>