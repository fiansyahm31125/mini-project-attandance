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
        }

        #numRun {
            color: #27ae60;
        }

        #numRunName {
            color: #8e44ad;
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
        <div>tbnumrun.name: <strong id="numRunName">-</strong></div>
    </div>

    <script>
        const selectAppid = document.getElementById('appid');
        const selectEmployee = document.getElementById('employee');
        const btnTampilkan = document.getElementById('btnTampilkan');
        const hasilDiv = document.getElementById('hasil');
        const showAppid = document.getElementById('showAppid');
        const showEmpid = document.getElementById('showEmpid');
        const numRunSpan = document.getElementById('numRun');
        const numRunNameSpan = document.getElementById('numRunName');

        async function loadEmployees(appid) {
            selectEmployee.innerHTML = '<option value="">-- Pilih Employee --</option>';
            hasilDiv.style.display = 'none'; // sembunyikan hasil

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
                    if (emp.employee_id == 22363) option.selected = true;
                    selectEmployee.appendChild(option);
                });

                selectEmployee.style.display = 'inline-block';
            } catch (err) {
                console.error('Gagal memuat data employee:', err);
                selectEmployee.style.display = 'none';
            }
        }

        // 🔹 Ambil num_of_run_id & tbnumrun.name
        async function getNumOfRun(appid, empid) {
            try {
                const res = await fetch(`/index.php/tbuserofrun/get_by_appid_and_empid/${appid}/${empid}`);
                const response = await res.json();

                if (response.status && response.data) {
                    const numOfRun = response.data.num_of_run_id;
                    localStorage.setItem('num_of_run_id', numOfRun);

                    // Ambil nama dari tbnumrun
                    await getNumRunName(numOfRun);
                } else {
                    localStorage.removeItem('num_of_run_id');
                    localStorage.removeItem('tbnumrun_name');
                }
            } catch (err) {
                console.error('Gagal ambil num_of_run_id:', err);
            }
        }

        // 🔹 Ambil name dari tbnumrun berdasarkan id
        async function getNumRunName(numOfRun) {
            try {
                const res = await fetch(`/index.php/tbnumrun/get_name_by_id/${numOfRun}`);
                const response = await res.json();

                if (response.status && response.data) {
                    localStorage.setItem('tbnumrun_name', response.data.name);
                } else {
                    localStorage.removeItem('tbnumrun_name');
                }
            } catch (err) {
                console.error('Gagal ambil nama tbnumrun:', err);
                localStorage.removeItem('tbnumrun_name');
            }
        }

        // 🔹 Event: Ganti AppID
        selectAppid.addEventListener('change', function() {
            const appid = this.value;
            localStorage.setItem('selected_appid', appid);
            showAppid.textContent = appid;
            hasilDiv.style.display = 'none';
            loadEmployees(appid);
            localStorage.removeItem('selected_employee');
            localStorage.removeItem('num_of_run_id');
            localStorage.removeItem('tbnumrun_name');
        });

        // 🔹 Event: Ganti Employee
        selectEmployee.addEventListener('change', function() {
            const empid = this.value;
            const appid = selectAppid.value;
            hasilDiv.style.display = 'none'; // sembunyikan hasil sementara

            if (!appid || !empid) return;

            localStorage.setItem('selected_employee', empid);
            showEmpid.textContent = empid;

            getNumOfRun(appid, empid); // langsung ambil num_of_run_id & tbnumrun.name
        });

        // 🔹 Tombol tampilkan
        btnTampilkan.addEventListener('click', function() {
            const appid = localStorage.getItem('selected_appid');
            const empid = localStorage.getItem('selected_employee');
            const numRun = localStorage.getItem('num_of_run_id');
            const numRunName = localStorage.getItem('tbnumrun_name');

            if (!appid || !empid) {
                alert("AppID dan Employee wajib dipilih!");
                return;
            }

            showAppid.textContent = appid;
            showEmpid.textContent = empid;
            numRunSpan.textContent = numRun || '(tidak ditemukan)';
            numRunNameSpan.textContent = numRunName || '(tidak ditemukan)';

            hasilDiv.style.display = 'block';
        });

        // 🔹 Saat halaman pertama kali load
        document.addEventListener('DOMContentLoaded', function() {
            const defaultAppid = 'IA01M168064F20250505533';
            const defaultEmpid = '22363';

            selectAppid.value = defaultAppid;
            localStorage.setItem('selected_appid', defaultAppid);
            showAppid.textContent = defaultAppid;

            loadEmployees(defaultAppid).then(() => {
                selectEmployee.value = defaultEmpid;
                localStorage.setItem('selected_employee', defaultEmpid);
                showEmpid.textContent = defaultEmpid;
                getNumOfRun(defaultAppid, defaultEmpid);
            });
        });
    </script>

</body>

</html>