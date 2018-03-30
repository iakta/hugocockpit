<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/hugo')">@lang('Hugo')</a></li>
        <li class="uk-active"><span>@lang('Fields')</span></li>
    </ul>
</div>

<div class="uk-margin-large-top" riot-view>


    <div class="uk-grid uk-grid-divider">

        <div class="uk-width-medium-1-4">

            <h4>@lang('Collections')</h4>

            <div class="uk-margin" each="{meta, collection in collections}">
                <button class="uk-button uk-width-2-3 uk-button-large uk-text-left" onclick="{editfields}"><i class="uk-icon-gear uk-icon-justify"></i>{ meta.name || collection }</button>
                <br />
                {meta.description}
            </div>
        </div>

        <div class="uk-width-medium-3-4">


            <div class="uk-form-row">


                <h4>@lang('Fields') <span if="{collection.name}">@lang('for') <strong>{collection.label}</strong></span></h4>
                <p show="{ !collection.fields }">@lang("Please select a collection")</p>
                <p if="{ collection.fields && collection.fields.length }"> @lang('insert Hugo meta name (for example \'title\' or \'content\'), if any, and select featured image (if more than one image)')</p>
                <div class="uk-margin-small-top" show="{ collection.fields && collection.fields.length }">
                    <div  class="uk-form uk-margin-top">


                        <div class="uk-grid uk-margin-small-top" each="{field,idx in collection.fields}">
                            <div class="uk-width-1-5 uk-form-label">
                                <label class="uk-width-1-1  ">{field.name}</label>
                            </div>
                            <div class="uk-width-2-5">
                                <input type="text" name="{field.name}" class="uk-width-1-1 uk-form-controls" bind="collection.fields[{idx}].hugoname" _value="{field.hugoname}">
                            </div>
                            <div class="uk-width-1-5">
                                <span  if="{field.type=='image'}"> <input type="radio" value="{field.name}" name="featured_image"  checked="{field.name==collection.featuredfield}" onclick="{setfeatured}">
                                    <label for="featured_image" >@lang('is featured?')</label>
                               </span>
                            </div>
                        </div>

                        <div class="uk-button-group uk-margin-right uk-margin-large-top uk-width-1-5">
                            <button class="uk-button uk-button-large uk-button-primary uk-width-1-1" onclick="{ storehugo}">@lang('Save')</button>
                        </div>
                    </div>

                </div>
                <div show="{ !collection }">
                    @lang('Select collection')
                </div>
            </div>
        </div>
    </div>
    
    <script type="view/script">

        var $this = this;
        this.collection=null;
        this.collection=[];
        this.mixin(RiotBindMixin);


        this.on('mount', function() {

            App.callmodule('collections:collections', true).then(function(data) {

                this.collections = data.result;
                console.log("Collections are",this.collections);
                for(c in this.collections){
                    col=this.collections[c];
                    col.featuredfield=null;
                    featured=false;
                    firstimage=null;
                    col.fields.forEach(function(f){

                        hopts=f.options.hugo;
                        if(hopts){
                            if(hopts.isfeatured){
                                col.featuredfield=f.name;
                                featured=true;
                            }
                            f.hugoname = hopts.name || "";
                        }

                       if(f.type=='image' && !featured && !firstimage){
                            firstimage=f;

                       }
                    });
                    if(!featured && firstimage){
                        col.featuredfield=firstimage.name;
                    }

                }
                this.ready  = true;
                this.update();

            }.bind(this));

        });

        editfields(e){
            //select collection
            this.collection=e.item.meta;
            console.log(this.collection.fields);

        }

        setfeatured(e){
            this.collection.featuredfield=e.item.field.name;
        }

        storehugo(event){
            //store hugo collections metafields..
            var collection = this.collection;
            collection.fields.forEach(function(f){
                if(f.hugoname || (f.options.hugo && f.options.hugo.name) || (collection.featuredfield && collection.featuredfield == f.name)){
                    //get options
                    opts=f.options;
                    if(!opts) return;

                    if(opts.constructor === Array){
                        if(opts.length==0){
                            f.options={};
                            opts=f.options;
                        }
                    }else{
                        console.log("Heck.. :)",f);
                        //maybe should warn?
                        f.options={};
                        opts=f.options;
                    }

                    hopts=opts.hugo;
                    if(!hopts){
                        hopts={ name:null, isfeatured:false};
                        opts.hugo=hopts;
                    }
                    if(f.hugoname)
                        hopts.name=f.hugoname;
                    if(f.name==collection.featuredfield)
                        hopts.isfeatured=true;
                    else
                        hopts.isfeatured=false;

                    console.log("HOPTS",hopts,"FILD",f.hugoname,f.isfeatured,f);
                }
                console.log("F: ",f);
            });

            console.log(this.collection.fields );
            //console.log("Filed[0] hugoname",this.collection.fields[0].hugoname);


            console.log(collection);
            App.callmodule('collections:saveCollection', [this.collection.name, collection]).then(function(data) {
                if (data.result) {

                    App.ui.notify("Saving successful", "success");
                    $this.collection = data.result;
                    $this.update();

                } else {

                    App.ui.notify("Saving failed.", "danger");
                }
            });
            return false;
        }

    </script>

</div>
