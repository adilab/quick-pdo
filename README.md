Adi PDO handler
========================

Allows to work with PDO faster and more convenient way.

Installing
----------

Preferred way to install is with [Composer](https://getcomposer.org/).

Install this library using composer:

```console
$ composer require adilab/quick-pdo
```

Configuration:
-------------
```php
 /**
 * databases-configuration.php
 */ 

return array(
	'db1' => array(

		'dsn' => 'mysql:host=127.0.0.1;dbname=db1;charset=utf8',
		'user' => 'db1',
		'pass' => '********',
	),
	'db2' => array(

		'dsn' => 'mysql:host=127.0.0.1;dbname=db2;charset=utf8',
		'user' => 'db2',
		'pass' => '********',
	),	
);


```


Usage:
-------------
```php
require('vendor/autoload.php');

use Adi\QuickPDO\DB;

// Usage fetch() method
foreach (DB::main()->fetch('SELECT * FROM my_table WHERE my_column > ?', 10) as $row) {

	var_dump($row);
} 

// Usage row() method
var_dump(DB::alias('db2')->row("SELECT * FROM my_table WHERE my_column = ?", 2));

// Usage value() method
if (DB::alias('db1')->value("SELECT count(*) > 1 FROM my_table")) {

	echo 'There are many records.';
}

// Usage insert() method
DB::main()->insert('my_table', array('my_column1' => 'a', 'my_column2' => 'b'));

// Usage update() method
DB::main()->update('my_table', array('my_column1' => 'a', 'my_column2' => 'b'), new Where('id > ? AND id < ? OR id = ?', array(10,20,30)));
DB::main()->update('my_table', array('my_column1' => 'a', 'my_column2' => 'b'), array('id' => 25));

```

