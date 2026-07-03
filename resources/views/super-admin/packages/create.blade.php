@extends('layouts.app') 

@push('head-script')
    <link rel="stylesheet" href="{{ asset('assets/node_modules/switchery/dist/switchery.min.css') }}">
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">@lang('app.createNew')</h4>

                <form id="editSettings" class="ajax-form">
                    @csrf

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="company_name">@lang('modules.packages.packageName')</label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="form-group">
                                <label for="company_email">@lang('modules.packages.description')</label>
                                <textarea name="description" id="description" cols="30" rows="3" class="form-control"></textarea>
                            </div>        
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_phone">@lang('modules.packages.monthlyPrice')</label>
                                <input type="text" class="form-control" id="monthly_price" name="monthly_price">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_phone">@lang('modules.packages.annualPrice')</label>
                                <input type="text" class="form-control" id="annual_price" name="annual_price">
                            </div>
                        </div>
                        @if($paymentSetting->stripe_status == 'active')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stripe_monthly_plan_id">@lang('modules.packages.stripeMonthlyPlanId')</label>
                                    <input type="text" class="form-control" id="stripe_monthly_plan_id" name="stripe_monthly_plan_id">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stripe_annual_plan_id">@lang('modules.packages.stripeAnnualPlanId')</label>
                                    <input type="text" class="form-control" id="stripe_annual_plan_id" name="stripe_annual_plan_id">
                                </div>
                            </div>
                        @endif
                        @if($paymentSetting->razorpay_status == 'active')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">@lang('modules.packages.razorpayAnnualPlanId')</label>
                                        <input type="text" id="razorpay_annual_plan_id" name="razorpay_annual_plan_id" value="" class="form-control" >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('modules.packages.razorpayMonthlyPlanId')</label>
                                        <input type="text" name="razorpay_monthly_plan_id" id="razorpay_monthly_plan_id" value=""  class="form-control">
                                    </div>
                                </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_phone">@lang('modules.packages.noOfJobOpenings') @lang('modules.packages.leaveBlank')</label>
                                <input type="text" class="form-control" id="no_of_job_openings" name="no_of_job_openings">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_phone">@lang('modules.packages.noOfCandidateAccess') @lang('modules.packages.leaveBlank')</label>
                                <input type="text" class="form-control" id="no_of_candidate_access" name="no_of_candidate_access">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label col-sm-8">@lang('modules.packages.careerWebsite')</label>

                                <div class="col-sm-4">
                                    <div class="switchery-demo">
                                        <input type="checkbox"
                                               class="js-switch"
                                               name="career_website" 
                                               data-color="#99d683" data-size="small"
                                               />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label col-sm-8">@lang('modules.packages.multipleRoles')</label>

                                <div class="col-sm-4">
                                    <div class="switchery-demo">
                                        <input type="checkbox"
                                               class="js-switch"
                                               name="multiple_roles" 
                                               data-color="#99d683" data-size="small"
                                               />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label col-sm-8">@lang('modules.packages.recommended')</label>

                                <div class="col-sm-4">
                                    <div class="switchery-demo">
                                        <input type="checkbox"
                                                class="js-switch"
                                                name="recommended" 
                                                data-color="#99d683" data-size="small"
                                                />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="control-label col-sm-8">@lang('app.status')</label>

                                <div class="col-sm-4">
                                    <div class="switchery-demo">
                                        <input type="checkbox" checked
                                                class="js-switch"
                                                name="status" 
                                                data-color="#99d683" data-size="small"
                                                />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <button type="button" id="save-form" class="btn btn-success waves-effect waves-light m-r-10">
                            @lang('app.save')
                        </button>
                    <button type="reset" class="btn btn-inverse waves-effect waves-light">@lang('app.reset')</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
 @push('footer-script')
 <script src="{{ asset('assets/node_modules/switchery/dist/switchery.min.js') }}"></script>

<script>
    // Switchery
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function () {
            new Switchery($(this)[0], $(this).data());

    });
    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route("superadmin.packages.store")}}',
            container: '#editSettings',
            type: "POST",
            redirect: true,
            file: true
        })
    });
</script>



@endpush