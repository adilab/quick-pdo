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
    define('QuickPDO_CONFIGURATION_FILE', 'databases-configuration.php');
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
				
				echo '*';
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

	function __construct($dsn, $user, $pass) {
		$this->config = array(
			'dsn' => $dsn,
			'user' => $user,
			'pass' => $pass,
		);
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
	
	

}




/**
 * Auto initiation
 */

DB::init();