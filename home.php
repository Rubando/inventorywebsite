<?php
session_start();
$search_product = "";
if (isset($_POST["search_product"])) {
    $search_product = trim($_POST["search_product"]);
}
$search_supplier = "";
if (isset($_POST["search_supplier"])) {
    $search_supplier = trim($_POST["search_supplier"]);
}


if (isset($_SESSION["user_id"])) {

    $mysqli = require __DIR__ . "/database.php";

    $sql = "SELECT * FROM accounts
            WHERE id = {$_SESSION["user_id"]}";

    $result = $mysqli->query($sql);

    $user = $result->fetch_assoc();
}




// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = "oblong123";

// Create a new MySQLi object for database connection
$mysqli = new mysqli($host, $username, $password);

// Check if the connection was successful
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// echo "Successfully connect to MySQL\n";

// Connection successful, performing database operations below
// Create a new database if it doesn't exist
$databaseName = "cp476";
$sql = "CREATE DATABASE IF NOT EXISTS $databaseName";
if ($mysqli->query($sql) === false) {
    echo "Error creating database: " . $mysqli->error;
    exit();
}
$conn = mysqli_connect($host, $username, $password, $databaseName);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Select the database
$mysqli->select_db($databaseName);
// SQL query to create the product table
$sql = "CREATE TABLE IF NOT EXISTS product (
    ProductID INT(11) NOT NULL,
    ProductName VARCHAR(255) NOT NULL,
    Description VARCHAR(255) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Quantity INT(11) NOT NULL,
    Status CHAR(1) NOT NULL,
    SupplierID INT(11) NOT NULL
)";


// Execute the query
if ($mysqli->query($sql) === true) {
    // echo "Table product create successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error;
}

// Select the database
$mysqli->select_db($databaseName);
// SQL query to create the supplier table
$sql = "CREATE TABLE IF NOT EXISTS supplier (
    SupplierID INT(11) NOT NULL,
    SupplierName VARCHAR(255)NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Phone VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL
)";

// Execute the query
if ($mysqli->query($sql) === true) {
    // echo "Table supplier create successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error;
}

// Importing data into mysql Product tables
// Open the .txt file
$filename = 'C:\cp476\ProductFile.txt'; //switch to the path that you store the ProductFile.txt
$file = fopen($filename, 'r');
// echo "Importing data\n";

// Prepare the SQL statement with placeholders
$sql = "INSERT INTO product (ProductID, ProductName, Description, Price, Quantity, Status, SupplierID)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = $mysqli->prepare($sql);

// Check if the statement preparation was successful
if ($stmt === false) {
    echo "Error preparing statement: " . $mysqli->error;
    exit();
}

// Process each line and insert into the database
while (($line = fgets($file)) !== false) {
    // Split the line into values (assuming comma-separated)
    $values = explode(',', $line);

    // Check if all required values are present
    if (count($values) < 7) {
        //echo "Line does not have enough data: $line\n";
        continue;
    }

    // Trim whitespace from each value
    $values = array_map('trim', $values);

    // Remove the '$' sign from the price and convert it to a float
    $price = (float) str_replace('$', '', $values[3]);

    // Check if a record with the same ProductID already exists in the table
    $existingProductID = $values[0];
    $checkSql = "SELECT * FROM product WHERE ProductID = ?";
    $checkStmt = $mysqli->prepare($checkSql);
    $checkStmt->bind_param("i", $existingProductID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    $duplicateFound = false;
    while ($row = $checkResult->fetch_assoc()) {
        // Compare other columns to check for duplicates
        if (
            $row['ProductName'] === $values[1] && $row['Description'] === $values[2] && $row['Price'] == $price &&
            $row['Quantity'] == $values[4] && $row['Status'] === $values[5] && $row['SupplierID'] == $values[6]
        ) {
            $duplicateFound = true;
            break;
        }
    }

    if ($duplicateFound) {
        // echo "Duplicate data found, skipping insertion for ProductID: $existingProductID\n";
        continue;
    }
    // Bind the values to the prepared statement
    $stmt->bind_param("issdisi", $values[0], $values[1], $values[2], $price, $values[4], $values[5], $values[6]);

    // Execute the statement
    if ($stmt->execute() === false) {
        echo "Error inserting data: " . $stmt->error;
    }
}
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id']; // Ensure the ID is an integer for security
    $query = "DELETE FROM product WHERE productID = $delete_id";
    
    if (mysqli_query($conn, $query)) {
        echo "Entry deleted successfully!";
    } else {
        echo "Error deleting entry: " . mysqli_error($conn);
    }
}

