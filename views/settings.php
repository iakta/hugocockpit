<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/hugo')">@lang('Hugo')</a></li>
        <li class="uk-active"><span>@lang('Settings')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

    @if ($settingsexists)
    @if (is_writable($settingspath))
    <picoedit path="{{$settings_cockpit_path}}"></picoedit>
    @else
    <div class="uk-alert uk-alert-danger">
        @lang('Custom settings file is not writable').
    </div>
    @endif

    @else
    <div class="uk-alert">
        @lang('Custom settings file does not exist').
        <a class="uk-button uk-button-link" href="@route('/hugo/settings/create')"><i class="uk-icon-magic"></i> @lang('Create settings file')</a>
    </div>
    @endif

</div>
<script type="view/script">

        var $this = this;

</script>

<style>

    picoedit.CodeMirror {
        height: auto;
    }

</style>


