<!-- Financial Overview Page -->
<div class="card">
    <h3>Financial Overview</h3>
    <p>View your earnings and expenses.</p>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Earnings</th>
                <th>Expenses</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            <!-- Display financial data -->
            <?php
            // Example static data, fetch from database if needed
            $financial_data = [
                ['January', 1000, 500, 500],
                ['February', 1500, 800, 700],
                // more data...
            ];
            foreach ($financial_data as $data) {
                echo "<tr>
                        <td>{$data[0]}</td>
                        <td>{$data[1]}</td>
                        <td>{$data[2]}</td>
                        <td>{$data[3]}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
