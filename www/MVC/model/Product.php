<?php

require_once "ConnectDB.php";


class Product
{
    public $connect;

    public function __construct()
    {
        $this->connect = new ConnectDatabase();
    }

    public function getAllProduct()
    {
        $sql = "SELECT * FROM `products`";
        $this->connect->setQuery($sql);
        return $this->connect->loadData();
    }

    public function insertProduct($name, $price, $description, $quantity, $category = 1)
    {
        $sql = "INSERT INTO `products` (name, price, description, quantity) VALUES (?,?,?,?)";
        $this->connect->setQuery($sql);
        return $this->connect->loadData([$name, $price, $description, $quantity]);
    }
}