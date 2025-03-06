<!-- Supplier Interaction Page -->
<div class="card">
    <h3>Supplier Interaction</h3>
    <p>Place orders with suppliers and manage your supplier relationships.</p>
    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Crop Supplied</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fetch supplier interactions -->
            <?php
            $query = "SELECT * FROM suppliers WHERE farmer_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['supplier_name']}</td>
                        <td>{$row['crop_supplied']}</td>
                        <td>{$row['price']}</td>
                        <td><a href='order_supplier.php?id={$row['id']}'>Order</a></td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
