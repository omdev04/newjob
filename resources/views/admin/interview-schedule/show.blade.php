<style>
    .notify-button-show{
        /*width: 9em;*/
        height: 1.5em;
        font-size: 0.730rem !important;
        line-height: 0.5 !important;
    }

</style>
<link rel="stylesheet" href="{{ asset('assets/plugins/iCheck/all.css') }}">

<div class="modal-header">
    <h4 class="modal-title">@lang('modules.interviewSchedule.interviewSchedule')</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
            <div class="row font-12">
                <div class="col-6">
                    <div class="row">
                        <div class="col-md-5">
                            <h4>@lang('modules.interviewSchedule.scheduleEditDetail')</h4>
                        </div>
                        <div class="col-md-5">
                            @if($currentDateTimestamp <= $schedule->schedule_date->timestamp && $user->cans('edit_schedule'))
                                <button onclick="editSchedule()" class="btn btn-sm btn-info notify-button-show" title="Edit"> <i class="fa fa-pencil"></i> @lang('app.edit')</button>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <strong>@lang('modules.interviewSchedule.job') </strong><br>
                        <p class="text-muted">{{ ucwords($schedule->jobApplication->job->title).' ('.ucwords($schedule->jobApplication->job->location->location).')' }}</p>
                    </div>
                    <hr>
                    <div class="col-sm-12">
                        <strong>@lang('app.interviewOn') </strong><br>
                        <p class="text-muted">{{ $schedule->schedule_date->format('H:i A , d-m-Y ') }}</p>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>@lang('modules.interviewSchedule.assignedEmployee')</strong><br>
                        </div>
                        <div class="col-sm-6">
                            <strong>@lang('modules.interviewSchedule.employeeResponse')</strong><br>
                        </div>
                       @forelse($schedule->employee as $key => $emp )
                        <div class="col-sm-6">
                            <p class="text-muted">{{ ucwords($emp->user->name) }}</p>
                        </div>

                        <div class="col-sm-6">
                            @if($emp->user_accept_status == 'accept')
                                <label class="badge badge-success">@lang('app.accepted')</label>
                            @elseif($emp->user_accept_status == 'refuse')
                                <label class="badge badge-danger">{{ ucwords($emp->user_accept_status) }}</label>
                            @else
                                <label class="badge badge-warning">{{ ucwords($emp->user_accept_status) }}</label>
                            @endif
                        </div>
                        @empty
                            <div class="col-sm-12 text-center text-muted">
                                <strong>@lang('modules.interviewSchedule.noEmployeeAssigned')</strong><br>
                            </div>
                        @endforelse
                    </div>

                </div>
                <div class="col-6">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>@lang('modules.interviewSchedule.candidateDetail')</h4>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <strong>@lang('app.name')</strong><br>
                        <p class="text-muted">{{ ucwords($schedule->jobApplication->full_name) }}</p>
                    </div>


                    <div class="col-sm-12">
                        <strong>@lang('app.email')</strong><br>
                        <p class="text-muted">{{ $schedule->jobApplication->email }}</p>
                    </div>

                    <div class="col-sm-12">
                        <strong>@lang('app.phone')</strong><br>
                        <p class="text-muted">{{ $schedule->jobApplication->phone }}</p>
                    </div>

                    <div class="col-sm-12">
                        <p class="text-muted">
                        @if ($schedule->jobApplication->resume_url)
                            <a target="_blank" href="{{ $schedule->jobApplication->resume_url }}" class="btn btn-sm btn-primary">@lang('app.view') @lang('modules.jobApplication.resume')</a>
                        @endif
                        </p>
                    </div>

                </div>
                @if($schedule->jobApplication->schedule->comments == 'interview' && count($application->schedule->comments) > 0)
                    <hr>

                    <h5>@lang('modules.interviewSchedule.comments')</h5>
                    @forelse($schedule->jobApplication->schedule->comments as $key => $comment )
                        <div class="row">
                            <div class="col-sm-6">
                                <p class="text-muted">{{ $comment->user->name }}</p>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <p class="text-muted">{{ $comment->comment }}</p>
                        </div>
                    @empty
                    @endforelse

                @endif

                <div class="col-6">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="rejected">
                                <div class="iradio_minimal-blue" aria-checked=""
                                    aria-disabled="false" style="position: relative;font-size: .7rem">
                                    <input id="rejected" type="radio" @if($schedule->status == 'rejected') checked @endif name="r1"
                                    class="minimal" style="position: absolute; opacity: 0;"><ins class="iCheck-helper"
                                        style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins>
                                </div>
                                @lang('app.rejected')
                            </label>
                            <label for="hired">
                                <div class="iradio_minimal-blue" aria-checked=""
                                    aria-disabled="false" style="position: relative;margin-left: 10px;font-size: .7rem">
                                    <input id="hired" type="radio" @if($schedule->status == 'hired') checked @endif name="r1"
                                    class="minimal" style="position: absolute; opacity: 0;"><ins class="iCheck-helper"
                                        style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0; "></ins>
                                </div>
                                @lang('app.hired')
                            </label>
                            <label for="pending">
                                <div class="iradio_minimal-blue" aria-checked=""
                                    aria-disabled="false" style="position: relative;margin-left: 10px;font-size: .7rem">
                                    <input id="pending" type="radio" @if($schedule->status == 'pending') checked @endif name="r1"
                                    class="minimal" style="position: absolute; opacity: 0;"><ins class="iCheck-helper"
                                        style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins>
                                </div>
                                @lang('app.pending')
                            </label>
                            <label for="canceled">
                                <div class="iradio_minimal-blue" aria-checked=""
                                    aria-disabled="false" style="position: relative;margin-left: 10px;font-size: .7rem">
                                    <input id="canceled" type="radio" @if($schedule->status == 'canceled') checked @endif name="r1"
                                    class="minimal" style="position: absolute; opacity: 0;"><ins class="iCheck-helper"
                                        style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0; "></ins>
                                </div>
                                @lang('app.canceled')
                            </label>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn dark btn-outline" data-dismiss="modal">@lang('app.close')</button>
