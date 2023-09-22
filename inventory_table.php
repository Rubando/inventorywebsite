<?php

// Change accordingly to your machine
$host = "localhost";
$username = "root";
$password = "oblong123";
$database = "cp476";

// Create a new MySQLi object for database connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check if the connection was successful
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// Connection successful, performing database operations below
// echo "Successfully connected to MySQL\n";

// Prepare the SQL statement to create the "Inventory" table
$sql = "CREATE TABLE IF NOT EXISTS Inventory (
    ProductID INT(11),
    ProductName VARCHAR(255) NOT NULL,
    Quantity INT(11) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Status VARCHAR(255) NOT NULL,
    SupplierName VARCHAR(255) NOT NULL
)";

// Execute the query to create the "Inventory" table
if ($mysqli->query($sql) === FALSE) {
    echo "Error creating table: " . $mysqli->error;
    exit();
}

// Prepare the SQL statement with placeholders to insert data into the "Inventory" table
$sql = "INSERT INTO Inventory (ProductID, ProductName, Quantity, Price, Status, SupplierName)
        SELECT st.SupplierID, pt.ProductName, pt.Quantity, pt.Price, pt.Status, st.SupplierName
        FROM product AS pt
        LEFT JOIN supplier AS st ON pt.SupplierID = st.SupplierID
        WHERE NOT EXISTS (
            SELECT 1
            FROM Inventory AS inv
            WHERE inv.ProductID = st.SupplierID
        )";

// Prepare the statement
$stmt = $mysqli->prepare($sql);

// Check if the statement preparation was successful
if ($stmt === false) {
    echo "Error preparing statement: " . $mysqli->error;
    exit();
}

// Execute the statement
if ($stmt->execute() === false) {
    echo "Error inserting data: " . $stmt->error;
    exit();
}

// Close the statement
$stmt->close();

// echo "Inventory table created successfully and populated with data.\n";
