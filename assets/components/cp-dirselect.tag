<cp-dirselect>
    <div class="uk-form uk-form-icon uk-width-1-1">
	    <i class="uk-icon-folder"></i>
	    <input name="dir" id="dirselect"   type="text"  class="uk-form-width-large" onkeyup="{testdir}" />
    </div>
    <script>
        var $this = this
        // Ok, as a note: in install.php I could not for f**ks sake, have a this.mixin(RiotBindMixin)
        // alongisde this one, so I emulated the bind mixin with direct jquery calls.
        // it's dirty but works'

        //this.mixin(RiotBindMixin);

        this.dir = null;
        riot.util.bind(this);
        var selectenable =false;
        if(opts.path && opts.path == 'undefined')
            opts.path = null;

        this.on('mount', function(){
            console.log("OPTS",opts);
            if(opts.path){
                this.dir=opts.path;
                testDirApi(this.dir);
                $('#dirselect').val(this.dir);
            }
        }.bind(this));


        testdir(){
            var val = $('#dirselect').val();
            //console.log('testdir',this.dir, this, val);
            //test if directory exists..?
            testDirApi(val);//it was this.dir but since there's no mixin anymore..
        }

        function testDirApi(path){
            console.log("Testing dir ", path);
            App.callmodule('hugo:isDir',[path]).then(function(data){
                //console.log("HUGO DIR",data.result);
                selectenable = data.result ? true : false;
                params={selectable: selectenable, path:path}
                App.$($this.root).trigger('selectionchange',  params );
            } );
        }

    </script>
</cp-dirselect>