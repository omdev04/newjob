<?php

namespace App\Http\Controllers\Admin;

use App\ApplicationStatus;
use App\Helper\Reply;

use App\Http\Requests\InterviewSchedule\StoreRequest;
use App\Http\Requests\InterviewSchedule\UpdateRequest;
use App\InterviewSchedule;
use App\InterviewScheduleEmployee;
use App\JobApplication;
use App\Notifications\CandidateNotify;
use App\Notifications\CandidateReminder;
use App\Notifications\CandidateScheduleInterview;
use App\Notifications\EmployeeResponse;
use App\Notifications\ScheduleInterview;
use App\Notifications\ScheduleInterviewStatus;
use App\Notifications\ScheduleStatusCandidate;
use App\ScheduleComments;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\CandidateRejected;
use Illuminate\Support\Arr;

class InterviewScheduleController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'menu.interviewSchedule';
        $this->pageIcon = 'icon-calender';
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|mixed
     * @throws \Throwable
     */
    public function index(Request $request)
    {
        abort_if(! $this->user->cans('view_schedule'), 403);

        $currentDate = Carbon::now($this->global->timezone)->format('Y-m-d'); // Current Date

        // Get All schedules
        $this->schedules = InterviewSchedule::
            select('id', 'job_application_id', 'schedule_date', 'status')
            ->with(['employees', 'jobApplication:id,job_id,full_name', 'jobApplication.job:id,title'])
            ->where('status', 'pending')
            ->orderBy('schedule_date')
            ->get();

        // Filter upcoming schedule
        $upComingSchedules = $this->schedules->filter(function ($value, $key)use($currentDate) {
            return $value->schedule_date >= $currentDate;
        });

        $upcomingData = [];

        // Set array for upcoming schedule
        foreach($upComingSchedules as $upComingSchedule){
            $dt = $upComingSchedule->schedule_date->format('Y-m-d');
            $upcomingData[$dt][] = $upComingSchedule;
        }

        $this->upComingSchedules = $upcomingData;

        if($request->ajax()){
            $viewData = view('admin.interview-schedule.upcoming-schedule', $this->data)->render();
            return Reply::dataOnly(['data' => $viewData, 'scheduleData' => $this->schedules]);
        }

        return view('admin.interview-schedule.index', $this->data);
    }


    /**
     * @param Request $request
     * @return string
     * @throws \Throwable
     */
    public function create(Request $request){
        abort_if(! $this->user->cans('add_schedule'), 403);
        $this->candidates = JobApplication::all();
        $this->users = User::with('company')->company()->get();
        $this->scheduleDate = $request->date;
        return view('admin.interview-schedule.create', $this->data)->render();
    }

    /**
     * @param Request $request
     * @return string
     * @throws \Throwable
     */
    public function table(Request $request){
        abort_if(! $this->user->cans('add_schedule'), 403);
        $this->candidates = JobApplication::all();
        $this->users = User::with('company')->company()->get();
        return view('admin.interview-schedule.table', $this->data);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function data(Request $request){
        abort_if(! $this->user->cans('view_schedule'), 403);

        $shedule = InterviewSchedule::select('interview_schedules.id','job_applications.full_name','interview_schedules.status', 'interview_schedules.schedule_date')
            ->leftjoin('job_applications', 'job_applications.id', 'interview_schedules.job_application_id');
        // Filter by status
        if($request->status != 'all' && $request->status != ''){
            $shedule = $shedule->where('interview_schedules.status', $request->status);
        }

        // Filter By candidate
        if($request->applicationID != 'all' && $request->applicationID != ''){
            $shedule = $shedule->where('job_applications.id', $request->applicationID);
        }

        // Filter by StartDate
        if($request->startDate !== null && $request->startDate != 'null'){
            $shedule = $shedule->where(DB::raw('DATE(interview_schedules.`schedule_date`)'), '>=', "$request->startDate");
        }

        // Filter by EndDate
        if($request->endDate !== null && $request->endDate != 'null'){
            $shedule = $shedule->where(DB::raw('DATE(interview_schedules.`schedule_date`)'), '<=', "$request->endDate");
        }

        return DataTables::of($shedule)
            ->addColumn('action', function ($row) {
                $action = '';


                if( $this->user->cans('view_schedule')){
                    $action.= '<a href="javascript:;" data-row-id="' . $row->id . '" class="btn btn-info btn-circle view-data"
                      data-toggle="tooltip" data-original-title="'.__('app.view').'"><i class="fa fa-search" aria-hidden="true"></i></a>';
                }
                if( $this->user->cans('edit_schedule')){
                    $action.= '<a href="javascript:;" style="margin-left:4px" data-row-id="' . $row->id . '" class="btn btn-primary btn-circle edit-data"
                      data-toggle="tooltip" data-original-title="'.__('app.edit').'"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }

                if( $this->user->cans('delete_schedule')){
                    $action.= ' <a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-row-id="' . $row->id . '" data-original-title="'.__('app.delete').'"><i class="fa fa-times" aria-hidden="true"></i></a>';
                }
                return $action;
            })
            ->addColumn('checkbox', function ($row) {
                return '
                    <div class="checkbox form-check">
                        <input id="' . $row->id . '" type="checkbox" name="id[]" class="form-check-input" value="' . $row->id . '" >
                        <label for="' . $row->id . '"></label>
                    </div>
                ';
            })
            ->editColumn('full_name', function ($row) {
                return ucwords($row->full_name);
            })
            ->editColumn('schedule_date', function ($row) {
                return Carbon::parse($row->schedule_date)->format('d F, Y H:i a');
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'pending'){
                    return '<label class="badge bg-warning">'.__('app.pending').'</label>';
                }
                if($row->status == 'hired'){
                    return '<label class="badge bg-success">'.__('app.hired').'</label>';
                }
                if($row->status == 'canceled'){
                    return '<label class="badge bg-danger">'.__('app.canceled').'</label>';
                }
                if($row->status == 'rejected'){
                    return '<label class="badge bg-danger">'.__('app.rejected').'</label>';
                }
            })
            ->rawColumns(['action', 'status', 'full_name', 'checkbox'])
            ->make(true);
    }

    /**
     * @param $id
     * @return string
     * @throws \Throwable
     */
    public function edit($id){
        abort_if(! $this->user->cans('edit_schedule'), 403);
        
        $this->candidates = JobApplication::all();
        $this->users = User::with('company')->company()->get();
        $this->schedule = InterviewSchedule::with(['jobApplication', 'user'])->find($id);
        $this->comment = ScheduleComments::where('interview_schedule_id', $this->schedule->id)
                                            ->where('user_id', $this->user->id)->first();
        $this->employeeList = json_encode($this->schedule->employee->pluck('user_id')->toArray());

        return view('admin.interview-schedule.edit', $this->data)->render();
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request){
        abort_if(! $this->user->cans('add_schedule'), 403);

        $dateTime =  $request->scheduleDate.' '.$request->scheduleTime;
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);

        foreach ($request->candidates as $candidateId) {
            // store Schedule
            $interviewSchedule = new InterviewSchedule();
            $interviewSchedule->job_application_id = $candidateId;
            $interviewSchedule->schedule_date = $dateTime;
            $interviewSchedule->save();

            $jobApplicationStatus = ApplicationStatus::where('status','interview')->first();
            // Update Schedule Status
            $jobApplication = $interviewSchedule->jobApplication;
            $jobApplication->status_id = $jobApplicationStatus->id;
            $jobApplication->save();

            if($request->comment){
                $scheduleComment = [
                    'interview_schedule_id' => $interviewSchedule->id,
                    'user_id' => $this->user->id,
                    'comment' => $request->comment
                ];

                $interviewSchedule->comments()->create($scheduleComment);
            }

            if(!empty($request->employees)){
                $interviewSchedule->employees()->attach($request->employees, ['company_id' => $this->user->company_id]);

                // Mail to employee for inform interview schedule
                Notification::send($interviewSchedule->employees, new ScheduleInterview($jobApplication));
            }

            // mail to candidate for inform interview schedule
            Notification::send($jobApplication, new CandidateScheduleInterview($jobApplication, $interviewSchedule));
        }

        return Reply::redirect(route('admin.interview-schedule.index'), __('menu.interviewSchedule').' '.__('messages.createdSuccessfully'));
    }

    public function changeStatus(Request $request){
        abort_if(! $this->user->cans('add_schedule'), 403);

        $this->commonChangeStatusFunction($request->id, $request);

        return Reply::success(__('messages.interviewScheduleStatus'));
    }

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return array
     */
    public function update(UpdateRequest $request, $id){
        abort_if(! $this->user->cans('add_schedule'), 403);

        $dateTime =  $request->scheduleDate.' '.$request->scheduleTime;
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', $dateTime);

        // Update interview Schedule
        $interviewSchedule = InterviewSchedule::select('id', 'job_application_id', 'schedule_date', 'status')
                            ->with([
                                'jobApplication:id,full_name,email,job_id,status_id',
                                'employees',
                                'comments'
                            ])
                            ->where('id', $id)->first();
        $interviewSchedule->schedule_date = $dateTime;
        $interviewSchedule->save();

        if($request->comment){
            $scheduleComment = [
                'comment' => $request->comment
            ];

            $interviewSchedule->comments()->updateOrCreate([
                'interview_schedule_id' => $interviewSchedule->id,
                'user_id' => $this->user->id
            ], $scheduleComment);
        }

        $jobApplication = $interviewSchedule->jobApplication;

        if(!empty($request->employee)){
            $employees = [];

            foreach ($request->employee as $employee) {
                $employees = Arr::add($employees, $employee, ['company_id' => $this->user->company_id]);
            }
            
            $interviewSchedule->employees()->sync($employees);

            // Mail to employee for inform interview schedule
            Notification::send($interviewSchedule->employees, new ScheduleInterview($jobApplication));
        }

        return Reply::redirect(route('admin.interview-schedule.index'), __('menu.interviewSchedule').' '.__('messages.updatedSuccessfully'));
    }

    /**
     * @param $id
     * @return array
     */
    public function destroy($id)
    {
        abort_if(! $this->user->cans('delete_schedule'), 403);

        InterviewSchedule::destroy($id);
        return Reply::success(__('messages.recordDeleted'));
    }

    /**
     * @param $id
     * @return string
     * @throws \Throwable
     */
    public function show(Request $request, $id)
    {
        abort_if(! $this->user->cans('view_schedule'), 403);
        $this->schedule = InterviewSchedule::with(['jobApplication', 'user'])->find($id);
        $this->currentDateTimestamp = Carbon::now(company()->timezone)->timestamp;
        $this->tableData = null;

        if($request->has('table')){
            $this->tableData = 'yes';
        }

        return view('admin.interview-schedule.show', $this->data)->render();
    }

    // notify and reminder to candidate on interview schedule
    public function notify($id, $type)
    {

        $jobApplication = JobApplication::find($id);

        if ($type == 'notify') {
            if ($jobApplication->status->status == 'hired') {
                // mail to candidate for hiring notify
                Notification::send($jobApplication, new CandidateNotify());
                return Reply::success(__('messages.notificationForHire'));
            }
            
            if ($jobApplication->status->status == 'rejected') {
                // mail to candidate for hiring notify
                Notification::send($jobApplication, new CandidateRejected());
                return Reply::success(__('messages.notificationForReject'));
            }
            
        } else {
            // mail to candidate for interview reminder
            Notification::send($jobApplication, new CandidateReminder( $jobApplication->schedule));
            return Reply::success(__('messages.notificationForReminder'));
        }

    }

    // Employee response on interview schedule
    public function employeeResponse($id, $res){

        $scheduleEmployee = InterviewScheduleEmployee::find($id);
        $users = User::allAdmins(); // Get All admins for mail
        $type = 'refused';

        if($res == 'accept'){  $type = 'accepted'; }

        $scheduleEmployee->user_accept_status = $res;

        // mail to admin for employee response on refuse or accept
        Notification::send($users, new EmployeeResponse($scheduleEmployee->schedule, $type, $this->user));

        $scheduleEmployee->save();

        return Reply::success(__('messages.responseAppliedSuccess'));

    }

    public function changeStatusMultiple(Request $request){
        abort_if(! $this->user->cans('edit_schedule'), 403);
        foreach($request->id as $ids)
        {
            $this->commonChangeStatusFunction($ids, $request);
        }

        return Reply::success(__('messages.interviewScheduleStatus'));
    }

    public function commonChangeStatusFunction($id, $request)
    {
        // store Schedule
        $interviewSchedule = InterviewSchedule::select('id', 'job_application_id', 'status')
                            ->with([
                                'jobApplication:id,full_name,email,job_id,status_id',
                                'employees'
                            ])
                            ->where('id', $id)->first();
        $interviewSchedule->status = $request->status;
        $interviewSchedule->save();

        $application = $interviewSchedule->jobApplication;
        $status = ApplicationStatus::select('id', 'status');

        if(in_array($request->status, ['rejected', 'canceled'])){
            $applicationStatus = $status->status('rejected');
        }
        if($request->status === 'hired'){
            $applicationStatus = $status->status('hired');
        }
        if ($request->status === 'pending') {
            $applicationStatus = $status->status('interview');
        }

        $application->status_id = $applicationStatus->id;
        
        $application->save();

        $employees = $interviewSchedule->employees;
        $admins = User::allAdmins();

        $users = $employees->merge($admins);

        if($users){
            // Mail to employee for inform interview schedule
            Notification::send($users, new ScheduleInterviewStatus($application));
        }

        if($request->mailToCandidate ==  'yes'){
            // mail to candidate for inform interview schedule status
            Notification::send($application, new ScheduleStatusCandidate($application, $interviewSchedule));
        }

        return;
    }
}
