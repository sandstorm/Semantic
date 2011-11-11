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

namespace SandstormMedia\Semantic\Rdfa;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class FluidSyntaxTreeInterceptor implements \TYPO3\Fluid\Core\Parser\InterceptorInterface {

	/**
	 * Is the interceptor enabled right now?
	 * @var boolean
	 */
	protected $interceptorEnabled = TRUE;

	/**
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @return \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 */
	public function process(\TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node, $interceptorPosition, \TYPO3\Fluid\Core\Parser\ParsingState $parsingState) {
		if (!$this->interceptorEnabled) {
			return $node;
		}

		$subNode = $node;
		// Hack for dealing with escape ViewHelper
		if ($subNode instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode && $subNode->getUninitializedViewHelper() instanceof \TYPO3\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper) {
			$argumentsReflection = new \ReflectionProperty($subNode, 'arguments');
			$argumentsReflection->setAccessible(TRUE);
			$arguments = $argumentsReflection->getValue($subNode);
			$subNode = $arguments['value'];
		}

		if ($subNode instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode) {
			$objectPathReflection = new \ReflectionProperty($subNode, 'objectPath');
			$objectPathReflection->setAccessible(TRUE);
			$objectPath = $objectPathReflection->getValue($subNode);

			$newNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode(
					new RdfaWrapperViewHelper(),
					array('propertyPath' => new \TYPO3\Fluid\Core\Parser\SyntaxTree\TextNode($objectPath))
				);

			$newNode->addChildNode($node);
			$node = $newNode;
		} else {
			// TODO: log!
		}
		return $node;
	}

	/**
	 * This interceptor wants to hook into object accessor creation, and opening / closing ViewHelpers.
	 *
	 * @return array Array of INTERCEPT_* constants
	 */
	public function getInterceptionPoints() {
		return array(
			\TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OBJECTACCESSOR
		);
	}
}
?>