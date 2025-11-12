<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pilih AppID</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        select {
            padding: 6px 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: 60%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background: #f8f8f8;
            text-align: left;
        }
    </style>
</head>

<body>

    <h3>Pilih App ID</h3>

    <select name="appid" id="appid">
        <option value="">-- Pilih AppID --</option>
        <?php foreach ($iausers as $row): ?>
            <option value="<?= htmlspecialchars($row['appid']); ?>"
                <?= $row['appid'] === 'IA01M168064F20250505533' ? 'selected' : ''; ?>>
                <?= htmlspecialchars($row['appid']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <table id="employeeTable" style="display:none;">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Full Name</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <script>
        const selectAppid = document.getElementById('appid');
        const table = document.getElementById('employeeTable');
        const tbody = table.querySelector('tbody');

        async function loadEmployees(appid) {
            tbody.innerHTML = '';
            if (!appid) {
                table.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`http://localhost:8000/index.php/tbemployee/get_by_appid/${appid}`);
                const response = await res.json();
                const data = response.data || response;

                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2">Tidak ada data</td></tr>';
                } else {
                    data.forEach(emp => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${emp.employee_id}</td><td>${emp.employee_full_name}</td>`;
                        tbody.appendChild(tr);
                    });
                }

                table.style.display = 'table';
            } catch (err) {
                console.error('Gagal memuat data:', err);
                tbody.innerHTML = '<tr><td colspan="2">Terjadi kesalahan saat mengambil data</td></tr>';
                table.style.display = 'table';
            }
        }

        // Ganti data saat AppID dipilih manual
        selectAppid.addEventListener('change', function() {
            loadEmployees(this.value);
        });

        // 🔹 Panggil otomatis untuk default AppID
        document.addEventListener('DOMContentLoaded', function() {
            const defaultAppid = selectAppid.value || 'IA01M168064F20250505533';
            loadEmployees(defaultAppid);
        });
    </script>

</body>

</html>
