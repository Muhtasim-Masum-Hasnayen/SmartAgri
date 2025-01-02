<!-- Order Management Page -->
<div class="card">
    <h3>Order Management</h3>
    <p>View and manage your incoming orders.</p>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Crop Name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fetch orders from the database -->
            <?php
            $query = "SELECT * FROM orders WHERE farmer_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['order_id']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['crop_name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['status']}</td>
                        <td>
                            <a href='view_order.php?id={$row['order_id']}'>View</a> | 
                            <a href='update_order_status.php?id={$row['order_id']}'>Update Status</a>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
