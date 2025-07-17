<?php
// Database connection variables
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

// Set default selected month
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : 'April-2025';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Uncharged Clients</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            margin: 40px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 10px;
        }
        select, input[type="submit"] {
            padding: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Select Month to View Uncharged Clients</h2>
    <form method="POST" action="">
        <label for="month">Month:</label>
        <select name="month" id="month">
            <option value="January-2025" <?= $selectedMonth == 'January-2025' ? 'selected' : '' ?>>January 2025</option>
            <option value="February-2025" <?= $selectedMonth == 'February-2025' ? 'selected' : '' ?>>February 2025</option>
            <option value="March-2025" <?= $selectedMonth == 'March-2025' ? 'selected' : '' ?>>March 2025</option>
            <option value="April-2025" <?= $selectedMonth == 'April-2025' ? 'selected' : '' ?>>April 2025</option>           
            <option value="May-2025" <?= $selectedMonth == 'May-2025' ? 'selected' : '' ?>>May 2025</option>          
            <option value="June-2025" <?= $selectedMonth == 'June-2025' ? 'selected' : '' ?>>June 2025</option>          
            <option value="July-2025" <?= $selectedMonth == 'July-2025' ? 'selected' : '' ?>>July 2025</option>
            <option value="August-2025" <?= $selectedMonth == 'August-2025' ? 'selected' : '' ?>>August 2025</option>
            <option value="September-2025" <?= $selectedMonth == 'September-2025' ? 'selected' : '' ?>>September 2025</option>
            
          
            <!-- Add more months as needed -->
        </select>
        <input type="submit" value="Get Data">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // SQL query using prepared statement
        $sql = "
        SELECT 
            c.client_name, 
            c.client_master_msisdn, 
            c.client_additional_notes, 
            lc.clog_charging_amount 
        FROM log_charging lc
        INNER JOIN clients c ON lc.client_id = c.client_id
        WHERE 
            lc.clog_month = ? 
            AND lc.clog_charging_status = 'FAILED' 
            AND c.client_type = 'client' 
            AND c.client_status = 'active' 
            AND NOT EXISTS (
                SELECT 1
                FROM log_charging lc2
                WHERE lc2.clog_charging_status = 'SUCCESS'
                AND lc2.clog_month = lc.clog_month
                AND lc2.client_id = lc.client_id
            )";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $selectedMonth);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<h3>Results for: <em>$selectedMonth</em></h3>";
        $serial = 1; // Initialize serial number
        if ($result->num_rows > 0) {
            $totalAmount = 0;            

            echo "<table>
                    <tr>
                        <th>Serial</th>
                        <th>Client Name</th>
                        <th>MSISDN</th>
                        <th>Notes</th>
                        <th>Charging Amount</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                $totalAmount += $row['clog_charging_amount'];
                echo "<tr>
                        <td>{$serial}</td>        
                        <td>{$row['client_name']}</td>
                        <td>{$row['client_master_msisdn']}</td>
                        <td>{$row['client_additional_notes']}</td>
                        <td>{$row['clog_charging_amount']}</td>
                    </tr>";
                    $serial++; // Increment serial number
            }
            echo "<tr>
            <td colspan='4'><strong>Total</strong></td>
            <td><strong>Rs. " . number_format($totalAmount) . "</strong></td>
            </tr>
            </table>";
            
            // echo "</table>";
        } else {
            echo "<p>No uncharged clients found for this month.</p>";
        }

        $stmt->close();
    }

    $conn->close();
    ?>
</div>
</body>
</html>
