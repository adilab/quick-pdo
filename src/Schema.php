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
 * Handle for database schema
 *
 * @author adrian
 */
class Schema {

	/**
	 *
	 * @var DB
	 */
	private $db;

	/**
	 * 
	 * @param DB $db
	 */
	function __construct(DB $db) {
		$this->db = $db;
	}

	/**
	 * Returns the list of columns with column information.
	 * 
	 * @param string $table
	 * @return array
	 * @throws Exception
	 */
	public function getColumns($table) {

		// @TODO add cache

		$this->db->getEngine()->escapeElement($table);

		$sql = "SELECT column_name, is_nullable = 'YES' AS is_nullable, data_type, character_maximum_length, column_default 
				FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$table}'
				ORDER BY ordinal_position";


		// @TODO add body

		throw new Exception("The method is not implemented yet.");
	}

	/**
	 * Returns the list of tables.
	 * 
	 * @return array
	 * @throws Exception
	 */
	public function getTable() {

		// @TODO add cache
		// @TODO add body

		throw new Exception("The method is not implemented yet.");
	}

	/**
	 * Returns list of primary key columns as array.
	 * 
	 * @param string $table
	 * @return array
	 * @throws Exception
	 */
	public function getPK($table) {

		// @TODO add cache

		$result = array();
		$type = $this->db->getEngine()->getType();
		$database = $this->db->getName();

		switch ($type) {

			case "mysql" :

				$sql = "SHOW INDEX FROM `{$table}` WHERE key_name = 'PRIMARY'";
				$filed = 'Column_name';
				$table_check_sql = "SELECT count(*) FROM information_schema.tables WHERE table_schema = '{$database}' AND table_name = '{$table}'";
				break;

			case "pgsql" :

				$sql = "	SELECT
				c.column_name, c.data_type
				FROM
				information_schema.table_constraints tc
				JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name)
				JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name
				where constraint_type = 'PRIMARY KEY' and tc.table_name = '{$table}';";

				$filed = 'column_name';
				$table_check_sql = "SELECT count(*) FROM information_schema.tables WHERE table_catalog = '{$database}' AND table_name = '{$table}'";
				break;

			default : throw new Exception("Database type '{$type}' is not yet supported.");
		}

		if (!$this->db->value($table_check_sql)) {

			throw new Exception("Table '{$table}' doesn't exist.");
		}

		foreach ($this->db->query($sql)->fetchAll() as $row) {

			$result[] = $row[$filed];
		}

		return $result;
	}

}
