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
 * Helper for WHERE sql expression
 *
 * @author adrian
 */
class Where {

	/**
	 *
	 * @var mixed 
	 */
	private $expression = NULL;

	/**
	 *
	 * @var array 
	 */
	private $params = NULL;

	/**
	 *
	 * @var string
	 */
	private $sql;

	function __construct($expression, $params = NULL) {

		$this->expression = $expression;


		if ($params) {

			if (!is_array($params)) {

				$params = array($params);
			}

			$this->params = $params;
		}

		$this->createSql();
	}

	/**
	 * 
	 */
	private function createSql() {

		if (is_string($this->expression)) {

			$this->sql = $this->expression;

			return;
		} else if (is_array($this->expression)) {

			$sql = array();

			foreach ($this->expression as $field => $value) {

				$sql[] = "{$field} = ?"; // @TODO Add escape to field name
				$this->params[] = $value;
			}

			$this->sql = implode(' AND ', $sql);

			return;
		}

		throw new Exception("'Where' expression cannot be created");
	}

	/**
	 * Returns WHERE elements as associative array
	 * 
	 * @return array
	 */
	public function getWhere() {

		return array(
			'sql' => $this->sql,
			'params' => $this->params,
		);
	}

}
