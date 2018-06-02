<?php

class Database extends PDO {
    public function __construct ($dsn, $username = null, $password = null, $driver_options = null) {
        parent::__construct($dsn, $username, $password, $driver_options);
    }

    public function query ($sql, $data = []) {
        $r = $this->prepare($sql);
        $r->execute();
        return $r;
    }

    public function prepare ($sql, $options = []) {
        $r = parent::prepare($sql, $options);
        if (!$r) {
            Tools::println('Error: ' . implode(', ', $this->errorInfo()), FALSE, [], 'red');
        }
        return $r;
    }

    public function fast_prepare ($table, $fields) {
        return $this->prepare('INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', self::prependColon($fields)) . ')');
    }

    public static function prependColon ($arr) {
        return array_map(function ($v) {
            return ':' . $v;
        }, $arr);
    }

    public function addColumns ($table, $data) {
        foreach ($data as $col => $clause) {
            if ($this->query('ALTER TABLE ' . $table . ' ADD COLUMN ' . $col . ' ' . $clause))
                Tools::println('Created column %s', FALSE, [$col]);
        }
        return $this;
    }

    public function insertData ($table, $data, $update = FALSE) {
        $row_size = count($data);
        if ($row_size === 0)
            return FALSE;

        $keys = [];
        $flattened_data = array_reduce($data, function ($c, $v) use (&$keys) {
            if (!$keys) {
                $keys = array_map(function ($v) {
                    return '`' . $v . '`';
                }, array_keys($v));
            }
            return array_merge($c, array_values($v));
        }, []);

        $trail = $update ? ' ON DUPLICATE KEY UPDATE ' . implode(', ', array_map(function ($v) {
            return $v . ' = VALUES(' . $v . ')';
        }, $keys)) : '';

        $col_size       = count($flattened_data) / $row_size;
        $question_marks = ltrim(str_repeat(PHP_EOL . ',(' . ltrim(str_repeat(',?', $col_size), ',') . ')', $row_size), ',' . PHP_EOL);
        $stmt           = $this->prepare(sprintf('INSERT INTO %s (%s) VALUES %s%s', $table, implode(', ', $keys), $question_marks, $trail));

        return $stmt->execute($flattened_data);
    }

    public function resetTables ($tables) {
        foreach ($tables as $t) {
            $this->query('DELETE FROM ' . $t);
            $this->query('ALTER TABLE ' . $t . ' AUTO_INCREMENT = 1');
        }
    }

}
