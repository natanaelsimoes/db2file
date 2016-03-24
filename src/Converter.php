<?php

namespace DB2FILE;

/**
 * Retrieve data from database and generate/print JSON or XML file
 * @author Natanael Simões <natanael.simoes@ifro.edu.br>
 * @copyright (c) 2016, Natanael Simões
 * @license https://github.com/natanaelsimoes/db2file/blob/master/LICENSE
 * MIT License
 * @link https://github.com/natanaelsimoes/db2file GitHub Repository
 * @package DB2FILE
 * @version 1.0.0-beta
 */
class Converter
{

    const FIREBIRD = 0;
    const MYSQL = 1;
    const ORACLE = 2;
    const POSTGRES = 3;
    const SQLITE = 4;
    const SQLSERVER = 5;

    /**
     * PDO object to work with multiple databases
     * @var \PDO
     */
    private $pdo;

    /**
     * Database and XML encoding charset
     * @var string
     */
    private $charset;

    /**
     * Instantiates a new Converter object.
     * @param integer $driver Database driver. It values can be:
     * <ul>
     *  <li>DB2FILE\Converter::FIREBIRD</li>
     *  <li>DB2FILE\Converter::MYSQL</li>
     *  <li>DB2FILE\Converter::ORACLE</li>
     *  <li>DB2FILE\Converter::POSTGRES</li>
     *  <li>DB2FILE\Converter::SQLITE</li>
     *  <li>DB2FILE\Converter::SQLSERVER</li>
     * </ul>
     * @param string $dbname Database name or path.
     * @param string $host Domain name or IP of database host.
     * @param string $username Database user name.
     * @param string $password Database password.
     * @param string $charset Retrieves data encoded with this charset.
     * @param integer $port Port running database service.
     * @param array $options PDO array options (depends on driver). See more about it at <a target="_blank" href="http://php.net/manual/pt_BR/pdo.construct.php">official documentation</a>
     * @throws \Exception
     */
    public function __construct($driver, $dbname, $host = null, $username = null, $password = null, $charset = 'utf8', $port = null, $options = null)
    {
        switch ($driver) {
            case self::FIREBIRD:
                $port = (is_null($port)) ? 3050 : $port;
                $this->pdo = new \PDO("firebird:dbname=$host/$port:$dbname;charset=$charset", $username, $password);
                break;
            case self::MYSQL:
                $port = (is_null($port)) ? 3306 : $port;
                $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset;port=$port", $username, $password);
                break;
            case self::ORACLE:
                $port = (is_null($port)) ? 1521 : $port;
                $this->pdo = new \PDO("oci:dbname=//$host:$port/$dbname", $username, $password);
                break;
            case self::POSTGRES:
                $port = (is_null($port)) ? 5432 : $port;
                $this->pdo = new \PDO("pgsql:host=$host;dbname=$dbname;user=$username;password=$password; port={$this->configuracoes->porta}");
                break;
            case self::SQLITE:
                $this->pdo = new \PDO("sqlite:$dbname}");
                break;
            case self::SQLSERVER:
                $port = (is_null($port)) ? 1433 : $port;
                $this->pdo = new \PDO("sqlsrv:Server=$host,$port;Database=$dbname", $username, $password);
                break;
            default:
                throw new \Exception('Parameter $drive must be a valid DM2XML Database type.');
        }
        $this->charset = $charset;
    }

    /**
     * Returns the content of a JSON file with table rows.
     * @param string $tablename The table name.
     * @param integer $count Number of records to get.
     * @param integer $offset Offset to start counting.
     * @return string The JSON file content.
     */
    public function getJSONFromTable($tablename, $count = -1, $offset = 0)
    {
        $table = $this->getTable($tablename, $count, $offset);
        return $this->generateJSON($table);
    }

    /**
     * Returns the content of a JSON file with query resulting rows.
     * @param string $query A SQL query.
     * @return string The JSON file content.
     */
    public function getJSONFromQuery($query)
    {
        $table = $this->getQuery($query);
        return $this->generateJSON($table);
    }

