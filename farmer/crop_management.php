<!-- Crop Management Page -->
<div class="card">
    <h3>Manage Crops</h3>
    <p>Here, you can add, update, or delete your crops/products.</p>
    <a href="add_crop.php" class="button">Add New Crop</a>
    <table>
        <thead>
            <tr>
                <th>Crop Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through crops from the database and display them here -->
            <?php
            $query = "SELECT * FROM crops WHERE farmer_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['crop_name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>{$row['price']}</td>
                        <td>
                            <a href='edit_crop.php?id={$row['id']}'>Edit</a> | 
                            <a href='delete_crop.php?id={$row['id']}'>Delete</a>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
