
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
    public function calculateCheckInOut($get_sch, $date)
    {
        if (empty($get_sch) || empty($date)) {
            return false;
        }

        // Konversi waktu jadwal ke format HH:MM:SS
        $checkintime1  = (new DateTime($get_sch->start_checkin_time))->format('H:i:s');
        $checkintime2  = (new DateTime($get_sch->end_checkin_time))->format('H:i:s');
        $checkouttime1 = (new DateTime($get_sch->start_checkout_time))->format('H:i:s');
        $checkouttime2 = (new DateTime($get_sch->end_checkout_time))->format('H:i:s');
        $starttime     = (new DateTime($get_sch->start_time))->format('H:i:s');

        // Variabel hasil
        $ckin_start = null;
        $ckin_end   = null;
        $ckout_start = null;
        $ckout_end   = null;
        $break_start = null;
        $break_end   = null;

        // Tambahan flag
        $flag_checkout_diff_day = false;
        $flag_morning_diff_day  = false;

        // Variabel jam kerja
        $start_work = $get_sch->start_time_real;
        $end_work   = $get_sch->end_time_real;

        // 🔹 Kasus normal: waktu checkin1 <= checkin2
        if ($checkintime1 <= $checkintime2) {
            $ckin_start = "$date $checkintime1";
            $ckin_end   = "$date $checkintime2";
            $break_start = "$date {$get_sch->start_time_real}";
            $ckout_start = "$date $checkouttime1";

            if ($get_sch->end_time_real < $get_sch->start_time_real) {
                // Shift lintas hari (contoh: mulai jam 22:00 - selesai jam 06:00)
                $ckout_end  = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " $checkouttime2";
                $break_end  = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " {$get_sch->end_time_real}";
                $flag_checkout_diff_day = true;

                // Hitung interval kerja lintas hari
                $start = new DateTime($start_work);
                $end   = new DateTime($end_work);
                if ($end < $start) $end->modify('+1 day');
                $get_sch->interval_work = $start->diff($end)->format('%H:%I:%S');
            } else {
                // Shift normal (hari yang sama)
                $ckout_end  = "$date $checkouttime2";
                $break_end  = "$date {$get_sch->end_time_real}";
            }

            // Jika checkout_time2 < end_time_real → lintas hari
            if ($checkouttime2 < $get_sch->end_time_real) {
                $flag_checkout_diff_day = true;
                $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " $checkouttime2";
            }
        }
        // 🔹 Kasus checkin lintas hari
        else {
            if ($checkintime1 <= $starttime) {
                // Shift malam, checkin sebelum starttime (contoh mulai 22:00)
                $ckin_start = "$date $checkintime1";
                $ckin_end   = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " $checkintime2";
                $break_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " {$get_sch->start_time_real}";

                $ckout_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " $checkouttime1";
                $ckout_end   = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " $checkouttime2";
                $break_end   = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . " {$get_sch->end_time_real}";

                // Hitung interval kerja lintas hari
                $start = new DateTime($start_work);
                $end   = new DateTime($end_work);
                if ($end < $start) $end->modify('+1 day');
                $get_sch->interval_work = $start->diff($end)->format('%H:%I:%S');
            } else {
                // Kondisi lain, mungkin shift pagi tapi data lintas tanggal
                $ckin_start = (new DateTime($date))->modify('-1 day')->format('Y-m-d') . " $checkintime1";
                $ckin_end   = "$date $checkintime2";
                $break_start = "$date {$get_sch->start_time_real}";

                $ckout_start = "$date $checkouttime1";
                $ckout_end   = "$date $checkouttime2";
                $break_end   = "$date {$get_sch->end_time_real}";

                $flag_morning_diff_day = true;
            }
        }

        // 🔹 Return hasil dalam bentuk object
        return (object) [
            'checkin_start' => $ckin_start,
            'checkin_end'   => $ckin_end,
            'checkout_start' => $ckout_start,
            'checkout_end'   => $ckout_end,
            'break_start' => $break_start,
            'break_end'   => $break_end,
            'interval_work' => isset($get_sch->interval_work) ? $get_sch->interval_work : null,
            'flag_checkout_diff_day' => $flag_checkout_diff_day,
            'flag_morning_diff_day'  => $flag_morning_diff_day,
        ];
    }


    function makeDateTime($date, $start_time, $end_time,$end_checkIn=null)
    {
        $start = new DateTime("$date $start_time");
        $end   = new DateTime("$date $end_time");

        // Jika jam akhir lebih kecil dari jam awal, berarti lintas hari
        if($end_checkIn!=null){
            if ($start < $end_checkIn) {
                $start->modify('+1 day');
                $end->modify('+1 day');
            }
        }
        else if ($end < $start) {
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
        // HITUNG TERLAMBAT
        $datetime_scheduledIn = $this->makeDateTime($date, $scheduledIn, $scheduledOut)['start'];
        $datetime_scheduleOut = $this->makeDateTime($date, $scheduledIn, $scheduledOut)['end'];        

        $lateMinutes = 0;
        if ($actualIn > $datetime_scheduledIn) {
            $late = ($actualIn->getTimestamp() - $datetime_scheduledIn->getTimestamp()) / 60;
            $lateMinutes = ($late >= $toleranceLate) ? (int)$late : 0;
        }

        // HITUNG PULANG CEPAT
        $earlyOutMinutes = 0;
        if ($datetime_scheduleOut < $scheduledOut) {
            $early = ($scheduledOut->getTimestamp() - $datetime_scheduleOut->getTimestamp()) / 60;
            $earlyOutMinutes = ($early >= $toleranceEarly) ? (int)$early : 0;
        }

        return [
            'late_minutes' => $lateMinutes,
            'early_out_minutes' => $earlyOutMinutes
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

            $ckkout = $this->makeDateTime($date, $emp_sch_temp['start_checkout_time'], $emp_sch_temp['end_checkout_time'],$ckkin['end']);
            $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);

            $item = new stdClass();
            $item->employee_name = $emp_data['employee_full_name'];
            $item->department = $emp_data['name'];
            $item->schedule_type = 'Temporary(' . $emp_sch_temp['name'] . ')';
            $item->date = $date;
            $item->work_hour = $emp_sch_temp['start_time'] . '-' . $emp_sch_temp['end_time'];
            $item->in = $dateTimeCheckin->first_checkin;
            $item->out = $dateTimeCheckout->last_checkout;
            $item->work_duration = $this->intervalWork($item->in, $item->out);
            $item->late=$this->calculateLateEarlyOut($date,$emp_sch_temp['start_time'], $item->in, $emp_sch_temp['end_time'], $item->out, $emp_sch_temp['late_minutes'], $emp_sch_temp['early_minutes'])['late_minutes'];
            $item->early_out=$this->calculateLateEarlyOut($date,$emp_sch_temp['start_time'], $item->in, $emp_sch_temp['end_time'], $item->out, $emp_sch_temp['late_minutes'], $emp_sch_temp['early_minutes'])['early_out_minutes'];
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

                $ckkout = $this->makeDateTime($date, $emp_used_class['start_checkout_time'], $emp_used_class['end_checkout_time'],$ckkin['end']);
                $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);

                $item = new stdClass();
                $item->employee_name = $emp_data['employee_full_name'];
                $item->department = $emp_data['name'];
                $item->schedule_type = 'Automatic(' . $emp_used_class['name'] . ')';
                $item->date = $date;
                $item->work_hour = $emp_used_class['start_time'] . '-' . $emp_used_class['end_time'];
                $item->in = $dateTimeCheckin->first_checkin;
                $item->out = $dateTimeCheckout->last_checkout;
                $item->work_duration = $this->intervalWork($item->in, $item->out);
                $item->late=$this->calculateLateEarlyOut($date,$emp_used_class['start_time'], $item->in, $emp_used_class['end_time'], $item->out, $emp_used_class['late_minutes'], $emp_used_class['early_minutes'])['late_minutes'];
                $item->early_out=$this->calculateLateEarlyOut($date,$emp_used_class['start_time'], $item->in, $emp_used_class['end_time'], $item->out, $emp_used_class['late_minutes'], $emp_used_class['early_minutes'])['early_out_minutes'];
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

                    $ckkout = $this->makeDateTime($date, $num_run['start_checkout_time'], $num_run['end_checkout_time'],$ckkin['end']);
                    $dateTimeCheckout = $this->Tbcheckinout_mobile_model->get_checkout($empid, $ckkout['start'], $ckkout['end']);
                    
                    $item = new stdClass();
                    $item->employee_name = $emp_data['employee_full_name'];
                    $item->department = $emp_data['name'];
                    $item->schedule_type = 'Schedule(' . $num_run['run_name'] . ')';
                    $item->date = $date;
                    $item->work_hour = $num_run['start_time'] . '-' . $num_run['end_time'];
                    $item->in = $dateTimeCheckin->first_checkin;
                    $item->out = $dateTimeCheckout->last_checkout;
                    $item->work_duration = $this->intervalWork($item->in, $item->out);
                    $item->late=$this->calculateLateEarlyOut($date,$num_run['start_time'], $item->in, $num_run['end_time'], $item->out, $num_run['late_minutes'], $num_run['early_minutes'])['late_minutes'];
                    $item->early_out=$this->calculateLateEarlyOut($date,$num_run['start_time'], $item->in, $num_run['end_time'], $item->out, $num_run['late_minutes'], $num_run['early_minutes'])['early_out_minutes'];
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
