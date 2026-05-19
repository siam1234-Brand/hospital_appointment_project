<?php
include_once(__DIR__ . "/../db/db_conn.php");

class BaseModel {
    protected $conn = null;

    function __construct() {
        $dbConnObj = new DBConnection();
        $this->conn = $dbConnObj->connect();
    }

    protected function makeStatement($sql, $types = "", $params = []) {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("SQL Error: " . $this->conn->error);
        }
        if ($types !== "") {
            $refs = [];
            $refs[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $refs[] = &$params[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);
        }
        return $stmt;
    }

    public function fetchAll($sql, $types = "", $params = []) {
        $stmt = $this->makeStatement($sql, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function fetchOne($sql, $types = "", $params = []) {
        $rows = $this->fetchAll($sql, $types, $params);
        if (count($rows) > 0) {
            return $rows[0];
        }
        return null;
    }

    public function execute($sql, $types = "", $params = []) {
        $stmt = $this->makeStatement($sql, $types, $params);
        return $stmt->execute();
    }

    public function getLastId() {
        return $this->conn->insert_id;
    }
}
?>