if (isset($_POST["add_product"])) {
    // Collect form data
    $ProductID = $mysqli->real_escape_string(trim($_POST["ProductID"]));
    $ProductName = $mysqli->real_escape_string(trim($_POST["ProductName"]));
    $Description = $mysqli->real_escape_string(trim($_POST["Description"]));
    $Price = $mysqli->real_escape_string(trim($_POST["Price"]));
    $Quantity = $mysqli->real_escape_string(trim($_POST["Quantity"]));
    $Status = $mysqli->real_escape_string(trim($_POST["Status"]));
    $SupplierID = $mysqli->real_escape_string(trim($_POST["SupplierID"]));
    $SupplierName = $mysqli->real_escape_string(trim($_POST["SupplierName"]));

    // SQL to insert new product
    $sql = "INSERT INTO product (ProductID, ProductName, Description, Price, Quantity, Status, SupplierID) 
    VALUES ('$ProductID', '$ProductName', '$Description', '$Price', '$Quantity', '$Status', '$SupplierID')";
;

    if ($mysqli->query($sql) === true) {
        echo "New product added successfully.";
    } else {
        echo "Error adding product: " . $mysqli->error;
    }
}
$stmt->close();
// echo "Finished importing data into Product Table\n";


// Importing data into mysql Supplier tables
// Open the .txt file
$filename = 'C:\cp476\SupplierFile.txt'; //switch to the path that you store the supplierfile.txt
$file = fopen($filename, 'r');
// echo "Importing data\n";

// Prepare the SQL statement with placeholders
$sql = "INSERT INTO supplier (SupplierID, SupplierName, Address, Phone, Email)
        VALUES (?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = $mysqli->prepare($sql);

// Check if the statement preparation was successful
if ($stmt === false) {
    echo "Error preparing statement: " . $mysqli->error;
    exit();
}


// Process each line and insert into the database
while (($line = fgets($file)) !== false) {
    // Split the line into values (assuming comma-separated)
    $values = explode(',', $line);

    // Trim whitespace from each value
    $values = array_map('trim', $values);

    // Check if the SupplierID already exists in the table
    $existingSupplierID = $values[0];
    $checkSql = "SELECT SupplierID FROM supplier WHERE SupplierID = ?";
    $checkStmt = $mysqli->prepare($checkSql);
    $checkStmt->bind_param("i", $existingSupplierID);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // echo "Duplicate data found, skipping insertion for SupplierID: $existingSupplierID\n";
        continue;
    }
    // Bind the values to the prepared statement
    $stmt->bind_param("issss", $values[0], $values[1], $values[2], $values[3], $values[4]);

    // Execute the statement
    if ($stmt->execute() === false) {
        echo "Error inserting data: " . $stmt->error;
    }
}

// Close the statement
$stmt->close();
// Display duplicate entry messages
if (!empty($duplicateEntries)) {
    echo "<p>Duplicate data found for the following SupplierIDs:</p>";
    echo "<ul>";
    foreach ($duplicateEntries as $supplierID) {
        echo "<li>$supplierID</li>";
    }
    echo "</ul>";
}



// echo "Finished importing data into Supplier Table\n";

// Create inventory table combine data from product and supplier
//require_once 'inventory_table.php';

// Close the file
fclose($file);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Home</title>
    
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://unpkg.com/mvp.css">
    <style>
        .table-container {
            display: none;
            /* Hide the table container by default */
            justify-content: space-around;
        }

        .table-container div {
            
            width: 100%;

        }

        .table-container div table {
            display: block;
            max-height: 500px;
            
            /* Set the max-height you want for your tables */
            overflow-y: auto;
            /* Enable vertical scrollbar */
            overflow-x: auto;
            /* Enable horizontal scrollbar, useful if your table has many columns */
        }
    </style>
    <script>
        function showTable(tableId) {
            var tableContainers = document.getElementsByClassName('table-container');
            for (var i = 0; i < tableContainers.length; i++) {
                if (tableContainers[i].id === tableId) {
                    tableContainers[i].style.display = 'block'; // Show the selected table
                } else {
                    tableContainers[i].style.display = 'none'; // Hide other tables
                }
            }
        }
    </script>

</head>


