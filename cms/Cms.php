<?php

class Cms
{

    public $conn;

    public function __construct($html = false)
    {

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "cms";

        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

    }

    public function getTextElement($id, $filename) {

        $sql = "SELECT b.* FROM elements a 
                LEFT JOIN elements b ON b.parent_id = a.element_id AND b.page_id = a.page_id AND b.tag = 'text'
                WHERE a.element_id = '$id' AND a.page_id = (SELECT id FROM pages WHERE name = '$filename' ORDER BY id DESC LIMIT 1)
        ";

        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {

            $row = $result->fetch_assoc();

            echo json_encode(['id' => $row['element_id'], 'text' =>  $row['text']]);

        } else {
            echo "false";
        }

    }

    public function createBlock($filename, $id, $image, $name) {

        $stmt = $this->conn->prepare("INSERT IGNORE INTO blocks (element_id, image, name) 
            SELECT elements.id, ?, ? FROM elements 
            LEFT JOIN pages ON name = ? 
            WHERE element_id = ? ORDER BY elements.id DESC LIMIT 1");
        $stmt->bind_param("ssss", $image, $name, $filename, $id);
        $stmt->execute();

        $stmt->close();

    }

    public function getBlocks() {

        $data = [];
        $sql = "SELECT * FROM blocks";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            echo json_encode($data);

        } else {
            echo "false";
        }

    }

}