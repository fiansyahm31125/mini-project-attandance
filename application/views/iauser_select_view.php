<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pilih AppID, Employee & Date</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center font-sans">

    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-2xl border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            ⚙️ Pilih App ID, Employee & Date
        </h2>

        <div class="flex flex-col md:flex-row gap-4 items-center justify-center flex-wrap">
            <!-- AppID -->
            <select name="appid" id="appid"
                class="w-full md:w-[30%] px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih AppID --</option>
                <?php foreach ($iausers as $row): ?>
                    <option value="<?= htmlspecialchars($row['appid']); ?>"
                        <?= $row['appid'] === 'IA01M168064F20250505533' ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($row['appid']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Employee -->
            <select name="employee" id="employee"
                class="hidden w-full md:w-[30%] px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                <option value="">-- Pilih Employee --</option>
            </select>

            <!-- Pilih Tanggal -->
            <input type="date" id="datePicker"
                class="w-full md:w-[30%] px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition"
                value="<?= date('Y-m-d'); ?>">

            <!-- Tombol -->
            <button id="btnTampilkan"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow-md transition w-full md:w-auto">
                🔍 Tampilkan
            </button>
        </div>

        <!-- HASIL -->
        <div id="hasil" class="mt-8 hidden animate-fade-in">
            <div class="bg-gray-100 rounded-lg p-5 border border-gray-200 space-y-2">
                <div>📱 AppID: <strong id="showAppid" class="text-blue-700">-</strong></div>
                <div>👤 Employee ID: <strong id="showEmpid" class="text-blue-700">-</strong></div>
                <div>📅 Date: <strong id="showDate" class="text-indigo-700">-</strong></div>
                <hr class="my-3 border-gray-300">

                <div style="display: none;">
                    <div>🧩 schclass_id: <strong id="schclassId" class="text-green-600">-</strong></div>
                    <div>🧩 num_of_run_id: <strong id="numRun" class="text-green-600">-</strong></div>
                    <div>🧩 tbusertempsch: <strong id="tbusertempschId" class="text-green-600">-</strong></div>
                </div>

                <div>🏷️ Schedule Type: <strong id="numRunName" class="text-purple-700">-</strong></div>
                <div>⏰ Start Time: <strong id="startTime" class="text-orange-600">-</strong></div>
                <div>🕓 End Time: <strong id="endTime" class="text-orange-600">-</strong></div>
                <div>✅ Check In: <strong id="checkIn" class="text-green-700">-</strong></div>
                <div>🚪 Check Out: <strong id="checkOut" class="text-red-700">-</strong></div>
                <hr class="my-3 border-gray-300">
            </div>
        </div>
    </div>

    <script>
        const selectAppid = document.getElementById('appid');
        const selectEmployee = document.getElementById('employee');
        const datePicker = document.getElementById('datePicker');
        const btnTampilkan = document.getElementById('btnTampilkan');
        const hasilDiv = document.getElementById('hasil');
        const showAppid = document.getElementById('showAppid');
        const showEmpid = document.getElementById('showEmpid');
        const showDate = document.getElementById('showDate');

        const numRunSpan = document.getElementById('numRun');
        const numRunNameSpan = document.getElementById('numRunName');
        const startTimeSpan = document.getElementById('startTime');
        const endTimeSpan = document.getElementById('endTime');
        const checkInSpan = document.getElementById('checkIn');
        const checkOutSpan = document.getElementById('checkOut');

        const schclassIdSpan = document.getElementById('schclassId');
        const tbusertempschIdSpan = document.getElementById('tbusertempschId');

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
                    option.selected = true;
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

                    localStorage.removeItem('schclass_id');

                    const selectedDate = localStorage.getItem('selected_date') || new Date().toISOString().split('T')[0];
                    await getNumRunName(numOfRun, selectedDate);
                    await getNumRunDeil(numOfRun);
                    return true;
                } else {
                    localStorage.removeItem('num_of_run_id');
                    localStorage.removeItem('tbnumrun_name');
                    localStorage.removeItem('tbnumrundeil_start_time');
                    localStorage.removeItem('tbnumrundeil_end_time');
                    return false;
                }
            } catch (err) {
                console.error('Gagal ambil num_of_run_id:', err);
            }
        }

        async function getNumRunName(numOfRun, date) {
            try {
                const res = await fetch(`/index.php/tbnumrun/get_name_by_id/${numOfRun}/${date}`);
                const response = await res.json();

                if (response.status && response.data) {
                    localStorage.setItem('tbnumrun_name', "Scheduled(" + response.data.name + ")");
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
            }
        }

        async function getCheckInOut(appid, empid, date) {
            try {
                const urltmp = `/index.php/tbcheckinout_mobile/get_by_empid_and_date/${empid}/${date}`;

                const res = await fetch(urltmp);
                const response = await res.json();

                if (response.status && response.data) {
                    localStorage.setItem('check_in_time', response.data.first_checkin || '-');
                    localStorage.setItem('check_out_time', response.data.last_checkout || '-');
                } else {
                    localStorage.removeItem('check_in_time');
                    localStorage.removeItem('check_out_time');
                }
            } catch (err) {
                console.error('Gagal ambil data absen:', err);
                localStorage.removeItem('check_in_time');
                localStorage.removeItem('check_out_time');
            }
        }


        async function getSchclassId(appid, empid) {
            try {
                const res = await fetch(`/index.php/tbuserusedclasses/get_by_appid_and_empid/${appid}/${empid}`);
                const response = await res.json();

                if (response.status && response.data) {
                    const schclassId = response.data.schclass_id;

                    localStorage.removeItem('num_of_run_id');
                    localStorage.removeItem('tbnumrun_name');
                    localStorage.removeItem('tbnumrundeil_start_time');
                    localStorage.removeItem('tbnumrundeil_end_time');

                    localStorage.setItem('schclass_id', schclassId);
                    const selectedDate = localStorage.getItem('selected_date') || new Date().toISOString().split('T')[0];
                    await getSchclassName(schclassId, selectedDate);
                    return true;

                } else {
                    localStorage.removeItem('schclass_id');
                    return false;
                }
            } catch (err) {
                console.error('Gagal ambil schclass_id:', err);
            }
        }


        async function getSchclassName(schcClassid, date, status = "Automatic(") {
            try {
                const res = await fetch(`/index.php/tbschclass/get_name_by_id/${schcClassid}/${date}`);
                const response = await res.json();

                if (response.status && response.data) {
                    localStorage.setItem('tbnumrun_name', status + response.data.name + ")");
                    localStorage.setItem('tbnumrundeil_start_time', response.data.start_time);
                    localStorage.setItem('tbnumrundeil_end_time', response.data.end_time);
                } else {
                    localStorage.removeItem('tbnumrun_name');
                    localStorage.removeItem('tbnumrundeil_start_time');
                    localStorage.removeItem('tbnumrundeil_end_time');
                }
            } catch (err) {
                console.error('Gagal ambil nama tbnumrun:', err);
                localStorage.removeItem('tbnumrun_name');
            }
        }

        async function getTbusertempschId(appid, empid) {
            try {
                const res = await fetch(`/index.php/tbusertempsch/get_by_appid_and_empid/${appid}/${empid}`);
                const response = await res.json();

                if (response.status && response.data) {
                    const tbusertempschId = response.data.id;
                    const schclassId = response.data.schclass_id;

                    localStorage.removeItem('num_of_run_id');
                    localStorage.removeItem('tbnumrun_name');
                    localStorage.removeItem('tbnumrundeil_start_time');
                    localStorage.removeItem('tbnumrundeil_end_time');

                    localStorage.setItem('tbusertempsch_id', tbusertempschId);
                    localStorage.setItem('schclass_id', schclassId);

                    const selectedDate = localStorage.getItem('selected_date') || new Date().toISOString().split('T')[0];
                    await getSchclassName(schclassId, selectedDate, "Temporary(")
                    return true;

                } else {
                    localStorage.removeItem('tbusertempsch_id');
                    return false;
                }
            } catch (err) {
                console.error('Gagal ambil tbusertempsch_id:', err);
            }
        }

        // Event listeners
        selectAppid.addEventListener('change', function() {
            const appid = this.value;
            localStorage.setItem('selected_appid', appid);
            showAppid.textContent = appid;
            hasilDiv.classList.add('hidden');
            loadEmployees(appid);
            localStorage.removeItem('selected_employee');
        });

        selectEmployee.addEventListener('change', async function() {
            const empid = this.value;
            const appid = selectAppid.value;
            if (!appid || !empid) return;
            localStorage.setItem('selected_employee', empid);
            try {
                const tmpstatus1 = await getTbusertempschId(appid, empid); // tunggu hasil async
                if (tmpstatus1 === false) {
                    const tmpstatus2 = await getSchclassId(appid, empid); // tunggu hasil async
                    if (tmpstatus2 === false) {
                        getNumOfRun(appid, empid);
                    }
                }
            } catch (error) {
                console.error('Terjadi kesalahan:', error);
            }

        });

        datePicker.addEventListener('change', function() {
            const selectedDate = this.value;
            localStorage.setItem('selected_date', selectedDate);
        });

        btnTampilkan.addEventListener('click', async function() {
            const appid = localStorage.getItem('selected_appid');
            const empid = localStorage.getItem('selected_employee');
            const date = localStorage.getItem('selected_date') || new Date().toISOString().split('T')[0];

            const numRun = localStorage.getItem('num_of_run_id');
            const numRunName = localStorage.getItem('tbnumrun_name');
            const startTime = localStorage.getItem('tbnumrundeil_start_time');
            const endTime = localStorage.getItem('tbnumrundeil_end_time');

            const schclassId = localStorage.getItem('schclass_id');
            const tbusertempschId = localStorage.getItem('tbusertempsch_id');

            if (!appid || !empid) {
                alert("AppID dan Employee wajib dipilih!");
                return;
            }

            // Ambil data check in/out sebelum tampil
            await getCheckInOut(appid, empid, date);

            const checkIn = localStorage.getItem('check_in_time');
            const checkOut = localStorage.getItem('check_out_time');

            showAppid.textContent = appid;
            showEmpid.textContent = empid;
            showDate.textContent = date;


            numRunSpan.textContent = numRun || '(tidak ditemukan)';

            numRunNameSpan.textContent = numRunName || '(tidak ditemukan)';
            startTimeSpan.textContent = startTime || '(tidak ditemukan)';
            endTimeSpan.textContent = endTime || '(tidak ditemukan)';
            checkInSpan.textContent = checkIn || '(tidak ditemukan)';
            checkOutSpan.textContent = checkOut || '(tidak ditemukan)';

            schclassIdSpan.textContent = schclassId || '(tidak ditemukan)';

            tbusertempschIdSpan.textContent = tbusertempschId || '(tidak ditemukan)';

            hasilDiv.classList.remove('hidden');
        });

        document.addEventListener('DOMContentLoaded', async function() {
            // const defaultAppid = 'IA01M368F20210831677';
            // const defaultEmpid = '15873';
            const defaultAppid = 'IA01M168064F20250505533';
            const defaultEmpid = '22363';
            const today = new Date().toISOString().split('T')[0];

            selectAppid.value = defaultAppid;
            datePicker.value = today;
            localStorage.setItem('selected_appid', defaultAppid);
            localStorage.setItem('selected_date', today);

            loadEmployees(defaultAppid).then(async () => {
                selectEmployee.value = defaultEmpid;
                localStorage.setItem('selected_employee', defaultEmpid);
                try {
                    const tmpstatus1 = await getTbusertempschId(appid, empid); // tunggu hasil async
                    if (tmpstatus1 === false) {
                        const tmpstatus2 = await getSchclassId(appid, empid); // tunggu hasil async
                        if (tmpstatus2 === false) {
                            getNumOfRun(appid, empid);
                        }
                    }
                } catch (error) {
                    console.error('Terjadi kesalahan:', error);
                }
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