<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Hugo')</span></li>
    </ul>
</div>

<div class="uk-margin-large-top uk-form" riot-view>

    <h3>@lang('Generate HUGO site') <span class="uk-alert-danger">@lang('Use with caution, still Beta')</span></h3>

    <div class="uk-grid">
        <div class="uk-width-2-3">
            <div class="uk-grid">
                <div class="uk-width-1-3">@lang('Hugo dir'):</div>
                <div class="uk-width-2-3">
                    <a href="#" onclick="{configureHugoDir}"><i class="uk-icon-justify uk-icon-folder"></i></a>
                    <span if="hugoDir">{hugoDir}</span>
                    <span class="uk-alert-danger" if="{!hugoDir}">@lang('PLEASE SET HUGO DIR')<br />
                    @lang('you won\'t be able to generate the files for Hugo unless you set it')</span>


                </div>
            </div>
            <div class="uk-grid uk-margin-small-top">
                <div class="uk-width-1-3">@lang('Generating site for language '):</div>
                <div class="uk-width-2-3"><strong>@lang('default')</strong>
                    <span if="languages.length"> and </span>
                    <strong each="{language in languages}"> {language} </strong>
                </div>
            </div>
        </div>
        <div class="uk-width-1-3">
            <a class="uk-button uk-button-large uk-width-1-1" href="@route('/hugo/fields')"><i class="uk-icon-plus-circle uk-icon-justify"></i>  @lang('Configure Hugo fields')</a>
            <br />
            <p>@lang('Each field in a collection can be mapped to a predefined Hugo field. You can set it here')</p>
        </div>
    </div>


    <div class="uk-grid uk-margin-small-top">
        <div class="uk-flex-item-1">
            @lang('Select collection for which to generate Hugo pages')<br />
            <em>@lang('Each collection will be rendered under a Hugo section, that is a subdir under \'content\'')</em>
        </div>
    </div>
    <div class="uk-grid uk-margin-small-top">
        <div class="uk-width-2-3">
        <fieldset class="" data-uk-margin>
            <div class="uk-form-row" each="{ collection, meta in collections }">
                <input type="checkbox" name="{collection}" checked="{meta.selected}" onclick="{meta.toggle}">  { meta.label || collection }
            </div>
        </fieldset>
        </div>
        <div class="uk-width-1-3">
            <button class="uk-button uk-button-large uk-button-primary " disabled="{ !oneSelected || !hugoDir }" type="button" onclick="{ generate }"><i class="uk-icon-justify uk-icon-arrow-down"></i>@lang('Generate')</button>

        </div>
    </div>


    <script type="view/script">

        var $this = this;

        this.ready  = false;
        this.collections = [];
        this.oneSelected=false;
        this.hugoDir='';
        this.languages    = App.$data.languages;

        this.on('mount', function() {

            App.callmodule('collections:collections', true).then(function(data) {

                this.collections = data.result;
                console.log(this.collections);
                //add selection flag
                for(c in this.collections){
                    col=this.collections[c];
                    col['selected']=false;

                    self=this;
                    col.toggle=function(e){
                        col=e.item.meta;
                        col.selected = !col.selected;
                        self.checkCollectionSelected();
                    }
                };

                this.ready  = true;
                this.update();

            }.bind(this));

            App.callmodule('hugo:getHugoDir',true).then(function(data){
                console.log("HUGOD IR",data.result);
                this.hugoDir=data.result;
                this.update();
            }.bind(this));
        });


        checkCollectionSelected(){
            for(c in this.collections){
                col=this.collections[c];
                if(col.selected){
                    this.oneSelected=true;
                    return;
                }
            };
            this.oneSelected=false;
        };
        generate() {
            //get list of collection names
            cols=[];
            for(c in this.collections){
                col=this.collections[c];
                if(col.selected){ 
                    cols.push(c);
                }
            };
            langs=['default'];
            this.languages.forEach(function(l){
                langs.push(l);
            });
            console.log(cols,langs);
            App.request('/hugo/generate', {data:cols, languages:langs }).then(function(data){
                App.ui.notify("Site generated", "success");
                console.log("Generate success:",data);
            },function(err){
                App.ui.notify("Error generating site", "error");
                console.log("Generate error",err);
            });
        }

        test(){
            for(c in this.collections){
                    col=this.collections[c];
                   console.log(col);
                };
                console.log("chTHISecl",this);
        }


        configureHugoDir(){
            options  = App.$.extend({
                    previewfiles: false,
                    pattern  : '*',
                    typefilter: '',
                    path: false,
                    selected : []
                }, {});
                var   dialog = UIkit.modal.dialog([
                    '<div>',
                        '<div class="uk-modal-header uk-text-large">Select HUGO Dir</div>',
                        '<cp-dirselect path="'+$this.hugoDir+'"></cp-dirselect>',
                        '<div class="uk-modal-footer uk-text-right">',
                            '<button class="uk-button uk-button-primary uk-margin-right uk-button-large uk-hidden js-select-button">Select dir</button>',
                            '<button class="uk-button uk-button-large uk-modal-close">Close</button>',
                        '</div>',
                    '</div>'
                ].join(''), {modal:false});

                var selectbtn   = dialog.dialog.find('.js-select-button');

                riot.mount(dialog.element[0], '*', options);

                selectbtn.on('click', function() {
                    App.callmodule('hugo:setHugoDir',path).then(function(data){
                        console.log("Called setDir");
                        $this.hugoDir=path;
                    });
                    dialog.hide();
                });
                var path='';
                dialog.on('selectionchange', function(e, s) {
                    isDir = s.selectable ;
                    p = s.path
                    selectbtn[isDir ? 'removeClass':'addClass']('uk-hidden');
                    if(isDir)
                        path=p;
                });

                dialog.show();
        }

    </script>

</div>


