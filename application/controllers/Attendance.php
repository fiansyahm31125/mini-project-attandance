
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
     * Endpoint: /index.php/tbusertempsch/get_by_appid_and_empid/{appid}/{empid}
     */

    function makeDateTime($date, $start_time, $end_time, $end_checkIn = null)
    {
        $start = new DateTime("$date $start_time");
        $end   = new DateTime("$date $end_time");

        // Jika jam akhir lebih kecil dari jam awal, berarti lintas hari
        if ($end_checkIn != null && $start < $end_checkIn) {
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

    function intervalWork($start, $end)
    {
        $start = new DateTime($start);
        $end   = new DateTime($end);
        return $start->diff($end)->format('%H:%I:%S');
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
        if ($actualInDT !== null && $actualInDT < $datetime_scheduledIn) {
            $total_overtime_start = ($datetime_scheduledIn->getTimestamp() - $actualInDT->getTimestamp()) / 60;
            $overtimeStartMinutes = ($total_overtime_start >= $overtime_start) ? (int)$total_overtime_start : 0;
        }

        // Overtime End
        $overtimeEndMinutes = 0;
        if ($actualOutDT !== null && $actualOutDT > $datetime_scheduledOut) {
            $total_overtime_end = ($actualOutDT->getTimestamp() - $datetime_scheduledOut->getTimestamp()) / 60;
            $overtimeEndMinutes = ($total_overtime_end >= $overtime_end) ? (int)$total_overtime_end : 0;
        }

        return [
            'overtime_start_minutes' => $overtimeStartMinutes,
            'overtime_end_minutes' => $overtimeEndMinutes
        ];
    }

    public function get_by_appid_and_empid($appid, $empid, $date = null)
    {
        $emp_sch_temp = $this->Tbusertempsch_model->get_with_schclass($appid, $empid, $date);
        $emp_data = $this->Tbemployee_model->get_employee($appid, $empid);
        $data = [];
        if ($emp_sch_temp) {
            $ckkin = $this->makeDateTime($date, $emp_sch_temp['start_checkin_time'], $emp_sch_temp['end_checkin_time']);
            $dateTimeCheckin = $this->Tbcheckinout_mobile_model->get_checkin($empid, $ckkin['start'], $ckkin['end']);

            $ckkout = $this->makeDateTime($date, $emp_sch_temp['start_checkout_time'], $emp_sch_temp['end_checkout_time'], $ckkin['end']);
            $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);

            $item = new stdClass();
            $item->employee_name = $emp_data['employee_full_name'];
            $item->department = $emp_data['name'];
            $item->schedule_type = 'Temporary(' . $emp_sch_temp['name'] . ')';
            $item->date = $date;
            $item->work_hour = $emp_sch_temp['start_time'] . '-' . $emp_sch_temp['end_time'];
            $item->in = $dateTimeCheckin->first_checkin;
            $item->out = $dateTimeCheckout->last_checkout;
            $item->work_duration = $item->in != null && $item->out != null ? $this->intervalWork($item->in, $item->out) : 0;
            $item->late = $this->calculateLateEarlyOut($date, $emp_sch_temp['start_time'], $item->in, $emp_sch_temp['end_time'], $item->out, $emp_sch_temp['late_minutes'], $emp_sch_temp['early_minutes'])['late_minutes'];
            $item->early_out = $this->calculateLateEarlyOut($date, $emp_sch_temp['start_time'], $item->in, $emp_sch_temp['end_time'], $item->out, $emp_sch_temp['late_minutes'], $emp_sch_temp['early_minutes'])['early_out_minutes'];
            $item->overtime_start = $this->calculateOvertime($date, $emp_sch_temp['start_time'], $item->in, $emp_sch_temp['end_time'], $item->out, $emp_sch_temp['overtime_start'], $emp_sch_temp['overtime_end'])['overtime_start_minutes'];
            $item->overtime_end = $this->calculateOvertime($date, $emp_sch_temp['start_time'], $item->in, $emp_sch_temp['end_time'], $item->out, $emp_sch_temp['overtime_start'], $emp_sch_temp['overtime_end'])['overtime_end_minutes'];
            echo json_encode([
                'status' => true,
                'data' => $item,
                'raw' => $emp_sch_temp
            ]);
        } else {
            $emp_used_class = $this->Tbuserusedclasses_model->get_with_schclass($appid, $empid);
            if ($emp_used_class) {
                $ckkin = $this->makeDateTime($date, $emp_used_class['start_checkin_time'], $emp_used_class['end_checkin_time']);
                $dateTimeCheckin = $this->Tbcheckinout_mobile_model->get_checkin($empid, $ckkin['start'], $ckkin['end']);

                $ckkout = $this->makeDateTime($date, $emp_used_class['start_checkout_time'], $emp_used_class['end_checkout_time'], $ckkin['end']);
                $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);

                $item = new stdClass();
                $item->employee_name = $emp_data['employee_full_name'];
                $item->department = $emp_data['name'];
                $item->schedule_type = 'Automatic(' . $emp_used_class['name'] . ')';
                $item->date = $date;
                $item->work_hour = $emp_used_class['start_time'] . '-' . $emp_used_class['end_time'];
                $item->in = $dateTimeCheckin->first_checkin;
                $item->out = $dateTimeCheckout->last_checkout;
                $item->work_duration = $item->in != null && $item->out != null ? $this->intervalWork($item->in, $item->out) : 0;
                $item->late = $this->calculateLateEarlyOut($date, $emp_used_class['start_time'], $item->in, $emp_used_class['end_time'], $item->out, $emp_used_class['late_minutes'], $emp_used_class['early_minutes'])['late_minutes'];
                $item->early_out = $this->calculateLateEarlyOut($date, $emp_used_class['start_time'], $item->in, $emp_used_class['end_time'], $item->out, $emp_used_class['late_minutes'], $emp_used_class['early_minutes'])['early_out_minutes'];
                $item->overtime_start = $this->calculateOvertime($date, $emp_used_class['start_time'], $item->in, $emp_used_class['end_time'], $item->out, $emp_used_class['overtime_start'], $emp_used_class['overtime_end'])['overtime_start_minutes'];
                $item->overtime_end = $this->calculateOvertime($date, $emp_used_class['start_time'], $item->in, $emp_used_class['end_time'], $item->out, $emp_used_class['overtime_start'], $emp_used_class['overtime_end'])['overtime_end_minutes'];
                echo json_encode([
                    'status' => true,
                    'data' => $item,
                    'raw' => $emp_used_class,
                ]);
            } else {
                $num_run = $this->Tbuserofrun_model->get_with_numrun($appid, $empid, $date);
                if ($num_run) {
                    $ckkin = $this->makeDateTime($date, $num_run['start_checkin_time'], $num_run['end_checkin_time']);
                    $dateTimeCheckin = $this->Tbcheckinout_mobile_model->get_checkin($empid, $ckkin['start'], $ckkin['end']);

                    $ckkout = $this->makeDateTime($date, $num_run['start_checkout_time'], $num_run['end_checkout_time'], $ckkin['end']);
                    $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);

                    $item = new stdClass();
                    $item->employee_name = $emp_data['employee_full_name'];
                    $item->department = $emp_data['name'];
                    $item->schedule_type = 'Schedule(' . $num_run['run_name'] . ')';
                    $item->date = $date;
                    $item->work_hour = $num_run['start_time'] . '-' . $num_run['end_time'];
                    $item->in = $dateTimeCheckin->first_checkin;
                    $item->out = $dateTimeCheckout->last_checkout;
                    $item->work_duration = $item->in != null && $item->out != null ? $this->intervalWork($item->in, $item->out) : 0;
                    $item->late = $this->calculateLateEarlyOut($date, $num_run['start_time'], $item->in, $num_run['end_time'], $item->out, $num_run['late_minutes'], $num_run['early_minutes'])['late_minutes'];
                    $item->early_out = $this->calculateLateEarlyOut($date, $num_run['start_time'], $item->in, $num_run['end_time'], $item->out, $num_run['late_minutes'], $num_run['early_minutes'])['early_out_minutes'];
                    $item->overtime_start = $this->calculateOvertime($date, $num_run['start_time'], $item->in, $num_run['end_time'], $item->out, $num_run['overtime_start'], $num_run['overtime_end'])['overtime_start_minutes'];
                    $item->overtime_end = $this->calculateOvertime($date, $num_run['start_time'], $item->in, $num_run['end_time'], $item->out, $num_run['overtime_start'], $num_run['overtime_end'])['overtime_end_minutes'];
                    echo json_encode([
                        'status' => true,
                        'data' => $item,
                        'raw' => $num_run,
                    ]);
                } else {
                    echo json_encode([
                        'status' => false,
                        'message' => 'Data tidak ditemukan'
                    ]);
                }
            }
        }
    }
}
