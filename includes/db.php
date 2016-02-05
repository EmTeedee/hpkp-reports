<?php

class DB {
    protected $dbo = null;

    /**
     * constructor
     *
     * connect to database
     *
     * @param string $host     host to connect to
     * @param string $database database to use
     * @param string $user     user for database
     * @param string $password password for database
     *
     * @return bool true
     **/
    public function __construct($host, $database, $user, $password) {
        $pdostr = 'mysql:host=' . $host . ';dbname=' . $database;
        $conn = null;
        try {
            $conn = new PDO($pdostr, $user, $password);
            $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } catch (PDOException $e) {
            error_log(__FILE__ . ': ' . $e->getMessage());
            die('Database Error');
        }
        $this->dbo = $conn;
        return true;
    }

    /**
     * get connection
     *
     * @return PDO pdo object in use
     **/
    public function getConnection() {
        return $this->dbo;
    }

    /**
     * execute prepared statement
     *
     * prepare and execute a prepared statement
     * %identifier and :identifier in the sql statement are replaced with values provided
     * %identifier is literally replaced, :identifier is marked as a value placeholder
     *
     * @param string $sql            SQL statement to execute
     * @param array  $vars           associative array with values to fill in
     * @param bool   $buffer_results if results should be buffered (default: true)
     *
     * @return PDOStatement object with additional 'success' and 'foundRows' properties set
     **/
    public function preparedStatement($sql, $vars, $buffer_results = true) {
        // Split $vars into literals and values.
        $literals = array();
        $values = array();
        foreach ($vars as $p => $v) {
            if (strpos($p, '%') === 0) {
                $literals[$p] = $v;
            } elseif (strpos($p, ':') === 0) {
                $values[$p] = $v;
            }
        }

        $query_settings = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
        if ($buffer_results) {
            $query_settings[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        }

        // Do the query:
        $q = strtr($sql, $literals);
        $stmt = $this->dbo->prepare($q, $query_settings);
        if ($stmt === false) {
            if (DEBUG) {
                printf('Statement prep failed for: %s<br/>', $sql);
                printf("Reason: %s<br/>", implode(' ', $this->dbo->errorInfo()));
                printf("Query settings: %s<br/>", implode(',', $query_settings));
            }
            error_log(__FILE__ . ': ' . $this->dbo->errorInfo());
            die('Database Error');
        }

        $r = $stmt->execute($values);

        if ($r === false) {
            $stmt->foundRows = 0;
            $stmt->success = false;
        } else {
            $stmt->success = true;
            $rr = $this->dbo->query('SELECT FOUND_ROWS()');
            if ($rr) {
                $foundRows = $rr->fetchColumn();
                $stmt->foundRows = $foundRows;
            }
        }
        return $stmt;
    }

    /**
     * does record exist
     *
     * checks if a record with a certain field value exists
     *
     * @param string $table table to check
     * @param string $field field to check
     * @param string $value value to search for
     *
     * @return bool true if record exists, false otherwise or on error
     **/
    public function recordExists($table, $field, $value) {
        static $cache = array();
        $cache_key = sprintf('%s#%s#%s', $table, $field, $value);
        if (!isset($cache[$cache_key])) {
            $q = "SELECT COUNT(*) FROM `%table` WHERE %field = :value";
            $v = array('%table' => $table, '%field' => $field, ':value' => $value);
            $stmt = db()->preparedStatement($q, $v);
            if ($stmt->success) {
                $cache[$cache_key] = ($stmt->fetchColumn(0) > 0);
            } else {
                $cache[$cache_key] = false;
            }
            unset($stmt);
        }
        return $cache[$cache_key];
    }
}