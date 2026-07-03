<?php

namespace App\Http\Controllers\Front;

use App\ApplicationSetting;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\FrontJobApplication;
use App\Job;
use App\JobApplication;
use App\JobApplicationAnswer;
use App\JobCategory;
use App\JobLocation;
use App\LinkedInSetting;
use App\Notifications\NewJobApplication;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use App\Company;
use App\ApplicationStatus;
use App\CompanyPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReceivedApplication;
use App\ThemeSetting;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class FrontJobsController extends FrontBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('modules.front.jobOpenings');

        $linkedinSetting = LinkedInSetting::where('status', 'enable')->first();
        /*dd($linkedinSetting);*/
        if ($linkedinSetting) {
            Config::set('services.linkedin.client_id', $linkedinSetting->client_id);
            Config::set('services.linkedin.client_secret', $linkedinSetting->client_secret);
            Config::set('services.linkedin.redirect', $linkedinSetting->callback_url);
        }
    }

    public function jobOpenings($slug)
    {
        $company = Company::withoutGlobalScope('company')->where('career_page_link', $slug)->first();

        $activePackage = CompanyPackage::where('company_id', $company->id)
            ->where('status', 'active')
            ->where(DB::raw('DATE(end_date)'), '>=', DB::raw('CURDATE()'))
            ->first();

        if (!$activePackage) {
            return abort(404);
        }

        $this->jobs = Job::frontActiveJobs($company->id);
        $this->locations = JobLocation::withoutGlobalScope('company')->where('company_id', $company->id)->get();
        $this->categories = JobCategory::withoutGlobalScope('company')->where('company_id', $company->id)->get();

        $this->company = $this->global = $company;
        $this->companyName = $this->global->company_name;
        $this->frontTheme = ThemeSetting::where('company_id', $this->company->id)->first();
        App::setLocale($this->global->locale);
        Carbon::setLocale($this->global->locale);
        setlocale(LC_TIME, $this->global->locale.'_'.strtoupper($this->global->locale));

        return view('front.job-openings', $this->data);
    }

    public function jobDetail($companySlug, $slug)
    {
        $companyId = Company::select('id', 'career_page_link')->where('career_page_link', $companySlug)->first()->id;

        $this->job = Job::where('slug', $slug)
            ->whereDate('start_date', '<=', Carbon::now())
            ->whereDate('end_date', '>=', Carbon::now())
            ->where('status', 'active')
            ->where('company_id', $companyId)
            ->firstOrFail();
        $this->linkedinGlobal = LinkedInSetting::first();
        Session::put('lastPageUrl', $slug);

        $this->company = $this->global = $this->job->company;

        $activePackage = CompanyPackage::where('company_id', $this->company->id)
            ->whereDate('end_date', '>=', Carbon::now())
            ->where('status', 'active')
            ->first();

        if (!$activePackage) {
            return abort(404);
        }

        $this->companyName = $this->global->company_name;
        $this->frontTheme = ThemeSetting::where('company_id', $this->company->id)->first();
        App::setLocale($this->global->locale);
        Carbon::setLocale($this->global->locale);
        setlocale(LC_TIME, $this->global->locale.'_'.strtoupper($this->global->locale));

        $this->pageTitle = $this->job->title . ' - ' . $this->companyName;
        $this->metaTitle = $this->job->meta_details['title'];
        $this->metaDescription = $this->job->meta_details['description'];
        $this->metaImage = $this->job->company->logo_url;
        $this->pageUrl = request()->url();

        return view('front.job-detail', $this->data);
    }

    public function callback($provider, Request $request)
    {
        if ($request->error) {
            $this->errorCode = $request->error;
            $this->error = $request->error_description;
            return view('errors.linkedin', $this->data);
        }
        $this->user = Socialite::driver($provider)->stateless()->user();
//        dd($this->user);
        $this->lastPageUrl = Session::get('lastPageUrl');
        Session::put('accessToken', $this->user->token);
        Session::put('expiresIn', $this->user->expiresIn);
        return redirect()->route('jobs.jobApply', $this->lastPageUrl);

    }

    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function jobApply($companySlug, $slug)
    {
        $companyId = Company::select('id', 'career_page_link')->where('career_page_link', $companySlug)->first()->id;

        $this->job = Job::where('slug', $slug)
            ->where(DB::raw('DATE(start_date)'), '<=', DB::raw('CURDATE()'))
            ->where(DB::raw('DATE(end_date)'), '>=', DB::raw('CURDATE()'))
            ->where('company_id', $companyId)
            ->firstOrFail();

        $this->accessToken = Session::get('accessToken');
        if ($this->accessToken) {
            $this->user = Socialite::driver('linkedin')->userFromToken($this->accessToken);
        } else {
            $this->user = [];
        }
        $this->job = Job::where('slug', $slug)->first();
      
        $this->company = $this->global = $this->job->company;
      
        $activePackage = CompanyPackage::where('company_id', $this->company->id)
            ->where('status', 'active')
            ->where(DB::raw('DATE(end_date)'), '>=', DB::raw('CURDATE()'))
            ->first();

        if (!$activePackage) {
            return abort(404);
        }
        $this->jobQuestion = $this->job->questions;

        $this->companyName = $this->global->company_name;
        $this->frontTheme = ThemeSetting::where('company_id', $this->company->id)->first();
        App::setLocale($this->global->locale);
        Carbon::setLocale($this->global->locale);
        setlocale(LC_TIME, $this->global->locale.'_'.strtoupper($this->global->locale));
        $this->applicationSetting = ApplicationSetting::where('company_id', $this->company->id)->first();
        $this->pageTitle = $this->job->title . ' - ' . $this->companyName;
        
        return view('front.job-apply', $this->data);
    }

    public function saveApplication(FrontJobApplication $request)
    {
        $job = Job::findOrFail($request->job_id);
        $activePackage = CompanyPackage::where('company_id', $job->company_id)
            ->where('status', 'active')
            ->where(DB::raw('DATE(end_date)'), '>=', DB::raw('CURDATE()'))
            ->first();
        
        if (!$activePackage) {
            return abort(404);
        }

        $applicationStatus = ApplicationStatus::where('company_id', $job->company_id)->firstOrFail();
       
        $jobApplication = new JobApplication();
        $jobApplication->full_name = $request->full_name;
        $jobApplication->job_id = $request->job_id;
        $jobApplication->company_id = $job->company_id;
        $jobApplication->status_id = $applicationStatus->id;
        $jobApplication->email = $request->email;
        $jobApplication->phone = $request->phone;
        if ($request->has('gender')) {
            $jobApplication->gender = $request->gender;
        }
        if ($request->has('dob')) {
            $jobApplication->dob = $request->dob;
        }
        if ($request->has('country')) {
            $countriesArray = json_decode(file_get_contents(public_path('country-state-city/countries.json')), true)['countries'];
            $statesArray = json_decode(file_get_contents(public_path('country-state-city/states.json')), true)['states'];

            $jobApplication->country = $this->getName($countriesArray, $request->country);
            $jobApplication->state = $this->getName($statesArray, $request->state);
            $jobApplication->city = $request->city;
        }

        $jobApplication->cover_letter = $request->cover_letter;
        $jobApplication->column_priority = 0;

        if ($request->hasFile('photo')) {
            $jobApplication->photo = Files::upload($request->photo,'candidate-photos');
        }
        $jobApplication->save();

        if ($request->hasFile('resume')) {
            $hashname = Files::upload($request->resume, 'documents/'.$jobApplication->id, null, null, false);
            $jobApplication->documents()->create([
                'company_id' => $job->company_id,
                'name' => 'Resume',
                'hashname' => $hashname
            ]);
        }

        $users = User::frontAllAdmins($job->company_id);
        $linkedin = false;
        if ($request->linkedinPhoto) {
            $contents = file_get_contents($request->linkedinPhoto);
            $getfilename =  str_replace(' ', '_', $request->full_name);
            $filename = $jobApplication->id.$getfilename.'.png';
            Storage::put('candidate-photos/'.$filename, $contents);
            $jobApplication = JobApplication::find($jobApplication->id);
            $jobApplication->photo = $filename;
            $jobApplication->save();
        }

        if($request->has('apply_type')){
            $linkedin = true;
        }

        if (!empty($request->answer)) {
            foreach ($request->answer as $key => $value) {
                $answer = new JobApplicationAnswer();
                $answer->job_application_id = $jobApplication->id;
                $answer->job_id = $request->job_id;
                $answer->question_id = $key;
                $answer->company_id = $job->company_id;
                $answer->answer = $value;
                $answer->save();
            }
        }
        $company = company::find($jobApplication->company_id);
        $jobApplication->company_name = $company->company_name;
        
       Notification::send($users, new NewJobApplication($jobApplication, $linkedin));
        
        Mail::send(new ReceivedApplication($jobApplication));

        return Reply::dataOnly(['status' => 'success', 'msg' => __('modules.front.applySuccessMsg')]);
    }

    public function fetchCountryState(Request $request)
    {
        $responseArr = [];

        $response = [
            "status" => "success", 
            "tp" => 1,
            "msg" => "Countries fetched successfully."
        ];

        switch ($request->type) {
            case 'getCountries':
                $countriesArray = json_decode(file_get_contents(public_path('country-state-city/countries.json')), true)['countries'];

                foreach ($countriesArray as $country) {
                    $responseArr = Arr::add($responseArr, $country['id'], $country['name']);
                }
            break;
            case 'getStates':
                $statesArray = json_decode(file_get_contents(public_path('country-state-city/states.json')), true)['states'];
                $countryId = $request->countryId;

                $filteredStates = array_filter($statesArray, function ($value) use ($countryId) {
                    return $value['country_id'] == $countryId;
                });

                foreach ($filteredStates as $state) {
                    $responseArr = Arr::add($responseArr, $state['id'], $state['name']);
                }
            break;
        }
        $response = Arr::add($response, "result", $responseArr);                

        return response()->json($response);
    }

    public function getName($arr, $id)
    {
        $result = array_filter($arr, function ($value) use ($id) {
            return $value['id'] == $id;
        });
        return current($result)['name'];
    }
}