    /**
     * Returns the content of a XML file with table rows encoding with the
     * database charset.
     * @param string $tablename The table name.
     * @param integer $count Number of records to get.
     * @param integer $offset Offset to start counting.
     * @param string $tableElement Represents the entire set of data.
     * @param string $rowElement Represents each row data set.
     * @return string The XML file content.
     */
    public function getXMLFromTable($tablename, $count = -1, $offset = 0, $tableElement = 'dataset', $rowElement = 'datarow')
    {
        $table = $this->getTable($tablename, $count, $offset);
        return $this->generateXML($table, $tableElement, $rowElement);
    }

    /**
     * Returns the content of a XML file with query resulting rows encoding with
     * the database charset.
     * @param string $query A SQL query.
     * @param string $tableElement Represents the entire set of data.
     * @param string $rowElement Represents each row data set.
     * @return string The XML file content.
     */
    public function getXMLFromQuery($query, $tableElement = 'dataset', $rowElement = 'datarow')
    {
        $table = $this->getQuery($query);
        return $this->generateXML($table, $tableElement, $rowElement);
    }

    /**
     * Returns an array with table rows.
     * @param string $tablename The table name.
     * @param integer $count Number of records to get.
     * @param integer $offset Offset to start counting.
     * @return array The resulting array.
     */
    public function getTable($tablename, $count = -1, $offset = 0)
    {
        $stmt = $this->pdo->query("SELECT * FROM $tablename");
        $table = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($count !== -1) {
            $table = array_slice($table, $offset, $count);
        }
        return $table;
    }

    /**
     * Returns an array with query resulting rows.
     * @param string $query A SQL query.
     * @return array The resulting array.
     */
    public function getQuery($query)
    {
        $stmt = $this->pdo->query($query);
        $table = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $table;
    }

    /**
     * Prints a JSON file with table rows. This should be the only content sent
     * to screen.
     * @param string $tablename The table name.
     * @param integer $count Number of records to get.
     * @param integer $offset Offset to start counting.
     */
    public function printJSONFromTable($tablename, $count = -1, $offset = 0)
    {
        header('Content-type: application/json');
        print_r($this->getJSONFromTable($tablename, $count, $offset));
    }

    /**
     * Prints a JSON file with query resulting rows. This should be the only
     * content sent to screen.
     * @param string $query A SQL query.
     */
    public function printJSONFromQuery($query)
    {
        header('Content-type: application/json');
        print_r($this->getJSONFromQuery($query));
    }

    /**
     * Prints a XML file with table rows encoding with the database
     * charset. This should be the only content sent to screen.
     * @param string $tablename The table name.
     * @param integer $count Number of records to get.
     * @param integer $offset Offset to start counting.
     * @param string $tableElement Represents the entire set of data.
     * @param string $rowElement Represents each row data set.
     */
    public function printXMLFromTable($tablename, $count = -1, $offset = 0, $tableElement = 'dataset', $rowElement = 'datarow')
    {
        header('Content-type: text/xml');
        print_r($this->getXMLFromTable($tablename, $count, $offset, $tableElement, $rowElement));
    }

    /**
     * Prints a XML file with query resulting rows encoding with the database
     * charset. This should be the only content sent to screen.
     * @param string $query A SQL query.
     * @param string $tableElement Represents the entire set of data.
     * @param string $rowElement Represents each row data set.
     */
    public function printXMLFromQuery($query, $tableElement = 'dataset', $rowElement = 'datarow')
    {
        header('Content-type: text/xml');
        print_r($this->getXMLFromQuery($query, $tableElement, $rowElement));
    }

    /**
     * Generates a JSON file with table/query rows.
     * @param array $table Result of table or query fetching.
     * @return string The JSON file.
     */
    private function generateJSON($table)
    {
        return json_encode($table);
    }

    /**
     * Generates a XML file with table/query rows encoding with the database
     * charset.
     * @param array $table Result of table or query fetching.
     * @param string $tableElement Represents the entire set of data.
     * @param string $rowElement Represents each row data set.
     * @return string The XML file.
     */
    private function generateXML($table, $tableElement, $rowElement)
    {
        $comment = sprintf('File generated by DB2XML, %s', date('M d Y'));
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', $this->charset);
        $xml->writeComment($comment);
        $xml->setIndent(true);
        $xml->startElement($tableElement);
        foreach ($table as $row) {
            $xml->startElement($rowElement);
            foreach ($row as $column => $value) {
                $xml->startElement($column);
                $repValue = str_replace('&', '&amp;', $value);
                $xml->writeRaw($repValue);
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
        return $xml->flush();
    }
}
