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

use PDO;
use PDOStatement;


if (!defined('QuickPDO_CONFIGURATION_FILE')) {
    define('QuickPDO_CONFIGURATION_FILE', 'config/adi/databases.php');
}

/**
 * Adi PDO handler
 *
 * @author adrian
 */
class DB {
	
	/**
	 *
	 * @var array 
	 */
	static private $alias = array();
	
	/**
	 * Alies name of main database (default: first alias)
	 *
	 * @var string
	 */
	static private $main;
	
	/**
	 * Prepares database connections as aliases. 
	 * This method will be call automatically when the class is loaded.
	 * 
	 * @param array $databases_configuration Let it NULL in order to use databases configuration file
	 */
	static public function init($databases_configuration = NULL) {
		
		
		if (!$databases_configuration) { 
			
			if ((count(self::$alias)) or (!file_exists(QuickPDO_CONFIGURATION_FILE))) {
				
				return;
				
			}
			
			$databases_configuration = include QuickPDO_CONFIGURATION_FILE;
			
			if (!is_array($databases_configuration)) {
				
				return;
			}
			
		}
		
		
		foreach ($databases_configuration as $alias => $config) {
			
			if (!self::$main) {
				
				self::$main = $alias;
				
			}
			
			self::$alias[$alias] = new self($config['dsn'], $config['user'], $config['pass']);
		}
	}

	/**
	 * Sets a new main database
	 * 
	 * @param string $alias
	 * @throws Exception
	 */
	static public function setMain($alias) {
		
		if (!array_key_exists($alias, self::$alias)) {
			
			throw new Exception("The alias '{$alias}' not exists.");
		}
		
		self::$main = $alias;
	}
	
	
	/**
	 * Returns database instance for alias
	 * 
	 * @param string $alias
	 * @return self
	 * @throws Exception
	 */
	static public function alias($alias) {
		
		if (!count(self::$alias)) {
			
			throw new Exception("No database aliases.");	
		}
		
		if (!array_key_exists($alias, self::$alias)) {
			
			throw new Exception("The alias '{$alias}' not exists.");
		}
		
		return self::$alias[$alias];
		
	}
	
	
	/**
	 * Returns aliases list
	 * 
	 * @return array
	 */
	static public function getAliases() {
		
		$result = array();
		
		foreach (self::$alias as $key => $value) {
		
			$result[] = $key;
		}
		
		return $result;
	}
	
	/**
	 * Returns main database instance
	 * 
	 * @return self
	 */
	static public function main() {
		
		return self::alias(self::$main);
		
	}
	
	
	/**
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 *
	 * @var PDO;
	 */
	private $pdo;
	
	
	/**
	 *
	 * @var Engine
	 */
	private $engine;
	
	
	/**
	 *
	 * @var Schema
	 */
	private $schema;


	/**
	 * 
	 * @param string $dsn
	 * @param string $user
	 * @param string $pass
	 */
	function __construct($dsn, $user, $pass) {
		
		$type = explode(':', $dsn);
		$detail = array(
			
			'type' => @$type[0],			
		);
		
		if ($params = @$type[1]) {
			
			$params = explode(';', $params);
			
			foreach ($params as $value) {
				
				$value = explode('=', $value);
				
				if (@$value[0]) {
					
					$detail[$value[0]] = @$value[1];
				}	
			}
		}
		
		
		$this->config = array(
			'dsn' => $dsn,
			'user' => $user,
			'pass' => $pass,
			'detail' => $detail,
		);
		
		$this->engine = new Engine($this);
		$this->schema = new Schema($this);
		
	}

	/**
	 * Returns main configuration
	 * 
	 * @return array
	 */
	public function getConfig() {
		
		return $this->config;
	} 
	
