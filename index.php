<?php
$servername = "172.21.163.162"; // Change if needed
$username = "root"; // Your MySQL username
$password = "Z0ng@311#315!"; // Your MySQL password
$dbname = "jxb_zongtrack"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get accounts that are not charged
$sql = "
select c.client_name, c.client_master_msisdn, c.client_additional_notes, lc.clog_charging_amount from log_charging lc
Inner join clients c on lc.client_id=c.client_id
where lc.clog_month = 'April-2025' AND lc.clog_charging_status = 'FAILED' AND c.client_type = 'client' AND c.client_status = 'active' 
AND NOT EXISTS (
  SELECT 1
  FROM log_charging lc2
  WHERE lc2.clog_charging_status = 'SUCCESS'
  AND lc2.clog_month = lc.clog_month
  AND lc2.client_id = lc.client_id
)";
$result = $conn->query($sql);

// Function to process the master number and display it in a table format
function processMasterNumber($serial, $client_name, $client_master_msisdn, $client_additional_notes,$clog_charging_amount) {
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; text-align: left;'>";
    echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>
            <th>Serial Number</th>
            <th>Client Name</th>
            <th>Master Number</th>
            <th>POC Name</th>
            <th>Amount</th>
          </tr>";
    echo "<tr>
            <td>$serial</td>
            <td>$client_name</td>
            <td>$client_master_msisdn</td>
            <td>$client_additional_notes</td>
            <td>$clog_charging_amount</td>            
          </tr>";
    echo "</table><br>";
}

// Initialize an empty array to store the result
$rows = [];
$serial = 1; // Initialize serial number
// Fetch all rows and store in the array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $client_name = $row["client_name"];  // Replace with actual column name
        $client_master_msisdn = $row["client_master_msisdn"];    // Replace with actual column name
        $client_additional_notes = $row["client_additional_notes"];            // Replace with actual column name
        $clog_charging_amount = $row["clog_charging_amount"];

        // Call the function
        processMasterNumber($serial, $client_name, $client_master_msisdn, $client_additional_notes,$clog_charging_amount);

        $serial++; // Increment serial number
    }
}

// Output result as JSON
header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);

// Close connection
$conn->close();
?>
