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

        select,
        button {
            padding: 6px 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #007bff;
            border: none;
            color: #fff;
            cursor: pointer;
            margin-left: 10px;
        }

        button:hover {
            background-color: #0056b3;
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
            <option value="<?= htmlspecialchars($row['appid']); ?>">
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

        selectAppid.addEventListener('change', async function() {
            const appid = this.value;
            tbody.innerHTML = '';
            if (!appid) {
                table.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`<?= base_url('tbemployee/get_by_appid/'); ?>${appid}`);
                const data = await res.json();

                if (data.length === 0) {
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
                console.error(err);
            }
        });
    </script>

</body>

</html>