	/**
	 * Returns database name
	 * 
	 * @return string
	 */
	public function getName() {
		
		return @$this->config['detail']['dbname'];
	}
	
	
	/**
	 * Connect to DB
	 * 
	 * @return type
	 */
	private function connect() {

		if ($this->pdo) {

			return;
		}

		$this->pdo = new PDO($this->config['dsn'], $this->config['user'], $this->config['pass']);
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	/**
	 * Returns instance of PDO connection
	 * 
	 * @return PDO
	 */
	public function getConnection() {
		
		$this->connect();
		
		return $this->pdo;
	}
	
	/**
	 * 
	 * @return Engine
	 */
	function getEngine() {
		return $this->engine;
	}

	/**
	 * Returns instans of database schema 
	 * 
	 * @return Schema
	 */
	function getSchema() {
		return $this->schema;
	}

	/**
	 * Returns database type
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->engine->getType();
	}
	
	/**
	 * Performs sql sequence
	 * 
	 * @param string $sql
	 * @param mixed $inputs_parameters Simple input parameter or array of inputs
	 * @return PDOStatement
	 */
	public function query($sql, $inputs_parameters = array()) {
		
		$this->connect();
		
		if (!is_array($inputs_parameters)) {
			
			$inputs_parameters = array($inputs_parameters);
		}
		
		$query = $this->pdo->prepare($sql);
		$query->execute($inputs_parameters);
		
		return $query;
	}
	
	
	/**
	 * Performs sql sequence
	 * 
	 * @param string $sql
	 * @param mixed $inputs_parameters Simple input parameter or array of inputs
	 * @return array
	 */
	public function & fetch($sql, $inputs_parameters = array()) {
		
		$result = array();
		$query = $this->query($sql, $inputs_parameters);
		
		while ($row = $query->fetch()) {
			
			$result[] = $row;
			
		}
		
		return $result;
		
	}
	
	/**
	 * Performs sql sequence
	 * 
	 * @param string $sql
	 * @param mixed $inputs_parameters Simple input parameter or array of inputs
	 */
	public function execute($sql, $inputs_parameters = array()) {
		
		$this->query($sql, $inputs_parameters);
		
	}
	
	/**
	 * Performs sql sequence and return the first row
	 * 
	 * @param string $sql
	 * @param mixed $inputs_parameters
	 * @return array|FALSE Returns FALSE if no query result
	 */
	public function & row($sql, $inputs_parameters = array()) {
		
		$result = FALSE;
		$query = $this->fetch($sql, $inputs_parameters);
		
		if (count($query)) {
			
			$result = $query[0];
		}
		
		return $result;
	}
	
	/**
	 * Performs sql sequence and return the first of the first row
	 * 
	 * @param string $sql
	 * @param mixed $inputs_parameters
	 * @return string|FALSE Returns FALSE if no query result
	 */
	public function & value($sql, $inputs_parameters = array()) {
		
		$return = FALSE;
		
		if ($row = $this->row($sql, $inputs_parameters)) {
			
			reset($row);
			$return = current($row);
		}
		
		return $return;
	}
	
	
	/**
	 * Inserts a new record into table
	 * 
	 * @param string $table Table name
	 * @param array $data New record as associative table
	 * @return miexd Id of inserted record (not supported for composite primary key).
	 */
	public function insert($table, $data) {

		$fields = array();
		$values = array();
		$params = array();
		$table_name = $table;
		$type = $this->engine->getType();

		foreach ($data as $field => $value) {

			$this->engine->escapeElement($field);
			$fields[] = $field;
			$values[] = '?';
			$params[] = $value;
		}

		$fields = implode(',', $fields);
		$values = implode(',', $values);

		$this->engine->escapeElement($table);
		$sql = "INSERT INTO {$table} ( {$fields} ) VALUES ( {$values} ) ";
		
		
		if ($type == 'pgsql') {
			
			$pk = $this->schema->getPK($table_name);
			
			if (count($pk) == 1) {
				
				$pk = $pk[0];
				$sql .= " RETURNING {$pk}";
				
			}
			
			return $this->value($sql, $params);

		}

		
		
		if ($type == 'mysql') {
			
			$this->execute($sql, $params);
			return $this->getConnection()->lastInsertId();
		}
		
		$this->execute($sql, $params);
		
//		echo $sql;
	}

	/**
	 * Update a record in table
	 * 
	 * @param string $table Table name
	 * @param array $data New record as associative table
	 * @param mixed $where Where expression as string, associative array, or instance of Where
	 */
	public function update($table, $data, $where) {

		if (!$where instanceof Where) {

			$where = new Where($where);
		}

		$where = $where->getWhere($this);

		$fields = array();
		$params = array();

		foreach ($data as $field => $value) {

			$this->engine->escapeElement($field);
			$fields[] = "{$field} = ?"; 
			$params[] = $value;
		}

		$fields = implode(',', $fields);

		$this->engine->escapeElement($table);
		$sql = "UPDATE {$table} SET {$fields} WHERE {$where['sql']} "; 

		if (is_array($where['params'])) {

			foreach ($where['params'] as $param) {

				$params[] = $param;
			}
		}


		$this->execute($sql, $params);
//		echo $sql;
//		print_r($params);
	}

	/**
	 * Update all records in table
	 * 
	 * @param string $table Table name
	 * @param array $data New record as associative table
	 */
	public function updateAll($table, $data) {

		$fields = array();
		$params = array();

		foreach ($data as $field => $value) {

			$this->engine->escapeElement($field);
			$fields[] = "{$field} = ?"; 
			$params[] = $value;
		}

		$fields = implode(',', $fields);
		
		$this->engine->escapeElement($table);
		$sql = "UPDATE {$table} SET {$fields} "; 

		$this->execute($sql, $params);
//		echo $sql;
	}

}

/**
 * Auto initiation
 */

DB::init();