<body>

    <h1>Home</h1>
    

    <?php if (isset($user)) : ?>
        <p>Hello <?= htmlspecialchars($user["name"]) ?></p>
        <p><a href="logout.php">Log out</a></p>

        <form method="POST">
            <button type="button" onclick="showTable('productTable')">Show Product Table</button>
            <button type="button" onclick="showTable('supplierTable')">Show Supplier Table</button>
            <button type="button" onclick="showTable('inventoryTable')">Show Inventory Table</button>
        </form>


    <?php else : ?>
        <p><a href="login.php">Log in</a> or <a href="signup.html">sign up</a></p>
    <?php endif; ?>
    <h2>Add New Product</h2>
<form action="" method="POST">
    <label for="ProductID">Product ID:</label>
    <input type="number" name="ProductID" required><br><br>

    <label for="ProductName">Product Name:</label>
    <input type="text" name="ProductName" required><br><br>

    <label for="Description">Description:</label>
    <input type="text" name="Description" required><br><br>

    <label for="Price">Price:</label>
    <input type="number" name="Price" step="0.01" required><br><br>

    <label for="Quantity">Quantity:</label>
    <input type="number" name="Quantity" required><br><br>

    <label for="Status">Status (e.g. A for active, I for inactive):</label>
    <input type="text" name="Status" required><br><br>

    <label for="SupplierID">Supplier ID:</label>
    <input type="number" name="SupplierID" required><br><br>
    <label for="SupplierName">Supplier Name:</label>
    <input type="text" name="SupplierName" required><br><br>


    <input type="submit" name="add_product" value="Add Product">
</form>

    <form action="" method="post">
    <input type="text" name="search_product" placeholder="Search Product" required>
    <input type="submit" value="Search">
</form>
<form action="" method="post">
    <input type="text" name="search_supplier" placeholder="Search Supplier" required>
    <input type="submit" value="Search">
    

    <div class="table-container" id="productTable">
        <div>
            <h2>Product Table</h2>
            <table>
                <tr>
                    <th>ProductID</th>
                    <th>ProductName</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>SupplierID</th>
                </tr>
                <?php
                
                if ($search_product != "") {
                    $search_product = $mysqli->real_escape_string($search_product); // To prevent SQL injection
                    $result = $mysqli->query("SELECT * FROM product WHERE ProductName LIKE '%$search_product%'");
                } else {
                    $result = $mysqli->query("SELECT * FROM product");
                }
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['ProductID'] . "</td>";
                        echo "<td>" . $row['ProductName'] . "</td>";
                        echo "<td>" . $row['Description'] . "</td>";
                        echo "<td>" . $row['Price'] . "</td>";
                        echo "<td>" . $row['Quantity'] . "</td>";
                        echo "<td>" . $row['Status'] . "</td>";
                        echo "<td>" . $row['SupplierID'] . "</td>";
                        echo "<td><a href='?delete_id=" . $row['ProductID'] . "' onclick=\"return confirm('Are you sure you want to delete this product?')\">Delete</a></td>";

                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>


    <div class="table-container" id="supplierTable">
        <div>
            <h2>Supplier Table</h2>
            <table>
                <tr>
                    <th>SupplierID</th>
                    <th>SupplierName</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                </tr>
                <?php
                      if ($search_supplier != "") {
                        $search_supplier = $mysqli->real_escape_string($search_supplier); // To prevent SQL injection
                        $result = $mysqli->query("SELECT * FROM supplier WHERE SupplierName LIKE '%$search_supplier%'");
                    } else {
                        $result = $mysqli->query("SELECT * FROM supplier");
                    }
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['SupplierID'] . "</td>";
                        echo "<td>" . $row['SupplierName'] . "</td>";
                        echo "<td>" . $row['Address'] . "</td>";
                        echo "<td>" . $row['Phone'] . "</td>";
                        echo "<td>" . $row['Email'] . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>


    <div class="table-container" id="inventoryTable">
        <div>
            <h2>Inventory Table</h2>
            <table>
                <tr>
                    <th>ProductID</th>
                    <th>ProductName</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>SupplierName</th>
                </tr>
                <?php
                $result = $mysqli->query("SELECT pt.ProductID, pt.ProductName, pt.Quantity, pt.Price, pt.Status, st.SupplierName
                                      FROM product AS pt
                                      LEFT JOIN supplier AS st ON pt.SupplierID = st.SupplierID");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['ProductID'] . "</td>";
                        echo "<td>" . $row['ProductName'] . "</td>";
                        echo "<td>" . $row['Quantity'] . "</td>";
                        echo "<td>" . $row['Price'] . "</td>";
                        echo "<td>" . $row['Status'] . "</td>";
                        echo "<td>" . $row['SupplierName'] . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
    </div>

</body>

</html>

<?php
// Close the database connection here
$mysqli->close();
?>