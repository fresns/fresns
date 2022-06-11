@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_events') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_events_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--Event-->
    <div class="row">
        <!--Crontab List-->
        <div class="col-lg-6">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead>
                        <tr class="table-info">
                            <th colspan="3" class="text-center">{{ __('FsLang::panel.sidebar_events_tab_crontab') }}</th>
                        </tr>
                        <tr class="table-secondary">
                            <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                            <th scope="col">{{ __('FsLang::panel.table_command_word') }}</th>
                            <th scope="col">{{ __('FsLang::panel.event_crontab_time') }} <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.event_crontab_time_desc') }}"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($crontabList as $crontab)
                            <tr>
                                <td>{{ $crontab['unikey'] }} </td>
                                <td>{{ $crontab['cmdWord'] }} </td>
                                <td>{{ $crontab['cronTableFormat'] }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!--Subscribe List-->
        <div class="col-lg-6">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead>
                        <tr class="table-info">
                            <th colspan="4" class="text-center">{{ __('FsLang::panel.sidebar_events_tab_subscribe') }}</th>
                        </tr>
                        <tr class="table-secondary">
                            <th scope="col">{{ __('FsLang::panel.table_type') }}</th>
                            <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                            <th scope="col">{{ __('FsLang::panel.table_command_word') }}</th>
                            <th scope="col">{{ __('FsLang::panel.event_subscribe_table') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscribeList as $sub)
                            <tr>
                                <td>{{ $sub['type'] }} </td>
                                <td>{{ $sub['unikey'] }} </td>
                                <td>{{ $sub['cmdWord'] }} </td>
                                <td>{{ $sub['subTableName'] }} </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
