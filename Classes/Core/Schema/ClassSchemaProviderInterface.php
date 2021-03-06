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

namespace SandstormMedia\Semantic\Core\Schema;




/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface ClassSchemaProviderInterface {

	public function getPropertyNames($className, array $existingPropertyNames);

	public function getPropertySchema($className, $propertyName, array $existingPropertySchema);

	public function getClassSchema($className, array $existingClassSchema);

	public function getClassNamesWithSchema(array $existingClassNamesWithSchema);
}
?>