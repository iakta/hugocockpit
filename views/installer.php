<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Hugo for Cockpit installer')</span></li>
    </ul>
</div>

<div class="uk-form  " riot-view>
    <div class="uk-alert-danger  " uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <p>@lang('Use with caution, first do a backup')</p>
    </div>

    <!-- FIRST STEP: create group in Cockpit config -->
    <div id="first_step_container">
        <h3>1. @lang('Setup Cockpit')
            <button show="{first_step_done}" class="uk-button toggle1" data-uk-toggle="\{target:'#first_step'\}"><i class="uk-icon-plus"></i> @lang('expand/collapse')</button>
            </h3>
        <div id="first_step">
            <p>
                @lang('First of all, we create an "hugo_author" group, or one with the name you like, if not present already in Cockpit config')
            </p>
            <div class="uk-grid-small uk-grid"    uk-grid>
                <div class="uk-width-1-2">
                    <input type="text" bind="hugo_author_name" class="uk-form-width-large" name="hugo_author_name" placeholder="@lang('name of Hugo editor group')" />
                </div>
                <div class="uk-width-1-2">
                    <button class="uk-button uk-button-large uk-button-primary " type="button" onclick="{ generate }"><i class="uk-icon-justify uk-icon-gear"></i>@lang('Create group')</button>
                </div>
            </div>
            @if($configexists)
            <pre id="settings_content">{{ $settings_content }}
            </pre>
            @else
            <i>File does not exists yet</i>
            @endif
            <button class="uk-button uk-button-large uk-button-success " show="{first_step_ok}" type="button" onclick="{ postFirst }"><i class="uk-icon-justify uk-icon-arrow-down"></i>@lang('Ok, go on')</button>
        </div>
    </div>

    <div id="second_step_container">
        <h3>2. @lang('Create image/media dir in Cockpit')
            <button show="{second_step_done}" class="uk-button toggle2" data-uk-toggle="\{target:'#second_step'\}"><i class="uk-icon-plus"></i>@lang('expand/collapse')</button>
        </h3>
        <div id="second_step">
            <p>@lang('Then, we create a standard folder <b>{hugo_media_dir}</b> tree under "storage" dir of Cockpit')</p>

            <button class="uk-button uk-button-large uk-button-primary " type="button" onclick="{ createSubdirs }"><i class="uk-icon-justify uk-icon-arrow-down"></i>@lang('Create media dir')</button>
        </div>
    </div>

    <!-- set hugo dir and change config -->
    <div id="third_step_container">
        <h3>3. @lang('Change Hugo config files')
            <button show="{third_step_done}"  class="uk-button toggle3" data-uk-toggle="\{target:'#third_step'\}"><i class="uk-icon-plus"></i>@lang('expand/collapse')</button>
        </h3>
        <div id="third_step">
            <p>@lang('Select Hugo dir'): @lang('Hugo dir must be on the same machine as Cockpit, or at least on the same filesystem') <br />
            @lang('It must contain some HUGO related dir and at least one <i>config.toml | json | yaml</i>. This config file is needed by the plugin')</p>
            <div class="uk-grid-small uk-grid" uk-grid>
                 <div class="uk-width-1-2">
                     <cp-dirselect path="/"/>

                 </div>
                <div class="uk-width-1-2">
                    <button class="uk-button uk-button-large uk-button-success " id="select-hugo-dir" onclick="{selectHugoDir}">@lang('set Hugo dir')</button>
                </div>
            </div>
            <div class="uk-grid-small uk-grid" show="{valid_hugo_dir}" uk-grid>
                <div class="uk-width-1-2">
                    <p>@lang('now change config in Hugo dir')</p>

                </div>
                <div class="uk-width-1-2">
                    <button class="uk-button uk-button-large uk-button-success " id="select-hugo-dir" onclick="{addParamsToHugoConfig}"><i class="uk-icon-justify uk-icon-gear"></i>@lang('change Hugo config')</button>
                </div>
            </div>
            <div show="{config_done}" class="uk-alert-success">
                <p>
                    @lang('We have added the configuration in these files:')
                    <ul>
                        <li each="{file in modified_files}">{file}</li>
                    </ul>
                </p>
            </div>
            <div show="{config_done}" class="uk-alert-warning">
                <p><i>@lang('Please ensure you have this line in every config file you use in this Hugo installation')</i></p>
                <pre class="uk-alert-warning">#TOML config file
