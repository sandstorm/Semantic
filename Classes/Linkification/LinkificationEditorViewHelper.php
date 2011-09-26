<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Linkification;

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
class LinkificationEditorViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'input';

	/**
	 * @var \SandstormMedia\Semantic\Core\Schema\ClassSchemaResolver
	 * @inject
	 */
	protected $classSchemaResolver;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\TextAnnotationsRepository
	 * @inject
	 */
	protected $textAnnotationsRepository;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\ExternalReferenceRepository
	 * @inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var \TYPO3\FLOW3\MVC\Web\Routing\RouterInterface
	 * @inject
	 */
	protected $router;

	/**
	 * @inject
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $myReflectionService;

	/**
	 * @param string $property
	 * @param string $action
	 * @param string $controller
	 * @param string $subpackage
	 * @param string $package
	 */
	public function render($property, $action = NULL, $controller = NULL, $subpackage = NULL, $package = NULL) {
		$annotation = NULL;
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
			$className = get_class($formObject);
		} else {
			// Enrichment should also work with "NEW" action. For that, we need to analyze the data type of the target action of the form.
			// and that's precisely what we do in this block
			if ($controller === NULL) {
				$controller = $this->controllerContext->getRequest()->getControllerName();
			}
			if ($package === NULL && $subpackage === NULL) {
				$subpackage = $this->controllerContext->getRequest()->getControllerSubpackageKey();
			}
			if ($package === NULL) {
				$package = $this->controllerContext->getRequest()->getControllerPackageKey();
			}

			$controllerObjectName = $this->router->getControllerObjectName($package, $subpackage, $controller);
			if ($controllerObjectName === NULL) return '';

			$methodParametersOfTargetAction = $this->myReflectionService->getMethodParameters($controllerObjectName, $action . 'Action');

			if (!$this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName')) {
				return '';
			}
			$formObjectName = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObjectName');
			if (!isset($methodParametersOfTargetAction[$formObjectName]) || !isset($methodParametersOfTargetAction[$formObjectName]['class'])) {
				return '';
			}
			$className = $methodParametersOfTargetAction[$formObjectName]['class'];
			$formObject = NULL;
		}

		$propertySchema = $this->classSchemaResolver->getPropertySchema($className, $property);
		if (!isset($propertySchema['rdfEnrichText']) && !isset($propertySchema['rdfLinkify'])) {
			return '';
		}

		$this->tag->addAttribute('type', 'hidden');

		if (isset($propertySchema['rdfLinkify'])) {
			$this->buildLinkificationTag($formObject, $propertySchema);
		} else {
			$this->buildContinuousTextEnrichmentTag($formObject, $propertySchema);
		}

		return $this->tag->render();
	}

	protected function buildLinkificationTag($formObject, $propertySchema) {
		$this->tag->addAttribute('class', 'sm-semantic externalReference');

		if (isset($propertySchema['rdfLinkificationType'])) {
			$this->tag->addAttribute('data-rdf-linkification-type', $propertySchema['rdfLinkificationType']);
		}

		if (!$formObject) return;

		$metadata = $this->externalReferenceRepository->findOneByObjectAndPropertyName($formObject, $this->arguments['property']);
		if ($metadata) {
			$this->tag->addAttribute('value', $metadata->getValue());
		}
	}

	protected function buildContinuousTextEnrichmentTag($formObject, $propertySchema) {
		$this->tag->addAttribute('class', 'sm-semantic continuousText');

		if (!$formObject) return;

		$annotation = $this->textAnnotationsRepository->findOneByObjectAndPropertyName($formObject, $this->arguments['property']);

		if ($annotation !== NULL) {
			$this->tag->addAttribute('value', json_encode($annotation->getAnnotations()));
		}
	}
}
?>