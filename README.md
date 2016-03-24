# DB2FILE
Retrieve data from database and generate/print JSON or XML file.

## Installation
This library can be found on [Packagist](https://packagist.org/packages/natanaelsimoes/db2file).
We endorse that everything will work fine if you install this through [composer](http://getcomposer.org).

Add in your `composer.json`:
```json
{
    "require": {
        "natanaelsimoes/db2file": "1.0.0-beta"
    }
}
```
or in your bash:
```bash
$ composer require natanaelsimoes/db2file
```

## Features
- PSR-4 compliant for easy interoperability
- Uses PDO to cover majority databases (Firebird, MySQL, Oracle, PostgreSQL, SQLite, Microsoft SQL Server)
- Easy to add support for others PDO database drivers as needed
- Gets and prints JSON/XML version of your tables and queries

See documentation `docs/` for futher details about this library.

## Usage
```php
<?php
require 'vendor/autoload.php'
// Database configuration
$driver = DB2FILE\Converter::MySQL;
$dbname = 'information_schema';
$host = 'localhost';
$username = 'root';
$password = '123';
// Create converter
$converter = new DB2FILE\Converter($driver, $dbname, $host, $username, $password);
// Print the XML file in text/xml format
$converter->printXMLFromTable('ENGINES');
?>
```