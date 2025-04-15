<?php
// Set how many results you want per page
$limit = 5; // Users per page

// Get the current page from the URL (default is page 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit; // Calculate the offset for the query

// Get the total number of users to calculate total pages
$totalQuery = "SELECT COUNT(*) as total FROM users";
$totalResult = $conn->query($totalQuery);
$totalUsers = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit); // Calculate total pages

// Get the users for the current page
$query = "SELECT * FROM users LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Output the users in a table
?>