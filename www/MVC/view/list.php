<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Danh sách sản phẩm</title>
</head>

<body class="container">
    <h1>Dashboard</h1>
    <a class="btn" type="button" href="?act=add"><button>Thêm</button></a>
    <!--Thêm không phải truyền theo id-->
    <table class="table" border="1">
        <thead>
            <tr>
                <td>ID</td>
                <td>NAME</td>
                <td>PRICE</td>
                <td>DECRIPTION</td>
                <td>QUANTITY</td>
                <td>ACTION</td>
            </tr>
        </thead>

        <tbody>
            <?php
            echo "<pre>";
            print_r($listProduct);
            foreach ($listProduct as $key => $product) {

            ?>
            <tr>
                <td><?= $key ?></td>
                <td><?= $product->name ?></td>
                <td><?= $product->price ?></td>
                <td><?= $product->description ?></td>
                <td><?= $product->quantity ?></td>
                <td>
                    <button>Edit</button>
                    <button>Delete</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>

    </table>
</body>
<script>
function confirmDelete(delUrl) {
    if (confirm("Bạn có chắc chắn muốn xóa")) {
        document.location = delUrl;
    }
}
</script>

</html>