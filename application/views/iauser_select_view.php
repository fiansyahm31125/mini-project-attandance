<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pilih AppID & Employee</title>
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
            margin-right: 10px;
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

    <h3>Pilih App ID dan Employee</h3>

    <div>
        <select name="appid" id="appid">
            <option value="">-- Pilih AppID --</option>
            <?php foreach ($iausers as $row): ?>
                <option value="<?= htmlspecialchars($row['appid']); ?>"
                    <?= $row['appid'] === 'IA01M168064F20250505533' ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($row['appid']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- 🔹 Dropdown untuk memilih employee -->
        <select name="employee" id="employee" style="display:none;">
            <option value="">-- Pilih Employee --</option>
        </select>
    </div>

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
        const selectEmployee = document.getElementById('employee');
        const table = document.getElementById('employeeTable');
        const tbody = table.querySelector('tbody');

        async function loadEmployees(appid) {
            tbody.innerHTML = '';
            selectEmployee.innerHTML = '<option value="">-- Pilih Employee --</option>';

            if (!appid) {
                table.style.display = 'none';
                selectEmployee.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`http://localhost:8000/index.php/tbemployee/get_by_appid/${appid}`);
                const response = await res.json();
                const data = response.data || response;

                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2">Tidak ada data</td></tr>';
                    table.style.display = 'table';
                    selectEmployee.style.display = 'none';
                    return;
                }

                // 🔹 Isi tabel
                data.forEach(emp => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td>${emp.employee_id}</td><td>${emp.employee_full_name}</td>`;
                    tbody.appendChild(tr);
                });

                // 🔹 Isi dropdown employee
                data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.employee_id; // value = employee_id
                    option.textContent = emp.employee_full_name; // tampilkan full name
                    selectEmployee.appendChild(option);
                });

                selectEmployee.style.display = 'inline-block';
                table.style.display = 'table';
            } catch (err) {
                console.error('Gagal memuat data:', err);
                tbody.innerHTML = '<tr><td colspan="2">Terjadi kesalahan saat mengambil data</td></tr>';
                table.style.display = 'table';
                selectEmployee.style.display = 'none';
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
