<?php
require_once 'models/Product.php';
require_once 'controllers/ProductController.php';
$productController = new ProductController();
// $act = isset($_GET['act']) ? $_GET['act'] : "/";
$act = isset($_GET['act']) ?? "/"; // action
//echo $act;
switch ($act) {
    case "add":
        $productController->addProduct();
        break;
    default:
        $productController->listProduct();
        break;
}
