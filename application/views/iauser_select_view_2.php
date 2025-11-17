<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance List</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .page-title {
            font-weight: 600;
            color: #2c3e50;
        }

        .filter-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .badge-absen {
            background: #dc3545;
            color: white;
        }

        .badge-hadir {
            background: #28a745;
            color: white;
        }

        .text-absen {
            color: #dc3545;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4 px-4">
        <h1 class="page-title mb-4">Attendance List</h1>

        <!-- Filter Card -->
        <div class="filter-card mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted mb-1">Date Range</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                        <input type="text" class="form-control" id="filterDateRange" placeholder="Pilih rentang tanggal">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small text-muted mb-1">AppID</label>
                    <select class="form-select select2" id="filterAppID">
                        <option value="">-- Semua AppID --</option>
                        <?php foreach ($iausers as $row): ?>
                            <option value="<?= htmlspecialchars($row['appid']); ?>"
                                <?= $row['appid'] === 'IA01M168064F20250505533' ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($row['appid']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted mb-1">Department</label>
                    <select class="form-select select2" id="filterDepartment">
                        <option value="">-- Semua Department --</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted mb-1">Employee (Multi)</label>
                    <select class="form-select select2" id="filterEmployee" multiple="multiple">
                        <option value="22363">Budi Kejora 23</option>
                        <option value="22701">Budi Kejora 29</option>
                    </select>
                </div>
                <div class="col-lg-1 text-end">
                    <button id="btnSearch" class="btn btn-primary w-100 mb-2">Search</button>
                    <button id="btnReset" class="btn btn-outline-secondary w-100">Reset</button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table id="attendanceTable" class="table table-striped table-hover table-bordered" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Schedule Type</th>
                        <th>Date</th>
                        <th>Work Hour</th>
                        <th>In</th>
                        <th>Out</th>
                        <th>Duration</th>
                        <th>Late (min)</th>
                        <th>Early Out (min)</th>
                        <th>OT Start</th>
                        <th>OT End</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(document).ready(function() {
            const datePicker = flatpickr("#filterDateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                conjunction: " to "
            });

            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                closeOnSelect: false
            });

            const table = $('#attendanceTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [4, 'desc']
                ],
                dom: "<'row'<'col-md-6'B><'col-md-6'f>>rt<'row'<'col-md-6'i><'col-md-6'p>>",
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'].map(t => ({
                    extend: t,
                    className: 'btn-sm btn-outline-secondary'
                })),
                columnDefs: [{
                    targets: 0,
                    width: "50px",
                    className: "text-center"
                }]
            });

            // Helper: menit → jam:menit
            function minutesToTime(minutes) {
                if (!minutes || minutes == 0) return '-';
                const h = Math.floor(minutes / 60).toString().padStart(2, '0');
                const m = (minutes % 60).toString().padStart(2, '0');
                return `${h}:${m}`;
            }

            // Helper: format waktu absen
            function formatTime(time) {
                return time ? time.substring(11, 16) : '<span class="text-dark">-</span>';
            }

            function loadAttendanceData() {
                const dateRange = $('#filterDateRange').val() || '';
                const appId = $('#filterAppID').val() || '';
                const department = $('#filterDepartment').val() || '';
                const employees = $('#filterEmployee').val() || [];

                if (!dateRange) {
                    alert('Please select a date range');
                    return;
                }

                if (!employees.length) {
                    alert('Please select at least one employee');
                    return;
                }

                if (!appId) {
                    alert('Please select an app ID');
                    return;
                }

                if (!department) {
                    alert('Please select a department');
                    return;
                }

                console.clear();
                console.log('%c Filter Dikirim ', 'background:#3498db; color:white; padding:8px; font-weight:bold;');
                console.log('Date Range :', dateRange || '(kosong)');
                console.log('AppID      :', appId || '(semua)');
                console.log('Employees  :', employees.length ? employees : '(semua)');

                $.ajax({
                    url: 'http://localhost:8080/index.php/attendance/get_data',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        date_range: dateRange,
                        appid: appId,
                        department: department,
                        employees: employees
                    },
                    success: function(res) {
                        table.clear();

                        if (res.status === 'success' && res.data.length > 0) {
                            console.log(res.data);
                            const formattedData = res.data.map((row, index) => {
                                const workHour = row.work_hour.replace(/:00-/g, '-').replace(/:00/g, '');
                                const isAbsent = !row.in && !row.out;

                                let displayDate = row.date; // default
                                if (row.date && row.date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                    const [y, m, d] = row.date.split('-');
                                    displayDate = `${d}-${m}-${y}`;
                                }

                                return [
                                    index + 1,
                                    row.employee_name,
                                    row.department,
                                    row.schedule_type,
                                    displayDate,
                                    workHour,
                                    formatTime(row.in),
                                    formatTime(row.out),
                                    isAbsent ? '<span class="text-dark fw-bold">-</span>' : row.work_duration.substring(0, 5),
                                    row.late > 0 ? `<span class="text-danger fw-bold">${row.late}</span>` : '0',
                                    row.early_out > 0 ? `<span class="text-warning fw-bold">${row.early_out}</span>` : '0',
                                    minutesToTime(row.overtime_start),
                                    minutesToTime(row.overtime_end)
                                ];
                            });

                            table.rows.add(formattedData).draw();
                        } else {
                            table.draw();
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal mengambil data!');
                    }
                });
            }

            $('#btnSearch').on('click', loadAttendanceData);

            $('#btnReset').on('click', function() {
                datePicker.clear();
                $('#filterAppID, #filterDepartment, #filterEmployee').val(null).trigger('change');
                table.clear().draw();
                console.clear();
            });

            // Ketika AppID berubah → load Department sesuai AppID
            $('#filterAppID').on('change', function() {
                const appId = $(this).val()?.trim();
                const $deptSelect = $('#filterDepartment');

                $deptSelect.empty().prop('disabled', true);
                $deptSelect.append('<option value="">-- Loading Department... --</option>');

                if (!appId) {
                    $deptSelect.empty()
                        .append('<option value="">-- Semua Department --</option>')
                        .prop('disabled', false)
                        .trigger('change');
                    return;
                }

                loadDepartment(appId);
            });

            function loadDepartment(appId) {
                const $deptSelect = $('#filterDepartment');
                $.ajax({
                    url: 'http://localhost:8080/index.php/tbdepartements/get_names/' + encodeURIComponent(appId),
                    method: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(res) {
                        $deptSelect.empty();
                        $deptSelect.append('<option value="">-- Semua Department --</option>');

                        if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
                            res.data.forEach(function(dept) {
                                $deptSelect.append(`<option value="${dept.id}">${dept.name}</option>`);
                            });
                        } else {
                            $deptSelect.append('<option value="">Tidak ada department</option>');
                        }

                        $deptSelect.prop('disabled', false).trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Gagal load department:', xhr.responseText);
                        $deptSelect.empty()
                            .append('<option value="">Gagal memuat department</option>')
                            .prop('disabled', false);
                    }
                });
            }

            // Ketika Department berubah → load Employee sesuai Department
            $('#filterDepartment').on('change', function() {
                const department = $(this).val()?.trim();
                const appId = $('#filterAppID').val()?.trim();
                const $empSelect = $('#filterEmployee');

                $empSelect.empty().prop('disabled', true);
                $empSelect.append('<option value="">-- Loading Employee... --</option>');

                // Jika tidak pilih AppID atau Department → reset employee
                if (!appId || !department) {
                    $empSelect.empty()
                        .append('<option value="">-- Semua Employee --</option>')
                        .prop('disabled', false)
                        .trigger('change');
                    return;
                }

                // AJAX ambil employee
                loadEmployee(appId, department);

            });

            function loadEmployee(appId, department) {
                const $empSelect = $('#filterEmployee');
                $.ajax({
                    url: 'http://localhost:8080/index.php/tbemployee/get_by_department',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        appid: appId,
                        department_id: department
                    },
                    timeout: 10000,
                    success: function(res) {
                        $empSelect.empty();

                        if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
                            console.log(res.data);
                            res.data.forEach(function(emp) {
                                $empSelect.append(
                                    `<option value="${emp.employee_id}">${emp.employee_full_name}</option>`
                                );
                            });
                        } else {
                            $empSelect.append('<option value="">Tidak ada employee</option>');
                        }

                        $empSelect.prop('disabled', false).trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Gagal load employee:', xhr.responseText);
                        $empSelect.empty()
                            .append('<option value="">Gagal memuat employee</option>')
                            .prop('disabled', false)
                            .trigger('change');
                    }
                });
            }

            table.buttons().container().addClass('mb-3').prependTo('.table-container');

            loadDepartment("IA01M168064F20250505533");

        });
    </script>
</body>

</html>