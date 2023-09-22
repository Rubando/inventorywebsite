<?php
$host = "localhost";
$username = "root";
$password = "oblong123";

// Create a new mysqli connection without specifying the database
$mysqli = new mysqli($host, $username, $password);

// Check connection
if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS login_db";
if ($mysqli->query($sql) === FALSE) {


    echo "Error creating database: " . $mysqli->error;
}

// Select the database
$mysqli->select_db('login_db');

// sql to create table
$sql = "CREATE TABLE IF NOT EXISTS accounts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
)";


if ($mysqli->query($sql) === FALSE) {
    
    echo "Error creating table: " . $mysqli->error;
}

return $mysqli;
