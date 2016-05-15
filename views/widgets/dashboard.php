<div>
    <div class="uk-panel-box uk-panel-card">

        <div class="uk-panel-box-header uk-flex">
            <strong class="uk-flex-item-1">@lang('Hugo')</strong>
            @if(count($collections))
            <span class="uk-badge uk-flex uk-flex-middle"><span>{{ count($collections) }}</span></span>
            @endif
        </div>

        @if(count($collections))

            <div class="uk-margin">

                <ul class="uk-list uk-margin-top">
                    <li>
                        <div class="uk-grid uk-grid-small">
                            <div class="uk-flex-item-1">
                                <a href="@route('/hugo')"><i class="uk-icon-justify uk-icon-list"></i> @lang('admin Hugo')</a>
                            </div>
                        </div>
                    </li>
                </ul>

            </div>

        @else

            <div class="uk-margin uk-text-center uk-text-muted">

                <p class="uk-text-large">
                    <i class="uk-icon-list"></i>
                </p>

                @lang('Nothing to publish'). <a href="@route('/collections/collection')">@lang('Create a collection')</a>.

            </div>

        @endif

    </div>

</div>
