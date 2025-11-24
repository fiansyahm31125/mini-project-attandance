
<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Attendance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tbemployee_model');
        $this->load->model('Tbusertempsch_model');
        $this->load->model('Tbuserusedclasses_model');
        $this->load->model('Tbuserofrun_model');
        $this->load->model('Tbcheckinout_mobile_model');
        header('Content-Type: application/json');
    }

    /**
     * Endpoint: /index.php/tbusertempsch/get_by_appid_and_empid_api/{appid}/{empid}
     */

    function makeDateTime($date, $start_time, $end_time, $end_checkIn = null, $status = null)
    {
        $start = new DateTime("$date $start_time");
        $end   = new DateTime("$date $end_time");

        if ($end_checkIn != null) {
            $end_checkIn = new DateTime("$end_checkIn");
        }

        // Jika jam akhir lebih kecil dari jam awal, berarti lintas hari
        if ($end_checkIn != null && $start > $end_checkIn && $status == 'i') {
            $start->modify('-1 day');
        } else if ($end_checkIn != null && $start < $end_checkIn && $status == 'o') {
            $start->modify('+1 day');
            $end->modify('+1 day');
        } else if ($end < $start) {
            $end->modify('+1 day');
        }

        return [
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end->format('Y-m-d H:i:s')
        ];
    }

    function intervalWork($date, $in, $out, $start_time, $end_time)
    {
        $in = new DateTime($in);
        $out   = new DateTime($out);

        // HASIL makeDateTime ASLINYA STRING → WAJIB DIKONVERSI
        $range = $this->makeDateTime($date, $start_time, $end_time);
        $datetime_scheduledIn  = new DateTime($range['start']);
        $datetime_scheduledOut = new DateTime($range['end']);

        $effective_time_start = $datetime_scheduledIn;
        $effective_time_end = $datetime_scheduledOut;
        if ($in > $effective_time_start) {
            $effective_time_start = $in;
        }
        if ($out < $effective_time_end) {
            $effective_time_end = $out;
        }

        return [
            'work_duration' => $in->diff($out)->format('%H:%I:%S'),
            'effective_work' => $effective_time_start->diff($effective_time_end)->format('%H:%I:%S'),
        ];
    }

    public function calculateLateEarlyOut(
        $date,
        $scheduledIn,
        $actualIn,
        $scheduledOut,
        $actualOut,
        $toleranceLate = 0,
        $toleranceEarly = 0
    ) {
        // HASIL makeDateTime ASLINYA STRING → WAJIB DIKONVERSI
        $range = $this->makeDateTime($date, $scheduledIn, $scheduledOut);
        $datetime_scheduledIn  = new DateTime($range['start']); // <--- FIX PALING PENTING
        $datetime_scheduledOut = new DateTime($range['end']);   // <--- FIX PALING PENTING

        // KONVERSI actual IN / OUT menjadi DateTime
        $actualInDT = !empty($actualIn) ? new DateTime($actualIn) : null;
        $actualOutDT = !empty($actualOut) ? new DateTime($actualOut) : null;

        // LATE
        $lateMinutes = 0;
        if ($actualInDT !== null && $actualInDT > $datetime_scheduledIn) {
            $late = ($actualInDT->getTimestamp() - $datetime_scheduledIn->getTimestamp()) / 60;
            $lateMinutes = ($late >= $toleranceLate) ? (int)$late : 0;
        }

        // EARLY OUT
        $earlyOutMinutes = 0;
        if ($actualOutDT !== null && $actualOutDT < $datetime_scheduledOut) {
            $early = ($datetime_scheduledOut->getTimestamp() - $actualOutDT->getTimestamp()) / 60;
            $earlyOutMinutes = ($early >= $toleranceEarly) ? (int)$early : 0;
        }

        return [
            'late_minutes' => $lateMinutes,
            'early_out_minutes' => $earlyOutMinutes
        ];
    }


    public function calculateOvertime(
        $date,
        $scheduledIn,
        $actualIn,
        $scheduledOut,
        $actualOut,
        $overtime_start = 0,
        $overtime_end = 0
    ) {
        // HASIL makeDateTime ASLINYA STRING → WAJIB DIKONVERSI
        $range = $this->makeDateTime($date, $scheduledIn, $scheduledOut);
        $datetime_scheduledIn  = new DateTime($range['start']); // <--- FIX PALING PENTING
        $datetime_scheduledOut = new DateTime($range['end']);   // <--- FIX PALING PENTING

        // KONVERSI actual IN / OUT menjadi DateTime
        $actualInDT = !empty($actualIn) ? new DateTime($actualIn) : null;
        $actualOutDT = !empty($actualOut) ? new DateTime($actualOut) : null;

        // Overtime Start
        $overtimeStartMinutes = 0;
        $total_overtime_start = 0;
        if ($actualInDT !== null && $actualInDT < $datetime_scheduledIn && $overtime_start != 0) {
            $total_overtime_start = ($datetime_scheduledIn->getTimestamp() - $actualInDT->getTimestamp()) / 60;
            $overtimeStartMinutes = ($total_overtime_start >= $overtime_start) ? (int)$total_overtime_start : 0;
        }

        // Overtime End
        $overtimeEndMinutes = 0;
        $total_overtime_end = 0;
        if ($actualOutDT !== null && $actualOutDT > $datetime_scheduledOut && $overtime_end != 0) {
            $total_overtime_end = ($actualOutDT->getTimestamp() - $datetime_scheduledOut->getTimestamp()) / 60;
            $overtimeEndMinutes = ($total_overtime_end >= $overtime_end) ? (int)$total_overtime_end : 0;
        }

        return [
            'overtime_start_minutes' => $overtimeStartMinutes,
            'overtime_end_minutes' => $overtimeEndMinutes,
            'early_in_minutes' => $total_overtime_start,
            'late_out_minutes' => $total_overtime_end,
        ];
    }

    public function calculateBreak($type, $break_in, $break_out, $break_duration)
    {
        $break_type = null;
        $break_time = null;

        // define break type
        if ($type == 0) {
            $break_type = 'with out Break';
        } else if ($type == 1) {
            $break_type = 'duration';
        } else if ($type == 2) {
            $break_type = 'time Span';
        }

        // define break time
        if ($type == 0) {
            $break_time = '';
        } else if ($type == 1) {
            $break_time = $break_duration . "m";
        } else if ($type == 2) {
            $break_time = $break_in . '-' . $break_out;
        }

        return [
            'break_type' => $break_type,
            'break_time' => $break_time
        ];
    }

    public function get_by_appid_and_empid_api($appid, $empid, $date = null)
    {
        $emp_data = $this->Tbemployee_model->get_employee($appid, $empid);
        $data = null;
        $raw  = null;

        // ---------------------------
        // 1. Temporary Schedule
        // ---------------------------
        $emp_schs = $this->Tbusertempsch_model->get_with_schclass($appid, $empid, $date);

        if ($emp_schs) {

            $items = []; // pastikan array

            foreach ($emp_schs as $emp_sch_temp) {

                $start = "$date {$emp_sch_temp['start_time']}";
                $ckkin = $this->makeDateTime($date, $emp_sch_temp['start_checkin_time'], $emp_sch_temp['end_checkin_time'], $start, 'i');
                $dateTimeCheckin = $this->Tbcheckinout_mobile_model->get_checkin($empid, $ckkin['start'], $ckkin['end']);

                $ckkout = $this->makeDateTime($date, $emp_sch_temp['start_checkout_time'], $emp_sch_temp['end_checkout_time'], $ckkin['end'], 'o');
                $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);

                $item = new stdClass();
                $item->employee_name  = $emp_data['employee_full_name'];
                $item->department     = $emp_data['name'];
                $item->schedule_type  = 'Schedule Temporary (' . $emp_sch_temp['name'] . ')';
                $item->date           = $date;
                $item->work_hour      = $emp_sch_temp['start_time'] . '-' . $emp_sch_temp['end_time'];
                $item->in             = $dateTimeCheckin->first_checkin;
                $item->out            = $dateTimeCheckout->last_checkout;

                $interval_work = $this->intervalWork($date, $item->in, $item->out, $emp_sch_temp['start_time'], $emp_sch_temp['end_time']);
                $item->work_duration  = ($item->in && $item->out) ? $interval_work['work_duration'] : 0;
                $item->effective_work  = ($item->in && $item->out) ? $interval_work['effective_work'] : 0;

                $calcLateEarly = $this->calculateLateEarlyOut(
                    $date,
                    $emp_sch_temp['start_time'],
                    $item->in,
                    $emp_sch_temp['end_time'],
                    $item->out,
                    $emp_sch_temp['late_minutes'],
                    $emp_sch_temp['early_minutes']
                );

                $item->late      = $calcLateEarly['late_minutes'];
                $item->early_out = $calcLateEarly['early_out_minutes'];

                $calcOT = $this->calculateOvertime(
                    $date,
                    $emp_sch_temp['start_time'],
                    $item->in,
                    $emp_sch_temp['end_time'],
                    $item->out,
                    $emp_sch_temp['overtime_start'],
                    $emp_sch_temp['overtime_end']
                );

                $item->overtime_start = $calcOT['overtime_start_minutes'];
                $item->overtime_end   = $calcOT['overtime_end_minutes'];

                $item->early_in = $calcOT['early_in_minutes'];
                $item->late_out = $calcOT['late_out_minutes'];

                $break = $this->calculateBreak($emp_sch_temp["break_type"], $emp_sch_temp['break_in'], $emp_sch_temp['break_out'], $emp_sch_temp['break_duration']);
                $item->break_type = $break['break_type'];
                $item->break_time = $break['break_time'];

                $items[] = $item;
            }

            return [
                'status' => true,
                'data'   => $items,
                'raw'    => $emp_schs,
                'count'  => count($items)
            ];
        }


        // ---------------------------
        // 2. Used Classes (Automatic)
        // ---------------------------
        $emp_used_classes = $this->Tbuserusedclasses_model->get_with_schclass($appid, $empid);

        if ($emp_used_classes && count($emp_used_classes) > 0) {

            $items = [];

            foreach ($emp_used_classes as $emp_used_class) {

                $start = "$date {$emp_used_class['start_time']}";
                $ckkin = $this->makeDateTime(
                    $date,
                    $emp_used_class['start_checkin_time'],
                    $emp_used_class['end_checkin_time'],
                    $start,
                    'i'
                );

                $dateTimeCheckin = $this->Tbcheckinout_mobile_model
                    ->get_checkin($empid, $ckkin['start'], $ckkin['end']);

                $ckkout = $this->makeDateTime(
                    $date,
                    $emp_used_class['start_checkout_time'],
                    $emp_used_class['end_checkout_time'],
                    $ckkin['end'],
                    'o'
                );

                $dateTimeCheckout = $this->Tbcheckinout_mobile_model
                    ->get_checkout($empid, $ckkout['start'], $ckkout['end']);

                $item = new stdClass();
                $item->employee_name  = $emp_data['employee_full_name'];
                $item->department     = $emp_data['name'];
                $item->schedule_type  = 'Automatic (' . $emp_used_class['name'] . ')';
                $item->date           = $date;
                $item->work_hour      = $emp_used_class['start_time'] . '-' . $emp_used_class['end_time'];
                $item->in             = $dateTimeCheckin->first_checkin ?? null;
                $item->out            = $dateTimeCheckout->last_checkout ?? null;

                $interval_work = $this->intervalWork($date, $item->in, $item->out, $emp_used_class['start_time'], $emp_used_class['end_time']);
                $item->work_duration  = ($item->in && $item->out)
                    ? $interval_work['work_duration']
                    : 0;
                $item->effective_work  = ($item->in && $item->out)
                    ? $interval_work['effective_work']
                    : 0;

                // Hitung late/early
                $calcLateEarly = $this->calculateLateEarlyOut(
                    $date,
                    $emp_used_class['start_time'],
                    $item->in,
                    $emp_used_class['end_time'],
                    $item->out,
                    $emp_used_class['late_minutes'],
                    $emp_used_class['early_minutes']
                );

                $item->late         = $calcLateEarly['late_minutes'];
                $item->early_out    = $calcLateEarly['early_out_minutes'];

                // Hitung OT
                $calcOT = $this->calculateOvertime(
                    $date,
                    $emp_used_class['start_time'],
                    $item->in,
                    $emp_used_class['end_time'],
                    $item->out,
                    $emp_used_class['overtime_start'],
                    $emp_used_class['overtime_end']
                );

                $item->overtime_start = $calcOT['overtime_start_minutes'];
                $item->overtime_end   = $calcOT['overtime_end_minutes'];

                $item->early_in = $calcOT['early_in_minutes'];
                $item->late_out = $calcOT['late_out_minutes'];

                $break = $this->calculateBreak($emp_used_class["break_type"], $emp_used_class['break_in'], $emp_used_class['break_out'], $emp_used_class['break_duration']);
                $item->break_type = $break['break_type'];
                $item->break_time = $break['break_time'];

                // simpan item
                $items[] = $item;
            }

            return [
                'status' => true,
                'data'   => $items,
                'raw' => $emp_used_classes,
                'count'  => count($items)
            ];
        }


        // ---------------------------
        // 3. NumRun (Schedule)
        // ---------------------------
        $num_runs = $this->Tbuserofrun_model->get_with_numrun($appid, $empid, $date);

        if ($num_runs && count($num_runs) > 0) {

            $items = [];

            foreach ($num_runs as $num_run) {

                $start = "$date {$num_run['start_time']}";
                $ckkin = $this->makeDateTime(
                    $date,
                    $num_run['start_checkin_time'],
                    $num_run['end_checkin_time'],
                    $start,
                    'i'
                );

                $dateTimeCheckin = $this->Tbcheckinout_mobile_model
                    ->get_checkin($empid, $ckkin['start'], $ckkin['end']);

                $ckkout = $this->makeDateTime(
                    $date,
                    $num_run['start_checkout_time'],
                    $num_run['end_checkout_time'],
                    $ckkin['end'],
                    'o'
                );

                $dateTimeCheckout = $this->Tbcheckinout_mobile_model
                    ->get_checkout($empid, $ckkout['start'], $ckkout['end']);

                $item = new stdClass();
                $item->employee_name  = $emp_data['employee_full_name'];
                $item->department     = $emp_data['name'];
                $item->schedule_type  = 'Schedule (' . $num_run['run_name'] . ')';
                $item->date           = $date;
                $item->work_hour      = $num_run['start_time'] . '-' . $num_run['end_time'];
                $item->in             = $dateTimeCheckin->first_checkin ?? null;
                $item->out            = $dateTimeCheckout->last_checkout ?? null;
                $interval_work = $this->intervalWork($date, $item->in, $item->out, $num_run['start_time'], $num_run['end_time']);
                $item->work_duration  = ($item->in && $item->out)
                    ? $interval_work['work_duration']
                    : 0;
                $item->effective_work  = ($item->in && $item->out)
                    ? $interval_work['effective_work']
                    : 0;

                // Hitung late / early-out
                $calcLateEarly = $this->calculateLateEarlyOut(
                    $date,
                    $num_run['start_time'],
                    $item->in,
                    $num_run['end_time'],
                    $item->out,
                    $num_run['late_minutes'],
                    $num_run['early_minutes']
                );

                $item->late         = $calcLateEarly['late_minutes'];
                $item->early_out    = $calcLateEarly['early_out_minutes'];

                // Hitung overtime
                $calcOT = $this->calculateOvertime(
                    $date,
                    $num_run['start_time'],
                    $item->in,
                    $num_run['end_time'],
                    $item->out,
                    $num_run['overtime_start'],
                    $num_run['overtime_end']
                );

                $item->overtime_start = $calcOT['overtime_start_minutes'];
                $item->overtime_end   = $calcOT['overtime_end_minutes'];

                $item->early_in = $calcOT['early_in_minutes'];
                $item->late_out = $calcOT['late_out_minutes'];

                $break = $this->calculateBreak($num_run["break_type"], $num_run['break_in'], $num_run['break_out'], $num_run['break_duration']);
                $item->break_type = $break['break_type'];
                $item->break_time = $break['break_time'];

                // SIMPAN KE ARRAY
                $items[] = $item;
            }

            return [
                'status' => true,
                'data'  => $items,
                'raw' => $num_runs,
                'count'  => count($items)
            ];
        }


        // ---------------------------
        // 4. NOT FOUND
        // ---------------------------
        return [
            'status'  => false,
            'data'    => null,
            'message' => 'Data tidak ditemukan'
        ];
    }


    public function get_data()
    {
        // Pastikan hanya POST
        if ($this->input->method() !== 'post') {
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        // Tangkap data dari frontend
        $date_range  = $this->input->post('date_range');
        $appid       = $this->input->post('appid');
        $department  = $this->input->post('department');
        $employees   = $this->input->post('employees'); // string, kita pecah

        // Parsing date range
        $start_date = null;
        $end_date   = null;
        if (!empty($date_range)) {
            if (strpos($date_range, ' to ') !== false) {
                list($start_date, $end_date) = array_map('trim', explode(' to ', $date_range));
            } else {
                $start_date = $end_date = trim($date_range);
            }
        }

        // Debug: Lihat apa yang diterima
        log_message('debug', 'Filter diterima: ' . json_encode([
            'date_range' => $date_range,
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'appid'      => $appid,
            'department' => $department,
            'employees'  => $employees
        ]));

        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($end_date))->modify('+1 day') // include end date
        );


        $all_data = [];

        foreach ($period as $date) {

            $currentDate = $date->format("Y-m-d");

            foreach ($employees as $emp) {

                $item = $this->get_by_appid_and_empid_api($appid, $emp, $currentDate);

                if ($item['status'] == true && !empty($item['data'])) {

                    // karena data = array of multiple items
                    $all_data = array_merge($all_data, $item['data']);
                }
            }
        }


        // Response JSON
        echo json_encode([
            'status' => 'success',
            'data'   => $all_data,
            'debug'  => [
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'employees'  => $employees
            ]
        ]);
    }






    public function get_by_appid_and_empid($appid, $empid, $date = null)
    {
        echo json_encode($this->get_by_appid_and_empid_api($appid, $empid, $date));
    }
}
