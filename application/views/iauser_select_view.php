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

        button {
            padding: 6px 12px;
            font-size: 16px;
            border-radius: 6px;
            border: none;
            background: #3498db;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        #hasil {
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
            display: none;
            /* 🔹 hasil disembunyikan dulu */
        }

        #numRun {
            color: #27ae60;
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

        <select name="employee" id="employee" style="display:none;">
            <option value="">-- Pilih Employee --</option>
        </select>

        <button id="btnTampilkan">Tampilkan</button>
    </div>

    <div id="hasil">
        <div>AppID: <strong id="showAppid">-</strong></div>
        <div>Employee ID: <strong id="showEmpid">-</strong></div>
        <div>num_of_run_id: <strong id="numRun">-</strong></div>
    </div>

    <script>
        const selectAppid = document.getElementById('appid');
        const selectEmployee = document.getElementById('employee');
        const btnTampilkan = document.getElementById('btnTampilkan');
        const hasilDiv = document.getElementById('hasil');
        const showAppid = document.getElementById('showAppid');
        const showEmpid = document.getElementById('showEmpid');
        const numRunSpan = document.getElementById('numRun');

        async function loadEmployees(appid, selectedEmpid = null) {
            selectEmployee.innerHTML = '<option value="">-- Pilih Employee --</option>';

            if (!appid) {
                selectEmployee.style.display = 'none';
                return;
            }

            try {
                const res = await fetch(`/index.php/tbemployee/get_by_appid/${appid}`);
                const response = await res.json();
                const data = response.data || response;

                if (!data || data.length === 0) {
                    selectEmployee.style.display = 'none';
                    return;
                }

                data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.employee_id;
                    option.textContent = emp.employee_full_name;

                    if (selectedEmpid && emp.employee_id == selectedEmpid) {
                        option.selected = true;
                    }
                    selectEmployee.appendChild(option);
                });

                selectEmployee.style.display = 'inline-block';
            } catch (err) {
                console.error('Gagal memuat data employee:', err);
                selectEmployee.style.display = 'none';
            }
        }

        async function getNumOfRun(appid, empid) {
            try {
                const res = await fetch(`/index.php/tbuserofrun/get_by_appid_and_empid/${appid}/${empid}`);
                const response = await res.json();

                if (response.status && response.data) {
                    const numOfRun = response.data.num_of_run_id;
                    localStorage.setItem('num_of_run_id', numOfRun);
                    numRunSpan.textContent = numOfRun;
                } else {
                    numRunSpan.textContent = '(tidak ditemukan)';
                    localStorage.removeItem('num_of_run_id');
                }
            } catch (err) {
                console.error('Gagal ambil num_of_run_id:', err);
                numRunSpan.textContent = '(error koneksi)';
            }
        }

        // 🔹 Ganti AppID → reset hasil & muat employee
        selectAppid.addEventListener('change', function() {
            const appid = this.value;
            hasilDiv.style.display = 'none'; // sembunyikan hasil
            numRunSpan.textContent = '-';
            localStorage.removeItem('num_of_run_id');
            localStorage.setItem('selected_appid', appid);
            loadEmployees(appid);
        });

        // 🔹 Ganti Employee → sembunyikan hasil & simpan ke localStorage
        selectEmployee.addEventListener('change', function() {
            const empid = this.value;
            hasilDiv.style.display = 'none'; // sembunyikan hasil
            localStorage.setItem('selected_employee', empid);
        });

        // 🔹 Klik tombol tampilkan → ambil data dan tampilkan hasil
        btnTampilkan.addEventListener('click', async function() {
            const appid = selectAppid.value;
            const empid = selectEmployee.value;

            if (!appid || !empid) {
                alert("AppID dan Employee wajib dipilih!");
                return;
            }

            showAppid.textContent = appid;
            showEmpid.textContent = empid;
            hasilDiv.style.display = 'block';

            await getNumOfRun(appid, empid);
        });

        // 🔹 Saat halaman pertama kali dibuka
        document.addEventListener('DOMContentLoaded', function() {
            const defaultAppid = 'IA01M168064F20250505533';
            const defaultEmpid = '22363';

            selectAppid.value = defaultAppid;
            showAppid.textContent = defaultAppid;
            loadEmployees(defaultAppid, defaultEmpid);
        });
    </script>

</body>

</html>