@extends('layouts.front')

@section('header-text')
    <h1 class="hidden-sm-down"><i class="icon-ribbon"></i> @lang('modules.front.homeHeader') </h1>
    <h4 class="hidden-sm-up"><i class="icon-ribbon"></i> @lang('modules.front.homeHeader') </h4>
    <style>

        .btn-outline.btn-custom {
            color: {{ $frontTheme->primary_color }};
            background-color: transparent;
            border-color: {{ $frontTheme->primary_color }};
        }

        .btn-custom:hover {
            -webkit-box-shadow: 0 2px 10px rgba(15,172,243,0.4);
            box-shadow: 0 2px 10px rgba(15,172,243,0.4);
        }
        .btn-custom, .btn-custom:hover {
            background-color: {{ $frontTheme->primary_color }};
            border-color: {{ $frontTheme->primary_color }};
            color: #fff;
        }
        .btn-custom:hover {
            color: #fff;
            background-color: {{ $frontTheme->primary_color }};
            border-color: {{ $frontTheme->primary_color }};
        }
        .btn-custom:active, .btn-custom.active, .show>.btn-custom.dropdown-toggle {
            background-color: {{ $frontTheme->primary_color }};
            border-color: {{ $frontTheme->primary_color }};
            color: #fff;
        }
    </style>
@endsection

@section('content')



    <!--
    |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
    | Working at TheThemeio
    |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
    !-->
    <section class="section bg-gray py-60">
        <div class="container">

            <div class="row gap-y align-items-center">

                <div class="col-12">
                    <h3>{{ $global->job_opening_text }}</h3>
                    <p>{{ $global->job_opening_title }}</p>

                </div>

            </div>

        </div>
    </section>





    <!--
    |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
    | Open positions
    |‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒‒
    !-->
    <section class="section">
        <div class="container">
            <header class="section-header">
                <h2>@lang('modules.front.jobOpening')</h2>
                <hr>
            </header>


            <div data-provide="shuffle">
                <div class="text-center gap-multiline-items-2 job-filters" data-shuffle="filter">
                    <button class="btn btn-w-md btn-outline btn-round btn-custom active"data-shuffle="button">All
                    </button>
                    @foreach($locations as $location)
                        <button class="btn btn-outline btn-round btn-custom" data-shuffle="button"
                                data-group="{{ $location->location }}">{{ ucwords($location->location) }}</button>
                    @endforeach
                    <p>&nbsp;</p>
                    @foreach($categories as $category)
                        <button class="btn btn-xs btn-outline btn-round btn-dark" data-shuffle="button"
                                data-group="{{ $category->name }}">{{ ucwords($category->name) }}</button>
                    @endforeach
                </div>

                <br><br>

                <div class="row gap-y" data-shuffle="list">

                    @foreach($jobs as $job)
                        <div class="col-12 col-md-6 col-lg-4 portfolio-2" data-shuffle="item" data-groups="{{ $job->location->location.','.$job->category->name }}">
                            <a href="{{ route('jobs.jobDetail', [$job->company->career_page_link, $job->slug]) }}" class="job-opening-card">
                            <div class="card card-bordered">
                                <div class="card-block">
                                    <h5 class="card-title">{{ ucwords($job->title) }}</h5>
                                    @if($job->company->show_in_frontend == 'true')
                                        <small class="company-title">@lang('app.by') {{ ucwords($job->company->company_name) }}</small>
                                    @endif
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <span class="fw-600 fs-12 text-info">{{ ucwords($job->location->location).', '.ucwords($job->location->country->country_name) }}</span>
                                        <span class="fw-600 fs-12 text-info">{{ ucwords($job->category->name) }}</span>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>
                    @endforeach

                </div>

            </div>


        </div>
    </section>

@endsection