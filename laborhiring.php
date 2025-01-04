<!-- Labor Hiring Page -->
<div class="card">
    <h3>Labor Hiring</h3>
    <p>Post job listings and hire laborers for your farm.</p>
    <table>
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Skills Required</th>
                <th>Salary</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Fetch labor listings -->
            <?php
            $query = "SELECT * FROM labor_jobs WHERE farmer_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['job_title']}</td>
                        <td>{$row['skills_required']}</td>
                        <td>{$row['salary']}</td>
                        <td>
                            <a href='hire_labor.php?id={$row['id']}'>Hire</a>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
