
<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Attendance extends CI_Controller
{

    function generateReport()
    {
        $params = json_decode($this->input->post('params'));
        $params_employee = $params->employee;

        if (count($params_employee) == 0) {
            $arr_dpt = $this->getChildDepartement($params->departement);

            $dtemp = $this->attendance_model->getEmpInDept($this->appid, $arr_dpt);
            foreach ($dtemp as $d) {
                array_push($params_employee, $d->employee_id);
            }
        }

        // get interval date
        $interval_date = $this->get_date_range($params->start_date, $params->end_date);

        $data = [];

        foreach ($params_employee as $empID) {
            $dayOfWork = 0;
            $numberDayofwork = $this->attendance_model->getDayOfWork($empID);

            if ($numberDayofwork) {
                if ($numberDayofwork[0]->unit == 1) { // jadwal mingguan
                    foreach ($numberDayofwork as $nd) {
                        $getCountDay = $this->hitungHari($nd->sdays, $params->start_date, $params->end_date);
                        $dayOfWork += $getCountDay;
                    }
                } else { // jadwal bulanan
                    $dayOfWork = count($numberDayofwork);
                }
            }

            // get employee data
            $employee = $this->attendance_model->getEmployee($empID);

            if (count($employee) == 0) {
                continue; // skip proses generate report
            }

            foreach ($interval_date as $key => $date) {
                // cek shift temp
                $emp_sch_temp = $this->attendance_model->getEmpSchTemp($empID, $date);
                if (!empty($emp_sch_temp)) {

                    $data_sch_temp = [];

                    foreach ($emp_sch_temp as $keytemp => $item_sch) {

                        if ($date >= $item_sch->start_date && $date <= $item_sch->end_date) {
                            $get_sch = $this->attendance_model->getSchClassById($item_sch->schclass_id);

                            if (count($get_sch) == 0) {
                                continue;
                            }

                            $get_sch = $get_sch[0];
                            $get_sch->start_time = (new DateTime($get_sch->start_time))->format('H:i:s');
                            $get_sch->end_time = (new DateTime($get_sch->end_time))->format('H:i:s');
                            $get_sch->start_time_real = (new DateTime($get_sch->start_time))->format('H:i:s');
                            $get_sch->end_time_real = (new DateTime($get_sch->end_time))->format('H:i:s');
                            $get_sch->employee_name = $employee[0]->employee_full_name;
                            $get_sch->employee_account_no = $employee[0]->employee_account_no;
                            $get_sch->employee_id = $employee[0]->employee_id;
                            $get_sch->employee_account_number = $employee[0]->employee_account_no;
                            $get_sch->departement = $employee[0]->departement_name;
                            $get_sch->date = (new DateTime($date))->format('d-m-Y');
                            $get_sch->date_formated = (new DateTime($date))->format('Y-m-d');
                            $get_sch->type = '1';
                            $get_sch->schedule_type = $this->gtrans->line("Schedule Temporary ($get_sch->name)");
                            $get_sch->is_automatic = false;
                            $get_sch->interval_checkinout = '';
                            $get_sch->day_of_work = $dayOfWork;
                            $get_sch->is_leave = false;
                            $get_sch->count_leave = 0;
                            $get_sch->is_cuti = false;
                            $get_sch->is_absent = false;
                            $get_sch->overtime_id = [];
                            $get_sch->overtime_detail = [];
                            $get_sch->overtime_type = null;
                            $get_sch->overtime_duration = null;
                            $get_sch->overtime_status = null;
                            $get_sch->overtime_checkin = null;
                            $get_sch->overtime_checkout = null;
                            $get_sch->employee_checkin_time = null;
                            $get_sch->employee_checkout_time = null;
                            $get_sch->effective_work = null;
                            $get_sch->late_out = null;
                            $get_sch->checkin_break = null;
                            $get_sch->checkin_break_latitude = null;
                            $get_sch->checkin_break_longitude = null;
                            $get_sch->checkout_break = null;
                            $get_sch->checkout_break_latitude = null;
                            $get_sch->checkout_break_longitude = null;
                            $get_sch->early_break = null;
                            $get_sch->extanded_break = null;
                            $get_sch->early_in = null;
                            $get_sch->alpa_checkin = false;
                            $get_sch->is_holiday = false;
                            $get_sch->holiday_name = null;

                            $date_end_of_task = $date . ' ' . $get_sch->end_time;
                            $date_start_of_task = $date . ' ' . $get_sch->start_time;
                            $get_sch->end_of_task = (new DateTime($date_end_of_task));
                            $get_sch->start_of_task = (new DateTime($date_start_of_task));

                            $start_work = (new DateTime($get_sch->start_time_real))->format('H:i:s');
                            $end_work = (new DateTime($get_sch->end_time_real))->format('H:i:s');

                            $interval_start_work = new DateTime($start_work);
                            $interval_end_work = new DateTime($end_work);

                            $time_diff_work = $interval_start_work->diff($interval_end_work);
                            $interval_work = $time_diff_work->format('%H:%I:%S');

                            $get_sch->interval_work = $interval_work;

                            if (isset($get_sch->late_minutes) && $get_sch->late_minutes > 0) {
                                $get_sch->start_time = (new DateTime($get_sch->start_time))->add(new DateInterval('PT' . $get_sch->late_minutes . 'M'))->format('H:i:s');
                            }

                            if (isset($get_sch->early_minutes) && $get_sch->early_minutes > 0) {
                                $get_sch->end_time = (new DateTime($get_sch->end_time))->sub(new DateInterval('PT' . $get_sch->early_minutes . 'M'))->format('H:i:s');
                            }

                            $temp_is_change_day = false;
                            $flag_morning_diff_day = false;
                            $flag_checkout_diff_day = false;

                            $checkintime1 = (new DateTime($get_sch->start_checkin_time))->format('H:i:s');
                            $checkintime2 = (new DateTime($get_sch->end_checkin_time))->format('H:i:s');
                            $checkouttime1 = (new DateTime($get_sch->start_checkout_time))->format('H:i:s');
                            $checkouttime2 = (new DateTime($get_sch->end_checkout_time))->format('H:i:s');
                            $starttime = (new DateTime($get_sch->start_time))->format('H:i:s');

                            $ckin_start = null;
                            $ckin_end = null;
                            $ckout_start = null;
                            $ckout_end = null;
                            $break_start = null;
                            $break_end = null;

                            if ($checkintime1 <= $checkintime2) {
                                $ckin_start = "$date $checkintime1";
                                $ckin_end = "$date $checkintime2";
                                $break_start = $date . ' ' . $get_sch->start_time_real;

                                $ckout_start = "$date $checkouttime1";
                                if ($get_sch->end_time_real < $get_sch->start_time_real) {
                                    $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                    $break_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $get_sch->end_time_real;
                                    $flag_checkout_diff_day = true;

                                    $start = new DateTime($start_work);
                                    $end = new DateTime($end_work);

                                    if ($end < $start) $end->modify('+1 day');

                                    $get_sch->interval_work = $start->diff($end)->format('%H:%I:%S');
                                } else {
                                    $ckout_end = "$date $checkouttime2";
                                    $break_end = $date . ' ' . $get_sch->end_time_real;
                                }
                                if ($checkouttime2 < $get_sch->end_time_real) {
                                    $flag_checkout_diff_day = true;
                                    $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                }
                            } else {
                                if ($checkintime1 <= $starttime) {
                                    $ckin_start = "$date $checkintime1";
                                    $ckin_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkintime2;
                                    $break_start = $date . ' ' . $get_sch->start_time_real;

                                    $ckout_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $checkouttime1;
                                    $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                    $break_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $get_sch->end_time_real;

                                    $start = new DateTime($start_work);
                                    $end = new DateTime($end_work);

                                    if ($end < $start) $end->modify('+1 day');

                                    $get_sch->interval_work = $start->diff($end)->format('%H:%I:%S');
                                } else {
                                    $ckin_start = (new DateTime($date))->modify('-1 day')->format('Y-m-d') . ' ' . $checkintime1;
                                    $ckin_end = "$date $checkintime2";
                                    $break_start = (new DateTime($date))->modify('-1 day')->format('Y-m-d') . ' ' . $get_sch->start_time_real;

                                    $ckout_start = "$date $checkouttime1";
                                    $ckout_end = "$date $checkouttime2";
                                    $break_end = $date . ' ' . $get_sch->end_time_real;

                                    $flag_morning_diff_day = true;
                                }
                            }
                            $get_sch->start_checkintime = $ckin_start;
                            $get_sch->end_checkintime = $ckin_end;
                            $get_sch->start_checkouttime = $ckout_start;
                            $get_sch->end_checkouttime = $ckout_end;

                            // $date_sts = explode(' ', $ckio)
                            $ckHoliday = $this->schedule_model->ckHolidayByDate($this->appid, $date);
                            if ($ckHoliday) {
                                if (count($ckHoliday) == 1) {
                                    $get_sch->is_holiday = true;
                                    $get_sch->holiday_name = $ckHoliday[0]->name;
                                } else {
                                    $get_sch->is_holiday = true;
                                    $holiday_names = [];
                                    foreach ($ckHoliday as $ckh) {
                                        $holiday_names[] = $ckh->name;
                                    }
                                    $get_sch->holiday_name = implode(', ', $holiday_names);
                                }
                            }

                            $get_checkin = [];
                            $get_checkout = [];

                            // get checkin data
                            $get_checkin_mobile = $this->attendance_model->getCheckInOutBetween($empID, $ckin_start, $ckin_end, 'CheckIn');
                            if ($get_checkin_mobile) {
                                foreach ($get_checkin_mobile as $hris_in) {
                                    array_push($get_checkin, [
                                        'checklog_date' => $hris_in->checklog_date,
                                        'admin_in_reason' => $hris_in->reason
                                    ]);
                                }
                            }

                            $get_checkin_mechine = $this->attendance_model->getCheckInOutFromMechine($empID, $ckin_start, $ckin_end, '');
                            if ($get_checkin_mechine) {
                                foreach ($get_checkin_mechine as $mchine) {
                                    array_push($get_checkin, [
                                        'checklog_date' => $mchine->checkinout_datetime,
                                        'admin_in_reason' => ''
                                    ]);
                                }
                            }

                            if ($get_checkin) {
                                usort($get_checkin, function ($a, $b) {
                                    return strtotime($a['checklog_date']) - strtotime($b['checklog_date']);
                                });
                                $get_sch->employee_checkin_datetime = (new DateTime($get_checkin[0]['checklog_date']))->format('Y-m-d H:i:s');
                                $get_sch->employee_checkin_time = (new DateTime($get_checkin[0]['checklog_date']))->format('H:i:s');
                                $get_sch->admin_checkin_reason = $get_checkin[0]['admin_in_reason'];

                                // get interval checkin to start real time (jam masuk kerja)
                                $time_sts = $date . ' ' . $get_sch->start_time_real;
                                $get_sch->interval_ci_sts = $this->timeIntervalAbsolute($get_sch->employee_checkin_datetime, $time_sts);
                            }
                            // get checkout data
                            $get_checkout_mobile = $this->attendance_model->getCheckInOutBetween($empID, $ckout_start, $ckout_end, 'CheckOut');

                            if ($get_checkout_mobile) {
                                foreach ($get_checkout_mobile as $hris_out) {
                                    array_push($get_checkout, [
                                        'checklog_date' => $hris_out->checklog_date,
                                        'admin_out_reason' => $hris_out->reason
                                    ]);
                                }
                            }
                            $get_checkout_mechine = $this->attendance_model->getCheckInOutFromMechine($empID, $ckout_start, $ckout_end, '');
                            if ($get_checkout_mechine) {
                                foreach ($get_checkout_mechine as $mchine_out) {
                                    array_push($get_checkout, [
                                        'checklog_date' => $mchine_out->checkinout_datetime,
                                        'admin_out_reason' => ''
                                    ]);
                                }
                            }
                            if ($get_checkout) {
                                usort($get_checkout, function ($a, $b) {
                                    return strtotime($a['checklog_date']) - strtotime($b['checklog_date']);
                                });
                                $ckout_temp = end($get_checkout);
                                $get_sch->employee_checkout_datetime = (new DateTime($ckout_temp['checklog_date']))->format('Y-m-d H:i:s');
                                $get_sch->employee_checkout_time = (new DateTime($ckout_temp['checklog_date']))->format('H:i:s');
                                $get_sch->admin_checkout_reason = $ckout_temp['admin_out_reason'];

                                // get interval checkin to start real time (jam masuk kerja)
                                $time_ets = date('Y-m-d', strtotime($ckout_start)) . ' ' . $get_sch->end_time_real;
                                $get_sch->interval_co_sts = $this->timeIntervalAbsolute($get_sch->employee_checkout_datetime, $time_ets);
                            }

                            $interval_checkin = 0;

                            $leave_stime = date('H:i:s', strtotime($ckin_start));
                            $leave_etime = date('H:i:s', strtotime($ckin_end));
                            $leave_detail = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date);

                            if (isset($get_sch->employee_checkin_time)) {
                                $firstTime = $get_sch->employee_checkin_time;
                                $firstDateTime = new DateTime($firstTime);
                                $first_thresholdTime = new DateTime($firstDateTime->format('Y-m-d') . (new DateTime($get_sch->start_time))->format('H:i:s'));

                                if (new DateTime($get_sch->employee_checkin_datetime) < $get_sch->start_of_task) {
                                    $ci_datetime = new DateTime($get_sch->employee_checkin_datetime);
                                    $ci_start_of_task = $get_sch->start_of_task;
                                    $diff = $ci_start_of_task->diff($ci_datetime);
                                    $get_sch->early_in = $diff->format('%H:%I:%S');

                                    list($hours, $minutes, $seconds) = explode(':', $get_sch->early_in);
                                    $total_minutes = ceil(($hours * 60) + $minutes + ($seconds / 60));

                                    $interval_checkin = (int)$total_minutes;
                                }

                                if ($flag_morning_diff_day) {
                                    $ckin_datetime = new DateTime($get_sch->employee_checkin_datetime);
                                    $first_thresholdTime = new DateTime($date . (new DateTime($get_sch->start_time))->format('H:i:s'));

                                    if ($ckin_datetime > $first_thresholdTime) {
                                        $first_thresholdTime = new DateTime($ckin_datetime->format('Y-m-d') . (new DateTime($get_sch->start_time_real))->format('H:i:s'));
                                        $interval = $ckin_datetime->diff($first_thresholdTime);
                                        $get_sch->late_time = $interval->format('%H:%I:%S');
                                    } else {
                                        $get_sch->late_time = '';
                                    }
                                } else {
                                    if ($firstDateTime > $first_thresholdTime) {
                                        $first_thresholdTime = new DateTime($firstDateTime->format('Y-m-d') . (new DateTime($get_sch->start_time_real))->format('H:i:s'));
                                        $interval = $firstDateTime->diff($first_thresholdTime);
                                        $get_sch->late_time = $interval->format('%H:%I:%S');
                                    } else {
                                        $get_sch->late_time = '';
                                    }
                                }
                            } else {
                                if ($leave_detail) {
                                    $req_alpa_checkin = $this->attendance_model->getDataLeaveAlpaCheckin($this->appid, $empID, $date, $leave_stime, $leave_etime);
                                    if (!$req_alpa_checkin) {
                                        $get_sch->alpa_checkin = true;
                                    } else {
                                        $get_sch->alpa_checkin = false;
                                    }
                                    if (count($leave_detail) == 1) {
                                        if ($leave_detail[0]->form_type == 1) {
                                            $get_sch->alpa_checkin = false;
                                        }
                                    }
                                } else {
                                    $get_sch->alpa_checkin = true;
                                }
                            }

                            $interval_checkout = 0;

                            if (isset($get_sch->employee_checkout_time)) {
                                $lastTime = (new DateTime($get_sch->employee_checkout_time))->format('H:i');
                                $lastDateTime = new DateTime($lastTime);
                                $last_thresholdTime = new DateTime($lastDateTime->format('Y-m-d') . (new DateTime($get_sch->end_time))->format('H:i'));

                                if (new DateTime($get_sch->employee_checkout_datetime) > $get_sch->end_of_task) {
                                    $co_datetime = new DateTime($get_sch->employee_checkout_datetime);
                                    $co_end_of_task = $get_sch->end_of_task;
                                    $diff = $co_end_of_task->diff($co_datetime);
                                    $get_sch->late_out = $diff->format('%H:%I:%S');

                                    list($hours, $minutes, $seconds) = explode(':', $get_sch->late_out);
                                    $total_minutes = ceil(($hours * 60) + $minutes + ($seconds / 60));

                                    $interval_checkout = (int)$total_minutes;
                                }

                                if ($flag_checkout_diff_day) {
                                    $ckout_datetime = new DateTime($get_sch->employee_checkout_datetime);
                                    $dt_co_diff = (new DateTime($date))->modify('+1 day')->format('Y-m-d');

                                    if (date('A', strtotime($get_sch->end_time)) == 'PM') {
                                        $dt_co_diff = $date;
                                    }

                                    $last_thresholdTime = new DateTime($dt_co_diff . (new DateTime($get_sch->end_time))->format('H:i'));

                                    if ($ckout_datetime < $last_thresholdTime) {
                                        $dtonedaylatter = (new DateTime($date))->modify('+1 day')->format('Y-m-d');
                                        $last_thresholdTime = new DateTime($dtonedaylatter . (new DateTime($get_sch->end_time_real))->format('H:i'));
                                        $interval = $ckout_datetime->diff($last_thresholdTime);
                                        $get_sch->home_early = $interval->format('%H:%I');
                                    } else {
                                        $get_sch->home_early = '';
                                    }
                                } else {
                                    if ($lastDateTime < $last_thresholdTime) {
                                        $last_thresholdTime = new DateTime($lastDateTime->format('Y-m-d') . (new DateTime($get_sch->end_time_real))->format('H:i'));
                                        $interval = $lastDateTime->diff($last_thresholdTime);
                                        $get_sch->home_early = $interval->format('%H:%I');
                                    } else {
                                        $get_sch->home_early = '';
                                    }
                                }
                            }

                            $getOvertime = $this->overtime_model->getOVertimeAttendance($empID, $date);
                            if ($getOvertime) {
                                foreach ($getOvertime as $ovtkey => $ovt) {
                                    $ovtStart = new DateTime($ovt->start_date . ' ' . $ovt->start_time);
                                    $ovtEnd = new DateTime($ovt->end_date . ' ' . $ovt->end_time);
                                    $interval = $ovtStart->diff($ovtEnd);
                                    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                                    $get_sch->overtime_duration += $totalMinutes;

                                    if ($ovtkey == 0) {
                                        $get_sch->overtime_type = $ovt->type == 1 ? 'Lembur Awal' : 'Lembur Akhir';
                                    } else {
                                        $get_sch->overtime_type .= $ovt->type == 1 ? '</br>Lembur Awal' : '</br>Lembur Akhir';
                                    }

                                    $ovt_obj = [
                                        'ovt_duration' => $totalMinutes,
                                        'ovt_employee' => $employee[0]->employee_full_name,
                                        'ovt_checkin' => $ovt->checkin_time,
                                        'ovt_checkout' => $ovt->checkout_time,
                                        'ovt_type' => $ovt->type == 1 ? 'Lembur Awal (spl)' : 'Lembur Akhir (spl)',
                                        'ovt_start' => $ovt->start_date . ' ' . $ovt->start_time,
                                        'ovt_end' => $ovt->end_date . ' ' . $ovt->end_time,
                                        'ovt_status' => $ovt->status == 1 ? 'Approved' : 'Approved By Admin',
                                        'ovt_admin_reaon' => $ovt->admin_reason
                                    ];
                                    array_push($get_sch->overtime_id, $ovt->id);
                                    array_push($get_sch->overtime_detail, $ovt_obj);
                                }
                            } else if ($get_sch->overtime_start != 0 || $get_sch->overtime_end != 0) {
                                if ($get_sch->overtime_start != 0) {
                                    if ($interval_checkin > 0 && $interval_checkin >= $get_sch->overtime_start) {
                                        $get_sch->overtime_type = 'Lembur Awal (non spl)';
                                        $get_sch->overtime_duration = $interval_checkin;
                                        $ovt_obj = [
                                            'ovt_duration' => $interval_checkin,
                                            'ovt_employee' => $employee[0]->employee_full_name,
                                            'ovt_checkin' => '',
                                            'ovt_checkout' => '',
                                            'ovt_type' => 'Lembur Awal (non spl)',
                                            'ovt_start' => $get_sch->employee_checkin_datetime,
                                            'ovt_end' => $date . ' ' . $get_sch->start_time_real,
                                            'ovt_status' => 'Approved',
                                            'ovt_admin_reaon' => ''
                                        ];
                                        array_push($get_sch->overtime_detail, $ovt_obj);
                                    }
                                }
                                if ($get_sch->overtime_end != 0) {
                                    if ($interval_checkout > 0 && $interval_checkout >= $get_sch->overtime_end) {
                                        $get_sch->overtime_duration = $get_sch->overtime_duration + $interval_checkout;
                                        if ($get_sch->overtime_type) {
                                            $get_sch->overtime_type .= '</br>Lembur Akhir (non spl)';
                                        } else {
                                            $get_sch->overtime_type .= 'Lembur Akhir (non spl)';
                                        }
                                        $ovt_obj = [
                                            'ovt_duration' => $interval_checkout,
                                            'ovt_employee' => $employee[0]->employee_full_name,
                                            'ovt_checkin' => '',
                                            'ovt_checkout' => '',
                                            'ovt_type' => 'Lembur Akhir (non spl)',
                                            'ovt_start' => $date . ' ' . $get_sch->end_time_real,
                                            'ovt_end' => $get_sch->employee_checkout_datetime,
                                            'ovt_status' => 'Approved',
                                            'ovt_admin_reaon' => ''
                                        ];
                                        array_push($get_sch->overtime_detail, $ovt_obj);
                                    }
                                }
                            }

                            // cek leave late
                            $leave_late = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date, $leave_stime, $leave_etime);
                            if ($leave_late) {
                                $get_sch->leave_late = $this->encryption_org->encode($leave_late[0]->id);
                                $get_sch->is_leave = true;
                            } else {
                                $get_sch->leave_late = 0;
                            }

                            // cek leave home early
                            $co_leave_stime = date('H:i:s', strtotime($ckout_start));
                            $co_leave_etime = date('H:i:s', strtotime($ckout_end));
                            $leave_home_early = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date, $co_leave_stime, $co_leave_etime);

                            if ($leave_home_early) {
                                $get_sch->leave_home_early = $this->encryption_org->encode($leave_home_early[0]->id);
                                $get_sch->is_leave = true;
                            } else {
                                $get_sch->leave_home_early = 0;
                            }

                            if ($leave_detail) {
                                $arr_idleave = [];
                                $arr_idcats = [];
                                $arr_cats_name = [];
                                $leave_date = [];
                                $count_leave = 0;
                                $leave_cuti = false;
                                foreach ($leave_detail as $ld) {
                                    array_push($arr_idleave, $ld->id);
                                    array_push($arr_idcats, $ld->category_id);
                                    $cats_name = $ld->cats_name;
                                    if ($ld->start_time != '00:00:00') {
                                        $cats_name = $ld->cats_name . ' (' . date("H:i", strtotime($ld->start_time)) . ' - ' . date("H:i", strtotime($ld->end_time)) . ')';
                                    }
                                    array_push($arr_cats_name, $cats_name);
                                    if ($ld->is_cuti == '1') {
                                        if ($leave_cuti == false) {
                                            $leave_cuti = true;
                                        }
                                    } else {
                                        $count_leave = $count_leave + 1;
                                    }
                                    array_push($leave_date, [
                                        'id' => $ld->id,
                                        'employee_id' => $ld->employee_id,
                                        'start_date' => $ld->start_date,
                                        'end_date' => $ld->end_date
                                    ]);
                                }
                                $get_sch->leave_detail = implode(',', $arr_idleave);
                                $get_sch->leave_date = $leave_date;
                                $get_sch->is_leave = true;
                                $get_sch->count_leave = $count_leave;
                                $get_sch->is_cuti = $leave_cuti;
                                $get_sch->leave_cats_id = implode(',', $arr_idcats);
                                $get_sch->leave_cats_name = implode(',', $arr_cats_name);
                            } else {
                                $get_sch->leave_date = null;
                                $get_sch->is_cuti = false;
                                $get_sch->leave_detail = 0;
                                $get_sch->leave_cats_id = 0;
                                $get_sch->leave_cats_name = '';
                            }
                            if ($get_sch->break_type != '0') {
                                $breakInData = $this->attendance_model->getBreak($this->appid, $empID, $break_start, $break_end, 1, $get_sch->id);
                                if ($breakInData) {
                                    $get_sch->checkin_break = $breakInData[0]->break_date;
                                    $get_sch->checkin_break_latitude = $breakInData[0]->latitude;
                                    $get_sch->checkin_break_longitude = $breakInData[0]->longitude;
                                    if ($get_sch->break_type == '2') {
                                        $b_in = $date . ' ' . $get_sch->break_in;
                                        $ci_break = $breakInData[0]->break_date;
                                        if ($ci_break < $b_in) {
                                            $interval_break = $this->calculate_range_break($ci_break, $b_in);
                                            $get_sch->early_break = $interval_break . 'm';
                                        }
                                    }
                                }
                                $breakOutData = $this->attendance_model->getBreak($this->appid, $empID, $break_start, $break_end, 2, $get_sch->id);
                                if ($breakOutData) {
                                    $get_sch->checkout_break = $breakOutData[0]->break_date;
                                    $get_sch->checkout_break_latitude = $breakOutData[0]->latitude;
                                    $get_sch->checkout_break_longitude = $breakOutData[0]->longitude;

                                    if ($get_sch->break_type == '1' && !empty($breakInData)) {
                                        $ci_break = $breakInData[0]->break_date;
                                        $co_break = $breakOutData[0]->break_date;
                                        $interval_break = $this->calculate_range_break($ci_break, $co_break);
                                        if ($interval_break > $get_sch->break_duration) {
                                            $get_sch->extanded_break = $interval_break - $get_sch->break_duration . 'm';
                                        }
                                    } elseif ($get_sch->break_type == '2' && !empty($breakInData)) {
                                        $b_out = (new DateTime($break_end))->format('Y-m-d') . ' ' . $get_sch->break_out;
                                        $co_break = $breakOutData[0]->break_date;

                                        if ((new DateTime($co_break))->format('H:i:s') > (new DateTime($b_out))->format('H:i:s')) {
                                            $interval_break = $this->calculate_range_break((new DateTime($b_out))->format('H:i:s'), (new DateTime($co_break))->format('H:i:s'));
                                            $get_sch->extanded_break = $interval_break . 'm';
                                        }
                                    }
                                }
                            }

                            if (isset($get_sch->employee_checkin_time) && isset($get_sch->employee_checkout_time)) {
                                // $interval_start_ckio = $get_sch->employee_checkin_time;
                                $get_sch->interval_checkinout = $this->interval_format($get_sch->employee_checkin_time, $get_sch->employee_checkout_time);

                                $s_effective_work = null;
                                if ($get_sch->start_time_real > $get_sch->employee_checkin_time) {
                                    $s_effective_work = $get_sch->start_time_real;
                                } else {
                                    $s_effective_work = $get_sch->employee_checkin_time;
                                }
                                $get_sch->effective_work = $this->interval_format($s_effective_work, $get_sch->employee_checkout_time);
                            }

                            $get_sch->start_time = (new DateTime($get_sch->start_time))->format('H:i:s');
                            $get_sch->end_time = (new DateTime($get_sch->end_time))->format('H:i:s');

                            if (count($get_checkin) !== 0 || count($get_checkout) !== 0) {
                                if (count($data_sch_temp) === 0) {
                                    if ($this->appid == 'IA01M185288F20250611445') {
                                        if (count($get_checkin) == 0 || count($get_checkout) == 0) {
                                            $get_sch->workday = '0.5';
                                        }
                                    }
                                    array_push($data_sch_temp, $get_sch);
                                } else {
                                    if ($keytemp == 1 && $data_sch_temp[0]->is_absent == true) {
                                        $data_sch_temp = [$get_sch];
                                    } else {
                                        if ($this->appid == 'IA01M185288F20250611445') {
                                            if (count($get_checkin) == 0 || count($get_checkout) == 0) {
                                                $get_sch->workday = '0.5';
                                            }
                                        }
                                        $compare = $this->compareDataInOut($data_sch_temp, $get_sch, $keytemp);
                                        $data_sch_temp = $compare;
                                    }
                                }
                            } else {
                                $result = array_filter($data_sch_temp, function ($obj) use ($date, $empID) {
                                    return $obj->date_formated === $date && $obj->employee_id === $empID;
                                });
                                if (empty($result)) {
                                    $get_sch->is_absent = true;
                                    array_push($data_sch_temp, $get_sch);
                                }
                            }
                        }
                    }

                    foreach ($data_sch_temp as $fixdata) {
                        array_push($data, $fixdata);
                    }
                } else {
                    // cek apakah karyawan mempunyai jadwal otomatis
                    $emp_used_class = $this->attendance_model->getUserUseClassByEmpId($empID);

                    if (count($emp_used_class) != 0) { // proses untuk mendapatkan data report jadwal otomatis
                        $schclass_used = $this->get_schclass_by_shift_auto($emp_used_class);

                        $data_temp = [];

                        foreach ($schclass_used as $keyused => $item) {
                            $found_sch = $this->attendance_model->getSchClassById($item->id);

                            if ($found_sch) {
                                $found_sch[0]->start_time_real = $found_sch[0]->start_time;
                                $found_sch[0]->end_time_real = $found_sch[0]->end_time;
                                if (isset($found_sch[0]->late_minutes) && $found_sch[0]->late_minutes > 0) {
                                    $found_sch[0]->start_time = (new DateTime($found_sch[0]->start_time))->add(new DateInterval('PT' . $found_sch[0]->late_minutes . 'M'))->format('H:i:s');
                                }

                                if (isset($found_sch[0]->early_minutes) && $found_sch[0]->early_minutes > 0) {
                                    $found_sch[0]->end_time = (new DateTime($found_sch[0]->end_time))->sub(new DateInterval('PT' . $found_sch[0]->early_minutes . 'M'))->format('H:i:s');
                                }

                                $flag_morning_diff_day = false;
                                $flag_checkout_diff_day = false;

                                $checkintime1 = (new DateTime($found_sch[0]->start_checkin_time))->format('H:i:s');
                                $checkintime2 = (new DateTime($found_sch[0]->end_checkin_time))->format('H:i:s');
                                $checkouttime1 = (new DateTime($found_sch[0]->start_checkout_time))->format('H:i:s');
                                $checkouttime2 = (new DateTime($found_sch[0]->end_checkout_time))->format('H:i:s');
                                $starttime = (new DateTime($found_sch[0]->start_time))->format('H:i:s');

                                $schclass_used[$keyused]->interval_work = $this->interval_format($found_sch[0]->start_time_real, $found_sch[0]->end_time_real);

                                $ckin_start = null;
                                $ckin_end = null;
                                $ckout_start = null;
                                $ckout_end = null;
                                $break_start = null;
                                $break_end = null;

                                if ($checkintime1 <= $checkintime2) {
                                    $ckin_start = "$date $checkintime1";
                                    $ckin_end = "$date $checkintime2";
                                    $break_start = $date . ' ' . $found_sch[0]->start_time_real;

                                    $ckout_start = "$date $checkouttime1";
                                    if ($found_sch[0]->end_time_real < $found_sch[0]->start_time_real) {
                                        $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                        $break_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $found_sch[0]->end_time_real;
                                        $flag_checkout_diff_day = true;

                                        $start = new DateTime($found_sch[0]->start_time_real);
                                        $end = new DateTime($found_sch[0]->end_time_real);

                                        if ($end < $start) $end->modify('+1 day');

                                        $schclass_used[$keyused]->interval_work = $start->diff($end)->format('%H:%I:%S');
                                    } else {
                                        $ckout_end = "$date $checkouttime2";
                                        $break_end = $date . ' ' . $found_sch[0]->end_time_real;
                                    }
                                    if ($checkouttime2 < $found_sch[0]->end_time_real) {
                                        $flag_checkout_diff_day = true;
                                        $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                    }
                                } else {
                                    if ($checkintime1 <= $starttime) {
                                        $ckin_start = "$date $checkintime1";
                                        $ckin_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkintime2;
                                        $break_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $found_sch[0]->start_time_real;

                                        $ckout_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $checkouttime1;
                                        $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                        $break_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $found_sch[0]->end_time_real;

                                        $start = new DateTime($found_sch[0]->start_time_real);
                                        $end = new DateTime($found_sch[0]->end_time_real);

                                        if ($end < $start) $end->modify('+1 day');

                                        $schclass_used[$keyused]->interval_work = $start->diff($end)->format('%H:%I:%S');
                                    } else {
                                        $ckin_start = (new DateTime($date))->modify('-1 day')->format('Y-m-d') . ' ' . $checkintime1;
                                        $ckin_end = "$date $checkintime2";
                                        $break_start = (new DateTime($date))->modify('-1 day')->format('Y-m-d') . ' ' . $found_sch[0]->start_time_real;

                                        $ckout_start = "$date $checkouttime1";
                                        $ckout_end = "$date $checkouttime2";
                                        $break_end = $date . ' ' . $found_sch[0]->end_time_real;

                                        $flag_morning_diff_day = true;
                                    }
                                }

                                $get_checkin = [];
                                $get_checkout = [];

                                // get checkin data
                                $get_checkin_mobile = $this->attendance_model->getCheckInOutBetween($empID, $ckin_start, $ckin_end, 'CheckIn');
                                if ($get_checkin_mobile) {
                                    foreach ($get_checkin_mobile as $hris_in) {
                                        array_push($get_checkin, [
                                            'checklog_date' => $hris_in->checklog_date,
                                            'admin_in_reason' => $hris_in->reason
                                        ]);
                                    }
                                }

                                $get_checkin_mechine = $this->attendance_model->getCheckInOutFromMechine($empID, $ckin_start, $ckin_end, '');
                                if ($get_checkin_mechine) {
                                    foreach ($get_checkin_mechine as $mchine) {
                                        array_push($get_checkin, [
                                            'checklog_date' => $mchine->checkinout_datetime,
                                            'admin_in_reason' => ''
                                        ]);
                                    }
                                }

                                $schclass_used[$keyused]->type = '2';
                                $schclass_used[$keyused]->schedule_type = $this->gtrans->line('Automatic') . ' (' . $schclass_used[$keyused]->name . ')';
                                $schclass_used[$keyused]->departement = $employee[0]->departement_name;
                                $schclass_used[$keyused]->date = (new DateTime($date))->format('d-m-Y');
                                $schclass_used[$keyused]->date_formated = (new DateTime($date))->format('Y-m-d');
                                $schclass_used[$keyused]->employee_name = $employee[0]->employee_full_name;
                                $schclass_used[$keyused]->employee_id = $employee[0]->employee_id;
                                $schclass_used[$keyused]->employee_id = $employee[0]->employee_id;
                                $schclass_used[$keyused]->employee_account_number = $employee[0]->employee_account_no;
                                $schclass_used[$keyused]->is_automatic = true;
                                $schclass_used[$keyused]->day_of_work = $dayOfWork;
                                $schclass_used[$keyused]->is_leave = false;
                                $schclass_used[$keyused]->count_leave = 0;
                                $schclass_used[$keyused]->is_cuti = false;
                                $schclass_used[$keyused]->is_absent = false;
                                $schclass_used[$keyused]->overtime_id = [];
                                $schclass_used[$keyused]->overtime_detail = [];
                                $schclass_used[$keyused]->overtime_type = null;
                                $schclass_used[$keyused]->overtime_duration = null;
                                $schclass_used[$keyused]->overtime_status = null;
                                $schclass_used[$keyused]->overtime_checkin = null;
                                $schclass_used[$keyused]->overtime_checkout = null;
                                $schclass_used[$keyused]->interval_checkinout = '';
                                $schclass_used[$keyused]->employee_checkin_time = null;
                                $schclass_used[$keyused]->employee_checkout_time = null;
                                $schclass_used[$keyused]->late_out = null;
                                $schclass_used[$keyused]->effective_work = null;
                                $schclass_used[$keyused]->start_time = (new DateTime($schclass_used[$keyused]->start_time))->format('H:i:s');
                                $schclass_used[$keyused]->end_time = (new DateTime($schclass_used[$keyused]->end_time))->format('H:i:s');
                                $schclass_used[$keyused]->start_time_real = $found_sch[0]->start_time_real;
                                $schclass_used[$keyused]->end_time_real = $found_sch[0]->end_time_real;
                                $schclass_used[$keyused]->start_checkintime = $ckin_start;
                                $schclass_used[$keyused]->end_checkintime = $ckin_end;
                                $schclass_used[$keyused]->start_checkouttime = $ckout_start;
                                $schclass_used[$keyused]->end_checkouttime = $ckout_end;
                                $date_end_of_task = $date . ' ' . $schclass_used[$keyused]->end_time;
                                $date_start_of_task = $date . ' ' . $schclass_used[$keyused]->start_time;
                                $schclass_used[$keyused]->end_of_task = (new DateTime($date_end_of_task));
                                $schclass_used[$keyused]->start_of_task = (new DateTime($date_start_of_task));
                                $schclass_used[$keyused]->checkin_break = null;
                                $schclass_used[$keyused]->checkin_break_latitude = null;
                                $schclass_used[$keyused]->checkin_break_longitude = null;
                                $schclass_used[$keyused]->checkout_break = null;
                                $schclass_used[$keyused]->checkout_break_latitude = null;
                                $schclass_used[$keyused]->checkout_break_longitude = null;
                                $schclass_used[$keyused]->early_break = null;
                                $schclass_used[$keyused]->extanded_break = null;
                                $schclass_used[$keyused]->early_in = null;
                                $schclass_used[$keyused]->alpa_checkin = false;
                                $schclass_used[$keyused]->is_holiday = false;
                                $schclass_used[$keyused]->holiday_name = null;

                                $ckHoliday = $this->schedule_model->ckHolidayByDate($this->appid, $date);
                                if ($ckHoliday) {
                                    if (count($ckHoliday) == 1) {
                                        $schclass_used[$keyused]->is_holiday = true;
                                        $schclass_used[$keyused]->holiday_name = $ckHoliday[0]->name;
                                    } else {
                                        $schclass_used[$keyused]->is_holiday = true;
                                        $holiday_names = [];
                                        foreach ($ckHoliday as $ckh) {
                                            $holiday_names[] = $ckh->name;
                                        }
                                        $schclass_used[$keyused]->holiday_name = implode(', ', $holiday_names);
                                    }
                                }

                                if ($get_checkin) {
                                    usort($get_checkin, function ($a, $b) {
                                        return strtotime($a['checklog_date']) - strtotime($b['checklog_date']);
                                    });
                                    $schclass_used[$keyused]->employee_checkin_datetime = (new DateTime($get_checkin[0]['checklog_date']))->format('Y-m-d H:i:s');
                                    $schclass_used[$keyused]->employee_checkin_time = (new DateTime($get_checkin[0]['checklog_date']))->format('H:i:s');
                                    $schclass_used[$keyused]->admin_checkin_reason = $get_checkin[0]['admin_in_reason'];

                                    // get interval checkin to start real time (jam masuk kerja)
                                    $time_sts = $date . ' ' . $schclass_used[$keyused]->start_time_real;
                                    $schclass_used[$keyused]->interval_ci_sts = $this->timeIntervalAbsolute($schclass_used[$keyused]->employee_checkin_datetime, $time_sts);
                                    // echo json_encode($schclass_used[$keyused]->interval_ci_sts); return;
                                }
                                // get checkout data
                                $get_checkout_mobile = $this->attendance_model->getCheckInOutBetween($empID, $ckout_start, $ckout_end, 'CheckOut');
                                // if ($keyused > 0) {
                                //     echo $get_checkout_mobile;
                                //     return;
                                // }
                                if ($get_checkout_mobile) {
                                    foreach ($get_checkout_mobile as $hris_out) {
                                        array_push($get_checkout, [
                                            'checklog_date' => $hris_out->checklog_date,
                                            'admin_out_reason' => $hris_out->reason
                                        ]);
                                    }
                                }
                                $get_checkout_mechine = $this->attendance_model->getCheckInOutFromMechine($empID, $ckout_start, $ckout_end, '');
                                if ($get_checkout_mechine) {
                                    foreach ($get_checkout_mechine as $mchine_out) {
                                        array_push($get_checkout, [
                                            'checklog_date' => $mchine_out->checkinout_datetime,
                                            'admin_out_reason' => ''
                                        ]);
                                    }
                                }

                                if ($get_checkout) {
                                    usort($get_checkout, function ($a, $b) {
                                        return strtotime($a['checklog_date']) - strtotime($b['checklog_date']);
                                    });
                                    $ckout_auto = end($get_checkout);
                                    $schclass_used[$keyused]->employee_checkout_datetime = (new DateTime($ckout_auto['checklog_date']))->format('Y-m-d H:i:s');
                                    $schclass_used[$keyused]->employee_checkout_time = (new DateTime($ckout_auto['checklog_date']))->format('H:i:s');
                                    $schclass_used[$keyused]->admin_checkout_reason = $ckout_auto['admin_out_reason'];
                                    // get interval checkin to start real time (jam masuk kerja)
                                    $time_ets = date('Y-m-d', strtotime($ckout_start)) . ' ' . $schclass_used[$keyused]->end_time_real;
                                    $schclass_used[$keyused]->interval_co_sts = $this->timeIntervalAbsolute($schclass_used[$keyused]->employee_checkout_datetime, $time_ets);
                                }

                                $interval_checkin = 0;

                                $leave_stime = date('H:i:s', strtotime($ckin_start));
                                $leave_etime = date('H:i:s', strtotime($ckin_end));
                                $leave_detail = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date);

                                if (isset($schclass_used[$keyused]->employee_checkin_time)) {
                                    // print_r($schclass_used[$keyused]->start_of_task);
                                    // return;
                                    $firstTime = $schclass_used[$keyused]->employee_checkin_time;
                                    $firstDateTime = new DateTime($firstTime);
                                    $first_thresholdTime = new DateTime($firstDateTime->format('Y-m-d') . (new DateTime($found_sch[0]->start_time))->format('H:i:s'));

                                    if (new DateTime($schclass_used[$keyused]->employee_checkin_datetime) < $schclass_used[$keyused]->start_of_task) {
                                        $ci_datetime = new DateTime($schclass_used[$keyused]->employee_checkin_datetime);
                                        $ci_start_of_task = $schclass_used[$keyused]->start_of_task;
                                        $diff = $ci_start_of_task->diff($ci_datetime);
                                        $schclass_used[$keyused]->early_in = $diff->format('%H:%I:%S');

                                        list($hours, $minutes, $seconds) = explode(':', $schclass_used[$keyused]->early_in);
                                        $total_minutes = ceil(($hours * 60) + $minutes + ($seconds / 60));

                                        $interval_checkin = (int)$total_minutes;
                                    }

                                    if ($flag_morning_diff_day) {
                                        $ckin_datetime = new DateTime($schclass_used[$keyused]->employee_checkin_datetime);
                                        $first_thresholdTime = new DateTime($date . (new DateTime($found_sch[0]->start_time))->format('H:i:s'));

                                        if ($ckin_datetime > $first_thresholdTime) {
                                            $first_thresholdTime = new DateTime($ckin_datetime->format('Y-m-d') . (new DateTime($found_sch[0]->start_time_real))->format('H:i:s'));
                                            $interval = $ckin_datetime->diff($first_thresholdTime);
                                            $schclass_used[$keyused]->late_time = $interval->format('%H:%I:%S');
                                        } else {
                                            $schclass_used[$keyused]->late_time = '';
                                        }
                                    } else {
                                        if ($firstDateTime > $first_thresholdTime) {
                                            $first_thresholdTime = new DateTime($firstDateTime->format('Y-m-d') . (new DateTime($found_sch[0]->start_time_real))->format('H:i:s'));
                                            $interval = $firstDateTime->diff($first_thresholdTime);
                                            $schclass_used[$keyused]->late_time = $interval->format('%H:%I:%S');
                                        } else {
                                            $schclass_used[$keyused]->late_time = '';
                                        }
                                    }
                                } else {
                                    if ($leave_detail) {
                                        $req_alpa_checkin = $this->attendance_model->getDataLeaveAlpaCheckin($this->appid, $empID, $date, $leave_stime, $leave_etime);
                                        if (!$req_alpa_checkin) {
                                            $schclass_used[$keyused]->alpa_checkin = true;
                                        } else {
                                            $schclass_used[$keyused]->alpa_checkin = false;
                                        }
                                        if (count($leave_detail) == 1) {
                                            if ($leave_detail[0]->form_type == 1) {
                                                $schclass_used[$keyused]->alpa_checkin = false;
                                            }
                                        }
                                    } else {
                                        $schclass_used[$keyused]->alpa_checkin = true;
                                    }
                                }

                                $interval_checkout = 0;

                                if (isset($schclass_used[$keyused]->employee_checkout_time)) {

                                    $lastTime = (new DateTime($schclass_used[$keyused]->employee_checkout_time))->format('H:i');
                                    $lastDateTime = new DateTime($lastTime);
                                    $last_thresholdTime = new DateTime($lastDateTime->format('Y-m-d') . (new DateTime($found_sch[0]->end_time))->format('H:i'));

                                    if (new DateTime($schclass_used[$keyused]->employee_checkout_datetime) > $schclass_used[$keyused]->end_of_task) {
                                        $co_datetime = new DateTime($schclass_used[$keyused]->employee_checkout_datetime);
                                        $co_end_of_task = $schclass_used[$keyused]->end_of_task;
                                        $diff = $co_end_of_task->diff($co_datetime);
                                        $schclass_used[$keyused]->late_out = $diff->format('%H:%I:%S');

                                        list($hours, $minutes, $seconds) = explode(':', $schclass_used[$keyused]->late_out);
                                        $total_minutes = ceil(($hours * 60) + $minutes + ($seconds / 60));

                                        $interval_checkout = (int)$total_minutes;
                                    }
                                    if ($flag_checkout_diff_day) {

                                        $ckout_datetime = new DateTime($schclass_used[$keyused]->employee_checkout_datetime);
                                        $dt_co_diff = (new DateTime($date))->modify('+1 day')->format('Y-m-d');

                                        if (date('A', strtotime($found_sch[0]->end_time)) == 'PM') {
                                            $dt_co_diff = $date;
                                        }

                                        $last_thresholdTime = new DateTime($dt_co_diff . (new DateTime($found_sch[0]->end_time))->format('H:i'));

                                        if ($ckout_datetime < $last_thresholdTime) {
                                            $dtonedaylatter = (new DateTime($date))->modify('+1 day')->format('Y-m-d');
                                            $last_thresholdTime = new DateTime($dtonedaylatter . (new DateTime($found_sch[0]->end_time_real))->format('H:i'));
                                            $interval = $ckout_datetime->diff($last_thresholdTime);
                                            $schclass_used[$keyused]->home_early = $interval->format('%H:%I');
                                        } else {
                                            $schclass_used[$keyused]->home_early = '';
                                        }
                                    } else {
                                        if ($lastDateTime < $last_thresholdTime) {
                                            $last_thresholdTime = new DateTime($lastDateTime->format('Y-m-d') . (new DateTime($found_sch[0]->end_time_real))->format('H:i'));
                                            $interval = $lastDateTime->diff($last_thresholdTime);
                                            $schclass_used[$keyused]->home_early = $interval->format('%H:%I');
                                        } else {
                                            $schclass_used[$keyused]->home_early = '';
                                        }
                                    }
                                }

                                $getOvertime = $this->overtime_model->getOVertimeAttendance($empID, $date);
                                if ($getOvertime) {
                                    foreach ($getOvertime as $ovtkey => $ovt) {
                                        $ovtStart = new DateTime($ovt->start_date . ' ' . $ovt->start_time);
                                        $ovtEnd = new DateTime($ovt->end_date . ' ' . $ovt->end_time);
                                        $interval = $ovtStart->diff($ovtEnd);
                                        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                                        $schclass_used[$keyused]->overtime_duration += $totalMinutes;

                                        if ($ovtkey == 0) {
                                            $schclass_used[$keyused]->overtime_type = $ovt->type == 1 ? 'Lembur Awal' : 'Lembur Akhir';
                                        } else {
                                            $schclass_used[$keyused]->overtime_type .= $ovt->type == 1 ? '</br>Lembur Awal' : '</br>Lembur Akhir';
                                        }

                                        $ovt_obj = [
                                            'ovt_duration' => $totalMinutes,
                                            'ovt_employee' => $employee[0]->employee_full_name,
                                            'ovt_checkin' => $ovt->checkin_time,
                                            'ovt_checkout' => $ovt->checkout_time,
                                            'ovt_type' => $ovt->type == 1 ? 'Lembur Awal (spl)' : 'Lembur Akhir (spl)',
                                            'ovt_start' => $ovt->start_date . ' ' . $ovt->start_time,
                                            'ovt_end' => $ovt->end_date . ' ' . $ovt->end_time,
                                            'ovt_status' => $ovt->status == 1 ? 'Approved' : 'Approved By Admin',
                                            'ovt_admin_reaon' => $ovt->admin_reason
                                        ];
                                        array_push($schclass_used[$keyused]->overtime_id, $ovt->id);
                                        array_push($schclass_used[$keyused]->overtime_detail, $ovt_obj);
                                    }
                                } else if ($schclass_used[$keyused]->overtime_start != 0 || $schclass_used[$keyused]->overtime_end != 0) {
                                    if ($schclass_used[$keyused]->overtime_start != 0) {
                                        if ($interval_checkin > 0 && $interval_checkin >= $schclass_used[$keyused]->overtime_start) {
                                            $schclass_used[$keyused]->overtime_type = 'Lembur Awal (non spl)';
                                            $schclass_used[$keyused]->overtime_duration = $interval_checkin;
                                            $ovt_obj = [
                                                'ovt_duration' => $interval_checkin,
                                                'ovt_employee' => $employee[0]->employee_full_name,
                                                'ovt_checkin' => '',
                                                'ovt_checkout' => '',
                                                'ovt_type' => 'Lembur Awal (non spl)',
                                                'ovt_start' => $schclass_used[$keyused]->employee_checkin_datetime,
                                                'ovt_end' => $date . ' ' . $schclass_used[$keyused]->start_time_real,
                                                'ovt_status' => 'Approved',
                                                'ovt_admin_reaon' => ''
                                            ];
                                            array_push($schclass_used[$keyused]->overtime_detail, $ovt_obj);
                                        }
                                    }
                                    if ($schclass_used[$keyused]->overtime_end != 0) {
                                        if ($interval_checkout > 0 && $interval_checkout >= $schclass_used[$keyused]->overtime_end) {
                                            $schclass_used[$keyused]->overtime_duration = $schclass_used[$keyused]->overtime_duration + $interval_checkout;
                                            if ($schclass_used[$keyused]->overtime_type) {
                                                $schclass_used[$keyused]->overtime_type .= '</br>Lembur Akhir (non spl)';
                                            } else {
                                                $schclass_used[$keyused]->overtime_type .= 'Lembur Akhir (non spl)';
                                            }
                                            $ovt_obj = [
                                                'ovt_duration' => $interval_checkout,
                                                'ovt_employee' => $employee[0]->employee_full_name,
                                                'ovt_checkin' => '',
                                                'ovt_checkout' => '',
                                                'ovt_type' => 'Lembur Akhir (non spl)',
                                                'ovt_start' => $date . ' ' . $schclass_used[$keyused]->end_time_real,
                                                'ovt_end' => $schclass_used[$keyused]->employee_checkout_datetime,
                                                'ovt_status' => 'Approved',
                                                'ovt_admin_reaon' => ''
                                            ];
                                            array_push($schclass_used[$keyused]->overtime_detail, $ovt_obj);
                                        }
                                    }
                                }

                                // cek leave late
                                $leave_late = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date, $leave_stime, $leave_etime);
                                if ($leave_late) {
                                    $schclass_used[$keyused]->leave_late = $this->encryption_org->encode($leave_late[0]->id);
                                    $schclass_used[$keyused]->is_leave = true;
                                } else {
                                    $schclass_used[$keyused]->leave_late = 0;
                                }

                                // cek leave home early
                                $co_leave_stime = date('H:i:s', strtotime($ckout_start));
                                $co_leave_etime = date('H:i:s', strtotime($ckout_end));
                                $leave_home_early = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date, $co_leave_stime, $co_leave_etime);

                                if ($leave_home_early) {
                                    $schclass_used[$keyused]->leave_home_early = $this->encryption_org->encode($leave_home_early[0]->id);
                                    $schclass_used[$keyused]->is_leave = true;
                                } else {
                                    $schclass_used[$keyused]->leave_home_early = 0;
                                }

                                if ($leave_detail) {
                                    $arr_idleave = [];
                                    $arr_idcats = [];
                                    $arr_cats_name = [];
                                    $leave_date = [];
                                    $count_leave = 0;
                                    $leave_cuti = false;
                                    foreach ($leave_detail as $ld) {
                                        array_push($arr_idleave, $ld->id);
                                        array_push($arr_idcats, $ld->category_id);
                                        $cats_name = $ld->cats_name;
                                        if ($ld->start_time != '00:00:00') {
                                            $cats_name = $ld->cats_name . ' (' . date("H:i", strtotime($ld->start_time)) . ' - ' . date("H:i", strtotime($ld->end_time)) . ')';
                                        }
                                        array_push($arr_cats_name, $cats_name);
                                        if ($ld->is_cuti == '1') {
                                            if ($leave_cuti == false) {
                                                $leave_cuti = true;
                                            }
                                        } else {
                                            $count_leave = $count_leave + 1;
                                        }
                                        array_push($leave_date, [
                                            'id' => $ld->id,
                                            'employee_id' => $ld->employee_id,
                                            'start_date' => $ld->start_date,
                                            'end_date' => $ld->end_date
                                        ]);
                                    }
                                    $schclass_used[$keyused]->leave_detail = implode(',', $arr_idleave);
                                    $schclass_used[$keyused]->leave_date = $leave_date;
                                    $schclass_used[$keyused]->is_leave = true;
                                    $schclass_used[$keyused]->count_leave = $count_leave;
                                    $schclass_used[$keyused]->is_cuti = $leave_cuti;
                                    $schclass_used[$keyused]->leave_cats_id = implode(',', $arr_idcats);
                                    $schclass_used[$keyused]->leave_cats_name = implode(',', $arr_cats_name);
                                } else {
                                    $schclass_used[$keyused]->is_cuti = false;
                                    $schclass_used[$keyused]->leave_detail = 0;
                                    $schclass_used[$keyused]->leave_date = null;
                                    $schclass_used[$keyused]->leave_cats_id = 0;
                                    $schclass_used[$keyused]->leave_cats_name = '';
                                }

                                if ($found_sch[0]->break_type != '0') {
                                    $breakInData = $this->attendance_model->getBreak($this->appid, $empID, $break_start, $break_end, 1, $schclass_used[$keyused]->id);
                                    if ($breakInData) {
                                        $schclass_used[$keyused]->checkin_break = $breakInData[0]->break_date;
                                        $schclass_used[$keyused]->checkin_break_latitude = $breakInData[0]->latitude;
                                        $schclass_used[$keyused]->checkin_break_longitude = $breakInData[0]->longitude;
                                        if ($found_sch[0]->break_type == '2') {
                                            $b_in = $date . ' ' . $found_sch[0]->break_in;
                                            $ci_break = $breakInData[0]->break_date;
                                            if ($ci_break < $b_in) {
                                                $interval_break = $this->calculate_range_break($ci_break, $b_in);
                                                $schclass_used[$keyused]->early_break = $interval_break . 'm';
                                            }
                                        }
                                    }
                                    $breakOutData = $this->attendance_model->getBreak($this->appid, $empID, $break_start, $break_end, 2, $schclass_used[$keyused]->id);
                                    if ($breakOutData) {
                                        $schclass_used[$keyused]->checkout_break = $breakOutData[0]->break_date;
                                        $schclass_used[$keyused]->checkout_break_latitude = $breakOutData[0]->latitude;
                                        $schclass_used[$keyused]->checkout_break_longitude = $breakOutData[0]->longitude;

                                        if ($found_sch[0]->break_type == '1' && !empty($breakInData)) {
                                            $ci_break = $breakInData[0]->break_date;
                                            $co_break = $breakOutData[0]->break_date;
                                            $interval_break = $this->calculate_range_break($ci_break, $co_break);
                                            if ($interval_break > $found_sch[0]->break_duration) {
                                                $schclass_used[$keyused]->extanded_break = $interval_break - $found_sch[0]->break_duration . 'm';
                                            }
                                        } elseif ($found_sch[0]->break_type == '2' && !empty($breakInData)) {
                                            // $b_out = $date . ' ' . $found_sch[0]->break_out;
                                            $b_out = (new DateTime($break_end))->format('Y-m-d') . ' ' . $found_sch[0]->break_out;
                                            $co_break = $breakOutData[0]->break_date;
                                            if ($co_break > $b_out) {
                                                $interval_break = $this->calculate_range_break($b_out, $co_break);
                                                $schclass_used[$keyused]->extanded_break = $interval_break . 'm';
                                            }
                                        }
                                    }
                                }

                                if (isset($schclass_used[$keyused]->employee_checkin_time) && isset($schclass_used[$keyused]->employee_checkout_time)) {
                                    $schclass_used[$keyused]->interval_checkinout = $this->interval_format($schclass_used[$keyused]->employee_checkin_time, $schclass_used[$keyused]->employee_checkout_time);

                                    $s_effective_work = null;
                                    if ($schclass_used[$keyused]->start_time_real > $schclass_used[$keyused]->employee_checkin_time) {
                                        $s_effective_work = $schclass_used[$keyused]->start_time_real;
                                    } else {
                                        $s_effective_work = $schclass_used[$keyused]->employee_checkin_time;
                                    }
                                    $schclass_used[$keyused]->effective_work = $this->interval_format($s_effective_work, $schclass_used[$keyused]->employee_checkout_time);
                                }

                                if (count($get_checkin) !== 0 || count($get_checkout) !== 0) {
                                    if (count($data_temp) == 0) {
                                        array_push($data_temp, $schclass_used[$keyused]);
                                    } else {
                                        if ($keyused == 1 && $data_temp[0]->is_absent == true) {
                                            if ($this->appid == 'IA01M185288F20250611445') {
                                                if (count($get_checkin) == 0 || count($get_checkout) == 0) {
                                                    $schclass_used[$keyused]->workday = '0.5';
                                                }
                                            }
                                            $data_temp = [$schclass_used[$keyused]];
                                        } else {
                                            if ($this->appid == 'IA01M185288F20250611445') {
                                                if (count($get_checkin) == 0 || count($get_checkout) == 0) {
                                                    $schclass_used[$keyused]->workday = '0.5';
                                                }
                                            }
                                            $compare = $this->compareDataInOut($data_temp, $schclass_used[$keyused], $keyused);
                                            $data_temp = $compare;
                                        }
                                    }
                                } else {
                                    $result = array_filter($data_temp, function ($obj) use ($date, $empID) {
                                        return $obj->date_formated === $date && $obj->employee_id === $empID;
                                    });

                                    if (empty($result)) {
                                        $schclass_used[$keyused]->is_absent = true;
                                        array_push($data_temp, $schclass_used[$keyused]);
                                    }
                                }
                            }
                        }

                        foreach ($data_temp as $fix_data) {
                            array_push($data, $fix_data);
                        }
                    }

                    // get data emp of shift
                    $emp_of_run = $this->attendance_model->getEmpOfRun($empID);
                    if (count($emp_of_run) != 0 && $emp_of_run[0]->end_date < $date) { // proses untuk mendapatkan data report terjadwal
                        $N_day_of_week = date('N', strtotime($date));
                        // get shift
                        $num_run = $this->attendance_model->getNumRunById($emp_of_run[0]->num_of_run_id);

                        if (count($num_run) == 0) {
                            continue;
                        }
                        // get num run deil
                        if ($num_run[0]->unit == 2) {
                            $N_day_of_week = date('d', strtotime($date)); // get number day of month
                        }

                        $numrun_deil = $this->attendance_model->getNumRunDeil($num_run[0]->id, $N_day_of_week);
                        if (count($numrun_deil) == 0) {
                            continue;
                        }

                        foreach ($numrun_deil as $keydeil => $ndeil) {
                            // get employee schedule class
                            $get_sch = $this->attendance_model->getScheduleClassById($numrun_deil[$keydeil]->schclass_id);

                            if (count($get_sch) == 0) {
                                continue;
                            }

                            $get_sch = $get_sch[0];
                            $get_sch->start_time = (new DateTime($get_sch->start_time))->format('H:i:s');
                            $get_sch->end_time = (new DateTime($get_sch->end_time))->format('H:i:s');
                            $get_sch->start_time_real = (new DateTime($get_sch->start_time))->format('H:i:s');
                            $get_sch->end_time_real = (new DateTime($get_sch->end_time))->format('H:i:s');
                            $get_sch->employee_name = $employee[0]->employee_full_name;
                            $get_sch->employee_id = $employee[0]->employee_id;
                            $get_sch->employee_account_number = $employee[0]->employee_account_no;
                            $get_sch->departement = $employee[0]->departement_name;
                            $get_sch->date = (new DateTime($date))->format('d-m-Y');
                            $get_sch->date_formated = (new DateTime($date))->format('Y-m-d');
                            $get_sch->type = '3';
                            $get_sch->schedule_type = $this->gtrans->line("Scheduled") . ' (' . $num_run[0]->name . ')';
                            $get_sch->is_automatic = false;
                            $get_sch->interval_checkinout = '';
                            $get_sch->day_of_work = $dayOfWork;
                            $get_sch->is_leave = false;
                            $get_sch->count_leave = 0;
                            $get_sch->is_cuti = false;
                            $get_sch->is_absent = false;
                            $get_sch->overtime_id = [];
                            $get_sch->overtime_detail = [];
                            $get_sch->overtime_type = null;
                            $get_sch->overtime_duration = null;
                            $get_sch->overtime_status = null;
                            $get_sch->overtime_checkin = null;
                            $get_sch->overtime_checkout = null;
                            $get_sch->employee_checkin_time = null;
                            $get_sch->employee_checkout_time = null;
                            $get_sch->effective_work = null;
                            $get_sch->late_out = null;
                            $get_sch->checkin_break = null;
                            $get_sch->checkin_break_latitude = null;
                            $get_sch->checkin_break_longitude = null;
                            $get_sch->checkout_break = null;
                            $get_sch->checkout_break_latitude = null;
                            $get_sch->checkout_break_longitude = null;
                            $get_sch->early_break = null;
                            $get_sch->extanded_break = null;
                            $get_sch->early_in = null;
                            $get_sch->alpa_checkin = false;
                            $get_sch->is_holiday = false;
                            $get_sch->holiday_name = null;

                            $date_end_of_task = $date . ' ' . $get_sch->end_time;
                            $date_start_of_task = $date . ' ' . $get_sch->start_time;
                            $get_sch->end_of_task = (new DateTime($date_end_of_task));
                            $get_sch->start_of_task = (new DateTime($date_start_of_task));

                            // start calculate
                            $start_work = (new DateTime($get_sch->start_time_real))->format('H:i:s');
                            $end_work = (new DateTime($get_sch->end_time_real))->format('H:i:s');

                            $interval_start_work = new DateTime($start_work);
                            $interval_end_work = new DateTime($end_work);

                            $time_diff_work = $interval_start_work->diff($interval_end_work);
                            $interval_work = $time_diff_work->format('%H:%I:%S');

                            $get_sch->interval_work = $interval_work;

                            if (isset($get_sch->late_minutes) && $get_sch->late_minutes > 0) {
                                $get_sch->start_time = (new DateTime($get_sch->start_time))->add(new DateInterval('PT' . $get_sch->late_minutes . 'M'))->format('H:i:s');
                            }

                            if (isset($get_sch->early_minutes) && $get_sch->early_minutes > 0) {
                                $get_sch->end_time = (new DateTime($get_sch->end_time))->sub(new DateInterval('PT' . $get_sch->early_minutes . 'M'))->format('H:i:s');
                            }

                            $sch_is_change_day = false;
                            $flag_morning_diff_day = false;
                            $flag_checkout_diff_day = false;

                            $checkintime1 = (new DateTime($get_sch->start_checkin_time))->format('H:i:s');
                            $checkintime2 = (new DateTime($get_sch->end_checkin_time))->format('H:i:s');
                            $checkouttime1 = (new DateTime($get_sch->start_checkout_time))->format('H:i:s');
                            $checkouttime2 = (new DateTime($get_sch->end_checkout_time))->format('H:i:s');
                            $starttime = (new DateTime($get_sch->start_time))->format('H:i:s');

                            $ckin_start = null;
                            $ckin_end = null;
                            $ckout_start = null;
                            $ckout_end = null;
                            $break_start = null;
                            $break_end = null;

                            if ($checkintime1 <= $checkintime2) {
                                $ckin_start = "$date $checkintime1";
                                $ckin_end = "$date $checkintime2";
                                $break_start = $date . ' ' . $get_sch->start_time_real;

                                $ckout_start = "$date $checkouttime1";

                                if ($get_sch->end_time_real < $get_sch->start_time_real) {
                                    $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                    $break_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $get_sch->end_time_real;
                                    $flag_checkout_diff_day = true;

                                    $start = new DateTime($start_work);
                                    $end = new DateTime($end_work);

                                    if ($end < $start) $end->modify('+1 day');

                                    $get_sch->interval_work = $start->diff($end)->format('%H:%I:%S');
                                } else {
                                    $ckout_end = "$date $checkouttime2";
                                    $break_end = $date . ' ' . $get_sch->end_time_real;
                                }
                                if ($checkouttime2 < $get_sch->end_time_real) {
                                    $flag_checkout_diff_day = true;
                                    $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                }
                            } else {
                                if ($checkintime1 <= $starttime) {
                                    $ckin_start = "$date $checkintime1";
                                    $ckin_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkintime2;
                                    $break_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $get_sch->start_time_real;

                                    $ckout_start = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $checkouttime1;
                                    $ckout_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' .  $checkouttime2;
                                    $break_end = (new DateTime($date))->modify('+1 day')->format('Y-m-d') . ' ' . $get_sch->end_time_real;

                                    $start = new DateTime($start_work);
                                    $end = new DateTime($end_work);

                                    if ($end < $start) $end->modify('+1 day');

                                    $get_sch->interval_work = $start->diff($end)->format('%H:%I:%S');
                                } else {
                                    // untuk condisi else ini masih perlu di pastikan akan masuk shift mana
                                    $ckin_start = (new DateTime($date))->modify('-1 day')->format('Y-m-d') . ' ' . $checkintime1;
                                    $ckin_end = "$date $checkintime2";
                                    $break_start = $date . ' ' . $get_sch->start_time_real;

                                    $ckout_start = "$date $checkouttime1";
                                    $ckout_end = "$date $checkouttime2";
                                    $break_end = $date . ' ' . $get_sch->end_time_real;

                                    $flag_morning_diff_day = true;
                                }
                            }
                            $ckHoliday = $this->schedule_model->ckHolidayByDate($this->appid, $date);
                            if ($ckHoliday) {
                                if (count($ckHoliday) == 1) {
                                    $get_sch->is_holiday = true;
                                    $get_sch->holiday_name = $ckHoliday[0]->name;
                                } else {
                                    $get_sch->is_holiday = true;
                                    $holiday_names = [];
                                    foreach ($ckHoliday as $ckh) {
                                        $holiday_names[] = $ckh->name;
                                    }
                                    $get_sch->holiday_name = implode(', ', $holiday_names);
                                }
                            }

                            $get_checkin = [];
                            $get_checkout = [];
                            // get checkin data
                            $get_checkin_mobile = $this->attendance_model->getCheckInOutBetween($empID, $ckin_start, $ckin_end, 'CheckIn');

                            if ($get_checkin_mobile) {
                                foreach ($get_checkin_mobile as $hris_in) {
                                    array_push($get_checkin, [
                                        'checklog_date' => $hris_in->checklog_date,
                                        'admin_in_reason' => $hris_in->reason
                                    ]);
                                }
                            }

                            // echo json_encode($ckin_start . ' : ' . $ckin_end);
                            // return;
                            $get_checkin_mechine = $this->attendance_model->getCheckInOutFromMechine($empID, $ckin_start, $ckin_end, '');
                            if ($get_checkin_mechine) {
                                foreach ($get_checkin_mechine as $mchine_in) {
                                    array_push($get_checkin, [
                                        'checklog_date' => $mchine_in->checkinout_datetime,
                                        'admin_in_reason' => ''
                                    ]);
                                }
                            }

                            if ($get_checkin) {
                                usort($get_checkin, function ($a, $b) {
                                    return strtotime($a['checklog_date']) - strtotime($b['checklog_date']);
                                });
                                $get_sch->employee_checkin_datetime = (new DateTime($get_checkin[0]['checklog_date']))->format('Y-m-d H:i:s');
                                $get_sch->employee_checkin_time = (new DateTime($get_checkin[0]['checklog_date']))->format('H:i:s');
                                $get_sch->admin_checkin_reason = $get_checkin[0]['admin_in_reason'];
                            }

                            // get checkout data
                            $get_checkout_mobile = $this->attendance_model->getCheckInOutBetween($empID, $ckout_start, $ckout_end, 'CheckOut');
                            if ($get_checkout_mobile) {
                                foreach ($get_checkout_mobile as $hris_out) {
                                    array_push($get_checkout, [
                                        'checklog_date' => $hris_out->checklog_date,
                                        'admin_out_reason' => $hris_out->reason
                                    ]);
                                }
                            }

                            $get_checkout_mechine = $this->attendance_model->getCheckInOutFromMechine($empID, $ckout_start, $ckout_end, '');
                            if ($get_checkout_mechine) {
                                foreach ($get_checkout_mechine as $mchine_out) {
                                    array_push($get_checkout, [
                                        'checklog_date' => $mchine_out->checkinout_datetime,
                                        'admin_out_reason' => ''
                                    ]);
                                }
                            }

                            if ($get_checkout) {
                                usort($get_checkout, function ($a, $b) {
                                    return strtotime($a['checklog_date']) - strtotime($b['checklog_date']);
                                });
                                $ckout_sch = end($get_checkout);
                                $get_sch->employee_checkout_datetime = (new DateTime($ckout_sch['checklog_date']))->format('Y-m-d H:i:s');
                                $get_sch->employee_checkout_time = (new DateTime($ckout_sch['checklog_date']))->format('H:i:s');
                                $get_sch->admin_checkout_reason = $ckout_sch['admin_out_reason'];
                            }

                            $interval_checkin = 0;

                            $leave_stime = date('H:i:s', strtotime($ckin_start));
                            $leave_etime = date('H:i:s', strtotime($ckin_end));
                            $leave_detail = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date);

                            if (isset($get_sch->employee_checkin_time)) {
                                $firstTime = $get_sch->employee_checkin_time;
                                $firstDateTime = new DateTime($firstTime);
                                $first_thresholdTime = new DateTime($firstDateTime->format('Y-m-d') . (new DateTime($get_sch->start_time))->format('H:i:s'));

                                if (new DateTime($get_sch->employee_checkin_datetime) < $get_sch->start_of_task) {
                                    $ci_datetime = new DateTime($get_sch->employee_checkin_datetime);
                                    $ci_start_of_task = $get_sch->start_of_task;
                                    $diff = $ci_start_of_task->diff($ci_datetime);
                                    $get_sch->early_in = $diff->format('%H:%I:%S');

                                    list($hours, $minutes, $seconds) = explode(':', $get_sch->early_in);
                                    $total_minutes = ceil(($hours * 60) + $minutes + ($seconds / 60));

                                    $interval_checkin = (int)$total_minutes;
                                }

                                if ($flag_morning_diff_day) {
                                    $ckin_datetime = new DateTime($get_sch->employee_checkin_datetime);
                                    $first_thresholdTime = new DateTime($date . (new DateTime($get_sch->start_time))->format('H:i:s'));

                                    if ($ckin_datetime > $first_thresholdTime) {
                                        $first_thresholdTime = new DateTime($ckin_datetime->format('Y-m-d') . (new DateTime($get_sch->start_time_real))->format('H:i:s'));
                                        $interval = $ckin_datetime->diff($first_thresholdTime);
                                        $get_sch->late_time = $interval->format('%H:%I:%S');
                                    } else {
                                        $get_sch->late_time = '';
                                    }
                                } else {
                                    if ($firstDateTime > $first_thresholdTime) {
                                        $first_thresholdTime = new DateTime($firstDateTime->format('Y-m-d') . (new DateTime($get_sch->start_time_real))->format('H:i:s'));
                                        $interval = $firstDateTime->diff($first_thresholdTime);
                                        $get_sch->late_time = $interval->format('%H:%I:%S');
                                    } else {
                                        $get_sch->late_time = '';
                                    }
                                }
                            } else { // cek data alpa checkin
                                if ($leave_detail) {
                                    $req_alpa_checkin = $this->attendance_model->getDataLeaveAlpaCheckin($this->appid, $empID, $date, $leave_stime, $leave_etime);
                                    if (!$req_alpa_checkin) {
                                        $get_sch->alpa_checkin = true;
                                    } else {
                                        $get_sch->alpa_checkin = false;
                                    }
                                    if (count($leave_detail) == 1) {
                                        if ($leave_detail[0]->form_type == 1) {
                                            $get_sch->alpa_checkin = false;
                                        }
                                    }
                                } else {
                                    $get_sch->alpa_checkin = true;
                                }
                            }

                            $interval_checkout = 0;

                            if (isset($get_sch->employee_checkout_time)) {

                                $lastTime = (new DateTime($get_sch->employee_checkout_time))->format('H:i');
                                $lastDateTime = new DateTime($lastTime);
                                $last_thresholdTime = new DateTime($lastDateTime->format('Y-m-d') . (new DateTime($get_sch->end_time))->format('H:i'));

                                if (new DateTime($get_sch->employee_checkout_datetime) > $get_sch->end_of_task) {
                                    $co_datetime = new DateTime($get_sch->employee_checkout_datetime);
                                    $co_end_of_task = $get_sch->end_of_task;
                                    $diff = $co_end_of_task->diff($co_datetime);
                                    $get_sch->late_out = $diff->format('%H:%I:%S');

                                    list($hours, $minutes, $seconds) = explode(':', $get_sch->late_out);
                                    $total_minutes = ceil(($hours * 60) + $minutes + ($seconds / 60));

                                    $interval_checkout = (int)$total_minutes;
                                }

                                if ($flag_checkout_diff_day) {
                                    $ckout_datetime = new DateTime($get_sch->employee_checkout_datetime);
                                    $dt_co_diff = (new DateTime($date))->modify('+1 day')->format('Y-m-d');

                                    if (date('A', strtotime($get_sch->end_time)) == 'PM') {
                                        $dt_co_diff = $date;
                                    }

                                    $last_thresholdTime = new DateTime($dt_co_diff . (new DateTime($get_sch->end_time))->format('H:i'));

                                    if ($ckout_datetime < $last_thresholdTime) {
                                        $dtonedaylatter = (new DateTime($date))->modify('+1 day')->format('Y-m-d');
                                        $last_thresholdTime = new DateTime($dtonedaylatter . (new DateTime($get_sch->end_time_real))->format('H:i'));
                                        $interval = $ckout_datetime->diff($last_thresholdTime);
                                        $get_sch->home_early = $interval->format('%H:%I');
                                    } else {
                                        $get_sch->home_early = '';
                                    }
                                } else {
                                    if ($lastDateTime < $last_thresholdTime) {
                                        $last_thresholdTime = new DateTime($lastDateTime->format('Y-m-d') . (new DateTime($get_sch->end_time_real))->format('H:i'));
                                        $interval = $lastDateTime->diff($last_thresholdTime);
                                        $get_sch->home_early = $interval->format('%H:%I');
                                    } else {
                                        $get_sch->home_early = '';
                                    }
                                }
                            }

                            $getOvertime = $this->overtime_model->getOVertimeAttendance($empID, $date);
                            if ($getOvertime) {
                                foreach ($getOvertime as $ovtkey => $ovt) {
                                    $ovtStart = new DateTime($ovt->start_date . ' ' . $ovt->start_time);
                                    $ovtEnd = new DateTime($ovt->end_date . ' ' . $ovt->end_time);
                                    $interval = $ovtStart->diff($ovtEnd);
                                    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                                    $get_sch->overtime_duration += $totalMinutes;

                                    if ($ovtkey == 0) {
                                        $get_sch->overtime_type = $ovt->type == 1 ? 'Lembur Awal (spl)' : 'Lembur Akhir (spl)';
                                    } else {
                                        $get_sch->overtime_type .= $ovt->type == 1 ? '</br>Lembur Awal (spl)' : '</br>Lembur Akhir (spl)';
                                    }

                                    $ovt_obj = [
                                        'ovt_duration' => $totalMinutes,
                                        'ovt_employee' => $employee[0]->employee_full_name,
                                        'ovt_checkin' => $ovt->checkin_time,
                                        'ovt_checkout' => $ovt->checkout_time,
                                        'ovt_type' => $ovt->type == 1 ? 'Lembur Awal (spl)' : 'Lembur Akhir (spl)',
                                        'ovt_start' => $ovt->start_date . ' ' . $ovt->start_time,
                                        'ovt_end' => $ovt->end_date . ' ' . $ovt->end_time,
                                        'ovt_status' => $ovt->status == 1 ? 'Approved' : 'Approved By Admin',
                                        'ovt_admin_reaon' => $ovt->admin_reason
                                    ];
                                    array_push($get_sch->overtime_id, $ovt->id);
                                    array_push($get_sch->overtime_detail, $ovt_obj);
                                }
                            } else if ($get_sch->overtime_start != 0 || $get_sch->overtime_end != 0) {
                                if ($get_sch->overtime_start != 0) {
                                    if ($interval_checkin > 0 && $interval_checkin >= $get_sch->overtime_start) {
                                        $get_sch->overtime_type = 'Lembur Awal (non spl)';
                                        $get_sch->overtime_duration = $interval_checkin;
                                        $ovt_obj = [
                                            'ovt_duration' => $interval_checkin,
                                            'ovt_employee' => $employee[0]->employee_full_name,
                                            'ovt_checkin' => '',
                                            'ovt_checkout' => '',
                                            'ovt_type' => 'Lembur Awal (non spl)',
                                            'ovt_start' => $get_sch->employee_checkin_datetime,
                                            'ovt_end' => $date . ' ' . $get_sch->start_time_real,
                                            'ovt_status' => 'Approved',
                                            'ovt_admin_reaon' => ''
                                        ];
                                        array_push($get_sch->overtime_detail, $ovt_obj);
                                    }
                                }
                                if ($get_sch->overtime_end != 0) {
                                    if ($interval_checkout > 0 && $interval_checkout >= $get_sch->overtime_end) {
                                        $get_sch->overtime_duration = $get_sch->overtime_duration + $interval_checkout;
                                        if ($get_sch->overtime_type) {
                                            $get_sch->overtime_type .= '</br>Lembur Akhir (non spl)';
                                        } else {
                                            $get_sch->overtime_type .= 'Lembur Akhir (non spl)';
                                        }
                                        $ovt_obj = [
                                            'ovt_duration' => $interval_checkout,
                                            'ovt_employee' => $employee[0]->employee_full_name,
                                            'ovt_checkin' => '',
                                            'ovt_checkout' => '',
                                            'ovt_type' => 'Lembur Akhir (non spl)',
                                            'ovt_start' => $date . ' ' . $get_sch->end_time_real,
                                            'ovt_end' => $get_sch->employee_checkout_datetime,
                                            'ovt_status' => 'Approved',
                                            'ovt_admin_reaon' => ''
                                        ];
                                        array_push($get_sch->overtime_detail, $ovt_obj);
                                    }
                                }
                            }
                            // cek leave late
                            $leave_late = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date, $leave_stime, $leave_etime);
                            if ($leave_late) {
                                $get_sch->leave_late = $this->encryption_org->encode($leave_late[0]->id);
                                $get_sch->is_leave = true;
                            } else {
                                $get_sch->leave_late = 0;
                            }

                            // cek leave home early
                            $co_leave_stime = date('H:i:s', strtotime($ckout_start));
                            $co_leave_etime = date('H:i:s', strtotime($ckout_end));
                            $leave_home_early = $this->attendance_model->checkleaveOneDay($this->appid, $empID, $date, $co_leave_stime, $co_leave_etime);

                            if ($leave_home_early) {
                                $get_sch->leave_home_early = $this->encryption_org->encode($leave_home_early[0]->id);
                                $get_sch->is_leave = true;
                            } else {
                                $get_sch->leave_home_early = 0;
                            }

                            if ($leave_detail) {
                                $arr_idleave = [];
                                $arr_idcats = [];
                                $arr_cats_name = [];
                                $count_leave = 0;
                                $leave_date = [];
                                $leave_cuti = false;
                                foreach ($leave_detail as $ld) {
                                    array_push($arr_idleave, $ld->id);
                                    array_push($arr_idcats, $ld->category_id);
                                    $cats_name = $ld->cats_name;
                                    if ($ld->start_time != '00:00:00') {
                                        $cats_name = $ld->cats_name . ' (' . date("H:i", strtotime($ld->start_time)) . ' - ' . date("H:i", strtotime($ld->end_time)) . ')';
                                    }
                                    array_push($arr_cats_name, $cats_name);
                                    if ($ld->is_cuti == '1') {
                                        if ($leave_cuti == false) {
                                            $leave_cuti = true;
                                        }
                                    } else {
                                        $count_leave = $count_leave + 1;
                                    }
                                    array_push($leave_date, [
                                        'id' => $ld->id,
                                        'employee_id' => $ld->employee_id,
                                        'start_date' => $ld->start_date,
                                        'end_date' => $ld->end_date
                                    ]);
                                }
                                $get_sch->leave_detail = implode(',', $arr_idleave);
                                $get_sch->leave_date = $leave_date;
                                $get_sch->is_leave = true;
                                $get_sch->count_leave = $count_leave;
                                $get_sch->is_cuti = $leave_cuti;
                                $get_sch->leave_cats_id = implode(',', $arr_idcats);
                                $get_sch->leave_cats_name = implode(',', $arr_cats_name);
                            } else {
                                $get_sch->is_cuti = false;
                                $get_sch->leave_date = null;
                                $get_sch->leave_detail = 0;
                                $get_sch->leave_cats_id = 0;
                                $get_sch->leave_cats_name = '';
                            }

                            if ($get_sch->break_type != '0') {
                                $breakInData = $this->attendance_model->getBreak($this->appid, $empID, $break_start, $break_end, 1, $get_sch->id);

                                if ($breakInData) {
                                    $get_sch->checkin_break = $breakInData[0]->break_date;
                                    $get_sch->checkin_break_latitude = $breakInData[0]->latitude;
                                    $get_sch->checkin_break_longitude = $breakInData[0]->longitude;
                                    if ($get_sch->break_type == '2') {
                                        $b_in = $date . ' ' . $get_sch->break_in;
                                        $ci_break = $breakInData[0]->break_date;
                                        if ($ci_break < $b_in) {
                                            $interval_break = $this->calculate_range_break($ci_break, $b_in);
                                            $get_sch->early_break = $interval_break . 'm';
                                        }
                                    }
                                }
                                $breakOutData = $this->attendance_model->getBreak($this->appid, $empID, $break_start, $break_end, 2, $get_sch->id);
                                if ($breakOutData) {
                                    $get_sch->checkout_break = $breakOutData[0]->break_date;
                                    $get_sch->checkout_break_latitude = $breakOutData[0]->latitude;
                                    $get_sch->checkout_break_longitude = $breakOutData[0]->longitude;

                                    if ($get_sch->break_type == '1' && !empty($breakInData)) {
                                        $ci_break = $breakInData[0]->break_date;
                                        $co_break = $breakOutData[0]->break_date;
                                        $interval_break = $this->calculate_range_break($ci_break, $co_break);
                                        if ($interval_break > $get_sch->break_duration) {
                                            $get_sch->extanded_break = $interval_break - $get_sch->break_duration . 'm';
                                        }
                                    } elseif ($get_sch->break_type == '2' && !empty($breakInData)) {
                                        $b_out = (new DateTime($break_end))->format('Y-m-d') . ' ' . $get_sch->break_out;
                                        $co_break = $breakOutData[0]->break_date;
                                        if ($co_break > $b_out) {
                                            $interval_break = $this->calculate_range_break($b_out, $co_break);
                                            $get_sch->extanded_break = $interval_break . 'm';
                                        }
                                    }
                                }
                            }

                            if (isset($get_sch->employee_checkin_time) && isset($get_sch->employee_checkout_time)) {
                                $get_sch->interval_checkinout = $this->interval_format($get_sch->employee_checkin_time, $get_sch->employee_checkout_time);

                                $s_effective_work = null;
                                if ($get_sch->start_time_real > $get_sch->employee_checkin_time) {
                                    $s_effective_work = $get_sch->start_time_real;
                                } else {
                                    $s_effective_work = $get_sch->employee_checkin_time;
                                }
                                $get_sch->effective_work = $this->interval_format($s_effective_work, $get_sch->employee_checkout_time);
                            }

                            $get_sch->start_time = (new DateTime($get_sch->start_time))->format('H:i:s');
                            $get_sch->end_time = (new DateTime($get_sch->end_time))->format('H:i:s');

                            if (count($get_checkin) !== 0 || count($get_checkout) !== 0) {
                                $result = array_filter($data, function ($obj) use ($date, $empID) {
                                    return $obj->date_formated === $date && $obj->employee_id === $empID;
                                });
                                // echo json_encode($result[0]->employee_id); return;
                                if (!empty($result)) {
                                    if ($result[0]->type == '3') {
                                        if ($this->appid == 'IA01M185288F20250611445') {
                                            if (count($get_checkin) == 0 || count($get_checkout) == 0) {
                                                $get_sch->workday = '0.5';
                                            }
                                        }
                                        array_push($data, $get_sch);
                                    }
                                } else {
                                    if ($this->appid == 'IA01M185288F20250611445') {
                                        if (count($get_checkin) == 0 || count($get_checkout) == 0) {
                                            $get_sch->workday = '0.5';
                                        }
                                    }
                                    array_push($data, $get_sch);
                                }
                            } else {
                                $result = array_filter($data, function ($obj) use ($date, $empID) {
                                    return $obj->date_formated === $date && $obj->employee_id === $empID;
                                });
                                if (empty($result)) {
                                    $get_sch->is_absent = true;
                                    array_push($data, $get_sch);
                                }
                            }
                        }
                    }
                }
            }
        }

        echo $this->setResponse(200, 'Success get data', $data);
        return;
    }
}