</div>
<script src="{{ asset('assets/plugins/iCheck/icheck.min.js') }}"></script>

<script>
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass   : 'iradio_minimal-blue'
    })

    $('input[type="radio"].minimal').on('ifChecked', function(e) {
        statusChange($(this).prop('id'));
    })

    // Employee Response on schedule
    function statusChange(status) {
        var msg;

        swal({
            title: "@lang('errors.askForCandidateEmail')",
            text: msg,
            type: "info",
            showCancelButton: true,
            confirmButtonColor: "#0c19dd",
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.no')",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function (isConfirm) {
            if (isConfirm) {
                statusChangeConfirm(status , 'yes')
            }
            else{
                statusChangeConfirm(status , 'no')
            }

        });
    }

    // change Schedule schedule
    function statusChangeConfirm(status, mailToCandidate) {
        var token = "{{ csrf_token() }}";
        var id = "{{$schedule->id}}";
        $.easyAjax({
            url: '{{route('admin.interview-schedule.change-status')}}',
            container: '.modal-body',
            type: "POST",
            data: {'_token': token,'status': status,'id': id,'mailToCandidate': mailToCandidate},
            success: function (response) {
                @if($tableData)
                    table._fnDraw();
                @else
                    reloadSchedule();
                @endif
                $('#scheduleDetailModal').modal('hide');
            }
        })
    }
    function editSchedule() {
        var url = "{{ route('admin.interview-schedule.edit', $schedule->id) }}";
        $('#modelHeading').html('Schedule');
        $('#scheduleEditModal').modal('hide');
        $.ajaxModal('#scheduleEditModal', url);
    }

</script>