staticdir = "{ hugo_static_dir }"

#YAML config file
staticdir: { hugo_static_dir }

#JSON config file
"staticdir": "{ hugo_static_dir }"
                </pre>

                <button class="uk-button uk-button-large uk-button-primary " type="button" onclick="{ postAddConfig }"><i class="uk-icon-justify uk-icon-arrow-down"></i>@lang('Ok, go on')</button>
            </div>
        </div>
    </div>

    <div id="fourth_step_container">
        <h3>4. @lang('Create Hugocockpit config')
            <button show="{third_step_done}"  class="uk-button toggle4" data-uk-toggle="\{target:'#4th_step'\}"><i class="uk-icon-plus"></i>@lang('expand/collapse')</button>
        </h3>
        <div id="4th_step">
            <div class="uk-grid-small uk-grid" show="{valid_hugo_dir}" uk-grid>
                <div class="uk-width-1-2">
                    <p>@lang('We create the basic Hugocockpit config')</p>
                </div>
                <div class="uk-width-1-2">
                    <button class="uk-button uk-button-large uk-button-success " id="select-hugo-dir" onclick="{createHugocockpitConfig}"><i class="uk-icon-justify uk-icon-gear"></i>@lang('Create config')</button>
                </div>
            </div>

            <div show="{fourth_step_done}">
                <div class="uk-alert uk-alert-success">
                    <p>@lang('Congratulations, installation done, now remeber to:')</p>
                    <ul>
                        <li>@lang('Login to cockpit, as admin, and create collections with fields')</li>
                        <li>@lang('Create a user in Cockpit belonging to the group ')<b>{ hugo_author_name}</b></li>
                        <li>@lang('Log in as this user and fill in entries in collections, with images')</li>
                        <li>@lang('Go to the Hugo plugin page ')
                            <ol>
                                <li>@lang('map the fields to special hugo fields, if needed')</li>
                                <li>@lang('export collections to hugo .md pages')</li>
                                <li>@lang('choose a template')</li>
                                <li>@lang('Run Hugo for every language, if any')</li>
                            </ol>
                        </li>
                    </ul>
                    <p>@lang('to run install again, remove config.yaml from /cockpit/addons/Hugo')</p>
                </div>
                <button  class="uk-button uk-button-large uk-button-primary " type="button" onclick="{ doneInstall }"><i class="uk-icon-justify uk-icon-bell-o"></i>@lang('Ok. Done!')</button>
            </div>
        </div>
    </div>

    <script type="view/script">

        var $this = this;
        this.mixin(RiotBindMixin);
        this.hugo_author_name = '{{ $default_group_name }}';
        this.hugo_media_dir = '{{ $default_hugo_media_dir}}';

        this.first_step_ok=false;
        this.first_step_done=false;
        this.second_step_done=false;
        this.third_step_done=false;
        this.fourth_step_done=false;

        this.hugo_dir=null;
        this.valid_hugo_dir=false;

        this.config_done = false;
        this.hugo_static_dir = '';
        this.modified_files = [];
        var $this = this;

        this.on('mount', function() {
            var selectbtn = $('#select-hugo-dir');

            //hide every section
            $('#second_step_container').addClass('uk-hidden');
            $('#third_step_container').addClass('uk-hidden');
            $('#fourth_step_container').addClass('uk-hidden');

            App.$($this.root).on('selectionchange',function(e,s){
                 var isDir = s.selectable ;
                 console.log("is dir", isDir,s);
                 var p = s.path
                 selectbtn[isDir ? 'removeClass':'addClass']('uk-hidden');
                 if(isDir){
                    $this.hugo_dir = p;
                 }
                });
        });

        generate(){
            //call API to add
            App.request('/hugo_installer/addCockpitGroup',{group:this.hugo_author_name}  ).then(function(data){
                if(data.status=='ok'){
                    App.ui.notify("Group added", "success");
                    console.log("Generate success:",data);
                    $('#settings_content').html(data.config_str);
                    $this.first_step_ok = true;
                    $this.update();
                    //$('#first_step').addClass('uk-hidden');
                }else{
                    App.ui.notify("Error adding group", "error");
                }

            },function(err){
                App.ui.notify("Error adding group", "error");
                console.log("Generate error",err);
            });
        }

        postFirst(){
            this.first_step_done=true;
            $('#first_step').addClass('uk-hidden');
            $('#second_step_container').removeClass('uk-hidden');
        }

        createSubdirs(){
            //call API to add
            App.request('/hugo_installer/createSubdirs',{group:this.hugo_author_name}  ).then(function(data){
                if(data.status=='ok'){
                    App.ui.notify("Folders created", "success");
                    console.log("created success:",data);
                    //go forward
                    $this.second_step_done=true;
                    $('#second_step').addClass('uk-hidden');
                    $('#third_step_container').removeClass('uk-hidden');
                    $this.update();
                }else{
                    App.ui.notify("Error creating media subdirs", "error");
                }

            },function(err){
                App.ui.notify("Error creating media subdirs", "error");
                console.log("Create error",err);
            });
        }

        selectHugoDir(){
            App.request('/hugo_installer/selectHugoDir',{dir:this.hugo_dir}  ).then(function(data){
                if(data.status=='ok'){
                    App.ui.notify("Hugo dir valid created", "success");
                    console.log("created success:",data);
                    //$('#third_step').addClass('uk-hidden');
                    $this.valid_hugo_dir= true;
                    $this.second_step_done=true;
                    $this.update();
                    //$('#second_step').addClass('uk-hidden');

                }else{
                    App.ui.notify("Error selecting dir: it doesn't look like a Hugo Dir", "error");
                }

            },function(err){
                App.ui.notify("Error selecting Hugo dir: "+err, "error");
                console.log("Create error",err);
            });
        }

        addParamsToHugoConfig(){
            App.request('/hugo_installer/addParamsToHugoConfig',{dir:this.hugo_dir}  ).then(function(data){
                if(data.status=='ok'){
                    App.ui.notify("Hugo config changed", "success");
                    console.log("created success:",data);
                    $this.hugo_static_dir = data.static_dir;
                    $this.config_done = true;
                    $this.modified_files = data.modified_files;
                    $this.update();
                    //$('#second_step').addClass('uk-hidden');
                }else{
                    App.ui.notify("Error changing Hugo config", "error");
                }

            },function(err){
                App.ui.notify("Error changing config file : "+err, "error");
                console.log("Create error",err);
            });
        }

        postAddConfig(){
            $('#third_step').addClass('uk-hidden');
            this.third_step_done=true;
            $('#fourth_step_container').removeClass('uk-hidden');
        }

        createHugocockpitConfig(){
            App.request('/hugo_installer/createHugocockpitConfig',{ hugo_dir:this.hugo_dir}).then(function(data){
                if(data.status=='ok'){
                    App.ui.notify("Hugocockpit config created", "success");
                    console.log("created success:",data);
                    $this.fourth_step_done=true;
                    $this.update();
                    //$('#second_step').addClass('uk-hidden');
                }else{
                    App.ui.notify("Error creating Hugocockpit config", "error");
                }

            },function(err){
                App.ui.notify("Error creating Hugocockpit file : "+err, "error");
                console.log("Create error",err);
            });
        }

        doneInstall(){
            //$('#4th_step').addClass('uk-hidden');
            //this.fourth_step_done=true;
            //$('#fourth_step_container').removeClass('uk-hidden');
            location.href='..';
        }

    </script>
</div>
