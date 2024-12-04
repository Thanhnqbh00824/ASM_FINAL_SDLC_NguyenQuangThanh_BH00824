<?php
include('dbconnect.php');

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch the category filter value from the URL (defaults to 'All' if not set)
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'All';

// Construct query based on selected category
$query = "SELECT p.*, c.name AS category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id";

if ($category_filter != 'All') {
    $query .= " WHERE c.name = :category";
}

$stmt = $conn->prepare($query);
if ($category_filter != 'All') {
    $stmt->execute([':category' => $category_filter]);
} else {
    $stmt->execute();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add product to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Initialize cart if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update product in the cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Redirect to the shopping page
    header('Location: buy.php');
    exit;
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id']; // Logged in user ID
    $total = 0;

    // Calculate the total cost of the cart
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = :product_id");
        $stmt->execute([':product_id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $total += $product['price'] * $quantity;
    }

    // Insert the order into the orders table
    $query = "INSERT INTO orders (created_at, user_id, total, status) 
              VALUES (NOW(), :user_id, :total, 'Pending')";
    $stmt = $conn->prepare($query);
    $stmt->execute([':user_id' => $user_id, ':total' => $total]);
    $order_id = $conn->lastInsertId();

    // Insert order details into the order_detail table
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = :product_id");
        $stmt->execute([':product_id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $query = "INSERT INTO order_detail (order_id, product_id, price, amount) 
                  VALUES (:order_id, :product_id, :price, :amount)";
        $stmt = $conn->prepare($query);
        $stmt->execute([ 
            ':order_id' => $order_id,
            ':product_id' => $product_id,
            ':price' => $product['price'],
            ':amount' => $quantity
        ]);

        // Reduce the quantity of the product in the inventory
        $query = "UPDATE products SET quantity = quantity - :quantity WHERE id = :product_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':quantity' => $quantity,
            ':product_id' => $product_id
        ]);
    }

    // Clear the cart after checkout
    unset($_SESSION['cart']);
    header('Location: buy.php?success=1');
    exit;
}

// Remove product from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity_to_remove = isset($_POST['quantity_to_remove']) ? (int)$_POST['quantity_to_remove'] : 1; // Default to 1 if not set

    // Kiểm tra số lượng sản phẩm trong giỏ
    if (isset($_SESSION['cart'][$product_id])) {
        // Giảm số lượng sản phẩm trong giỏ hàng thay vì xóa hẳn
        if ($_SESSION['cart'][$product_id] > $quantity_to_remove) {
            $_SESSION['cart'][$product_id] -= $quantity_to_remove;
        } else {
            unset($_SESSION['cart'][$product_id]); // Nếu số lượng còn lại nhỏ hơn hoặc bằng số muốn xóa thì xóa luôn sản phẩm
        }
    }

    // Redirect back to shopping cart page
    header('Location: buy.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Page</title>
    <link rel="stylesheet" href="buy.css">
    <style>
        /* Style for category filter */
        section ul {
            list-style-type: none; /* Remove default list bullets */
            padding: 0;
            display: flex; /* Display list items in a row */
            justify-content: center; /* Center the items horizontally */
        }

        section ul li {
            margin-right: 15px; /* Space between filter options */
        }

        section ul li a {
            display: block; /* Make the link fill the entire list item */
            padding: 10px 20px; /* Space inside the box */
            background-color: #007bff; /* Blue background */
            color: white; /* Text color */
            text-decoration: none; /* Remove underline */
            border-radius: 5px; /* Rounded corners */
            font-weight: bold;
            transition: background-color 0.3s ease; /* Smooth hover effect */
        }

        section ul li a:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Style for logout button */
        header {
            position: relative;
        }

        nav ul {
            position: absolute;
            top: 0;
            right: 0;
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            margin-left: 15px;
        }

        nav ul li a {
            display: block;
            padding: 10px 20px;
            background-color: red;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav ul li a:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to ThanhNQ FASHION</h1>
        <nav>
            <ul>
                <li><a href="logout.php">Logout</a></li> <!-- Add Logout -->
            </ul>
        </nav>
    </header>

    <main>
        <!-- Category Filter -->
        <section>
            <h2>Filter by Category</h2>
            <ul>
                <li><a href="buy.php?category=All">All</a></li>
                <li><a href="buy.php?category=T-Shirts">T-Shirts</a></li>
                <li><a href="buy.php?category=Jeans">Jeans</a></li>
                <li><a href="buy.php?category=Shoes">Shoes</a></li>
            </ul>
        </section>

        <!-- Product list -->
        <section>
            <h2>Product List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img 
                                    src="<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" 
                                    alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                    style="width: 100px; height: auto;">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'No Category'); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td>
                                <form method="POST" action="buy.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" min="1" max="<?php echo $product['quantity']; ?>" value="1" required>
                                    <button type="submit" name="add_to_cart">Add to Cart</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Success message when checkout is complete -->
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Your order has been placed successfully!</p>
        <?php endif; ?>

        <!-- Shopping Cart -->
        <section>
            <h2>Shopping Cart</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($_SESSION['cart'] as $product_id => $quantity):
                            $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
                            $stmt->execute([':id' => $product_id]);
                            $product = $stmt->fetch(PDO::FETCH_ASSOC);
                            $subtotal = $product['price'] * $quantity;
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td>
                                    <img 
                                        src="<?php echo htmlspecialchars($product['image'] ?? 'default.jpg'); ?>" 
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                        style="width: 100px; height: auto;">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td>$<?php echo number_format($subtotal, 2); ?></td>
                                <td>
                                    <form method="POST" action="buy.php">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="number" name="quantity_to_remove" min="1" max="<?php echo $quantity; ?>" value="1" required>
                                        <button type="submit" name="remove_from_cart">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4"><strong>Total:</strong></td>
                            <td>$<?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Checkout Form -->
                <form method="POST" action="buy.php">
                    <button type="submit" name="checkout">Checkout</button>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
