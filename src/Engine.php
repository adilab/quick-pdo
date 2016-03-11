<?php

/**
 *
 * AdiPHP : Rapid Development Tools (http://adilab.net)
 * Copyright (c) Adrian Zurkiewicz
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @version     0.1
 * @copyright   Adrian Zurkiewicz
 * @link        http://adilab.net
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Adi\QuickPDO;

/**
 * Database engine helper
 *
 * @author adrian
 */
class Engine {

	/**
	 *
	 * @var string;
	 */
	private $type;

	/**
	 *
	 * @var DB
	 */
	private $db;

	/**
	 * 
	 * @param \Adi\QuickPDO\DB $db
	 */
	function __construct(DB $db) {
		$this->db = $db;

		$config = $db->getConfig();

		if (!$this->type = @$config['detail']['type']) {

			throw new Exception("Incorrect dsn string. No database type.");
		}
	}

	/**
	 * Returns database type
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Escapes name of element
	 * 
	 * @param string $name
	 */
	public function escapeElement(& $name) {

		switch ($this->type) {

			case 'mysql':
				$name = "`{$name}`";
				break;

			case 'pgsql':
				$name = "\"{$name}\"";
				break;
		}
	}

}
