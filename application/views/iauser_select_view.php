<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pilih AppID & Employee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center font-sans">

    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-2xl border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            ⚙️ Pilih App ID & Employee
        </h2>

        <div class="flex flex-col md:flex-row gap-4 items-center justify-center">
            <select name="appid" id="appid"
                class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih AppID --</option>
                <?php foreach ($iausers as $row): ?>
                    <option value="<?= htmlspecialchars($row['appid']); ?>"
                        <?= $row['appid'] === 'IA01M168064F20250505533' ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($row['appid']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="employee" id="employee"
                class="hidden w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Employee --</option>
            </select>

            <button id="btnTampilkan"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow-md transition">
                🔍 Tampilkan
            </button>
        </div>

        <div id="hasil" class="mt-8 hidden animate-fade-in">
            <div class="bg-gray-100 rounded-lg p-5 border border-gray-200 space-y-2">
                <div>📱 AppID: <strong id="showAppid" class="text-blue-700">-</strong></div>
                <div>👤 Employee ID: <strong id="showEmpid" class="text-blue-700">-</strong></div>
                <div>🧩 num_of_run_id: <strong id="numRun" class="text-green-600">-</strong></div>
                <div>🏷️ tbnumrun.name: <strong id="numRunName" class="text-purple-700">-</strong></div>
                <div>⏰ Start Time: <strong id="startTime" class="text-orange-600">-</strong></div>
                <div>🕓 End Time: <strong id="endTime" class="text-orange-600">-</strong></div>
            </div>
        </div>
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
        const startTimeSpan = document.getElementById('startTime');
        const endTimeSpan = document.getElementById('endTime');

        async function loadEmployees(appid) {
            selectEmployee.innerHTML = '<option value="">-- Pilih Employee --</option>';
            hasilDiv.classList.add('hidden');

            if (!appid) {
                selectEmployee.classList.add('hidden');
                return;
            }

            try {
                const res = await fetch(`/index.php/tbemployee/get_by_appid/${appid}`);
                const response = await res.json();
                const data = response.data || response;

                if (!data || data.length === 0) {
                    selectEmployee.classList.add('hidden');
                    return;
                }

                data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.employee_id;
                    option.textContent = emp.employee_full_name;
                    if (emp.employee_id == 22363) option.selected = true;
                    selectEmployee.appendChild(option);
                });

                selectEmployee.classList.remove('hidden');
            } catch (err) {
                console.error('Gagal memuat data employee:', err);
                selectEmployee.classList.add('hidden');
            }
        }

        async function getNumOfRun(appid, empid) {
            try {
                const res = await fetch(`/index.php/tbuserofrun/get_by_appid_and_empid/${appid}/${empid}`);
                const response = await res.json();

                if (response.status && response.data) {
                    const numOfRun = response.data.num_of_run_id;
                    localStorage.setItem('num_of_run_id', numOfRun);

                    await getNumRunName(numOfRun);
                    await getNumRunDeil(numOfRun);
                } else {
                    localStorage.removeItem('num_of_run_id');
                    localStorage.removeItem('tbnumrun_name');
                    localStorage.removeItem('tbnumrundeil_start_time');
                    localStorage.removeItem('tbnumrundeil_end_time');
                }
            } catch (err) {
                console.error('Gagal ambil num_of_run_id:', err);
            }
        }

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

        async function getNumRunDeil(numOfRun) {
            try {
                const res = await fetch(`/index.php/tbnumrundeil/get_by_num_run_id/${numOfRun}`);
                const response = await res.json();

                if (response.status && response.data) {
                    const {
                        start_time,
                        end_time
                    } = response.data;
                    localStorage.setItem('tbnumrundeil_start_time', start_time);
                    localStorage.setItem('tbnumrundeil_end_time', end_time);
                } else {
                    localStorage.removeItem('tbnumrundeil_start_time');
                    localStorage.removeItem('tbnumrundeil_end_time');
                }
            } catch (err) {
                console.error('Gagal ambil tbnumrundeil:', err);
                localStorage.removeItem('tbnumrundeil_start_time');
                localStorage.removeItem('tbnumrundeil_end_time');
            }
        }

        selectAppid.addEventListener('change', function() {
            const appid = this.value;
            localStorage.setItem('selected_appid', appid);
            showAppid.textContent = appid;
            hasilDiv.classList.add('hidden');
            loadEmployees(appid);
            localStorage.removeItem('selected_employee');
            localStorage.removeItem('num_of_run_id');
            localStorage.removeItem('tbnumrun_name');
            localStorage.removeItem('tbnumrundeil_start_time');
            localStorage.removeItem('tbnumrundeil_end_time');
        });

        selectEmployee.addEventListener('change', function() {
            const empid = this.value;
            const appid = selectAppid.value;
            hasilDiv.classList.add('hidden');

            if (!appid || !empid) return;

            localStorage.setItem('selected_employee', empid);
            showEmpid.textContent = empid;

            getNumOfRun(appid, empid);
        });

        btnTampilkan.addEventListener('click', function() {
            const appid = localStorage.getItem('selected_appid');
            const empid = localStorage.getItem('selected_employee');
            const numRun = localStorage.getItem('num_of_run_id');
            const numRunName = localStorage.getItem('tbnumrun_name');
            const startTime = localStorage.getItem('tbnumrundeil_start_time');
            const endTime = localStorage.getItem('tbnumrundeil_end_time');

            if (!appid || !empid) {
                alert("AppID dan Employee wajib dipilih!");
                return;
            }

            showAppid.textContent = appid;
            showEmpid.textContent = empid;
            numRunSpan.textContent = numRun || '(tidak ditemukan)';
            numRunNameSpan.textContent = numRunName || '(tidak ditemukan)';
            startTimeSpan.textContent = startTime || '(tidak ditemukan)';
            endTimeSpan.textContent = endTime || '(tidak ditemukan)';

            hasilDiv.classList.remove('hidden');
        });

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

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.5s ease-in-out;
        }
    </style>

</body>

</html>