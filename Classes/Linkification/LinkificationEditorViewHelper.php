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

namespace SandstormMedia\Semantic\Linkification;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class LinkificationEditorViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'input';

	/**
	 * @var \SandstormMedia\Semantic\Core\Schema\ClassSchemaResolver
	 * @FLOW3\Inject
	 */
	protected $classSchemaResolver;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\TextAnnotationsRepository
	 * @FLOW3\Inject
	 */
	protected $textAnnotationsRepository;

	/**
	 * @var SandstormMedia\Semantic\Linkification\Domain\Repository\ExternalReferenceRepository
	 * @FLOW3\Inject
	 */
	protected $externalReferenceRepository;

	/**
	 * @var \TYPO3\FLOW3\Mvc\Routing\RouterInterface
	 * @FLOW3\Inject
	 */
	protected $router;

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 */
	protected $myReflectionService;

	/**
	 * @param string $property
	 */
	public function render($property) {
		$annotation = NULL;
		if ($this->viewHelperVariableContainer->exists('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject')) {
			$formObject = $this->viewHelperVariableContainer->get('TYPO3\Fluid\ViewHelpers\FormViewHelper', 'formObject');
			$className = get_class($formObject);
		} else {
			// Enrichment should also work with "NEW" action. For that, we need to analyze the data type of the target action of the form.
			// and that's precisely what we do in this block
			$action = $this->viewHelperVariableContainer->get('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'action');
			$controller = $this->viewHelperVariableContainer->get('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'controller');

			$subpackage = NULL;
			if ($this->viewHelperVariableContainer->exists('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'subpackage')) {
				$subpackage = $this->viewHelperVariableContainer->get('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'subpackage');
			}

			$package = $this->viewHelperVariableContainer->get('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'package');

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