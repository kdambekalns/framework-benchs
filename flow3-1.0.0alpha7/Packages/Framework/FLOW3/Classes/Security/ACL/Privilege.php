<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Security\ACL;

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

/**
 * The representation of a privilege, that a role has for a given resource. E.g. READ or WRITE.
 * A privilege can be explicitly granted or denied. In the policy file this is expressed by appending
 * _DENY or _GRANT to the privilege's identifier.
 *
 * @version $Id: Privilege.php 2813 2009-07-16 14:02:34Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class Privilege {

	/**
	 * The string identifier of this privilege
	 * @var string
	 */
	protected $identifier;

	/**
	 * TRUE if this privilege is granting, FALSE if it is denying
	 * @var boolean
	 */
	protected $isGrant;

	/**
	 * Constructor.
	 *
	 * @param string $identifier An identifier for this privilege. Note: Always prefix your package key for custom privileges!
	 * @param boolean $isGrant The isGrant flag of the privilege
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct($identifier, $isGrant = FALSE) {
		$this->identifier = $identifier;
		$this->isGrant = $isGrant;
	}

	/**
	 * Returns the string representation of this privilege
	 *
	 * @return string The string representation of this privilege
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getPrivilegeType() {
		return $this->identifier;
	}

	/**
	 * Sets this privilege to a granting privilege
	 *
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setGrant() {
		$this->isGrant = TRUE;
	}

	/**
	 * Sets this privilege to a denying privilege
	 *
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setDeny() {
		$this->isGrant = FALSE;
	}

	/**
	 * Returns TRUE if this privilege object grants the privilege it represents
	 *
	 * @return boolean TRUE if this privilege object grants the privilege it represents
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isGrant() {
		return $this->isGrant;
	}

	/**
	 * Returns TRUE if this privilege object denies the privilege it represents
	 *
	 * @return boolean TRUE if this privilege object denies the privilege it represents
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function isDeny() {
		return !$this->isGrant;
	}

	/**
	 * Returns the string representation of this privilege
	 *
	 * @return string The string representation of this privilege
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __toString() {
		return $this->identifier;
	}
}

?>