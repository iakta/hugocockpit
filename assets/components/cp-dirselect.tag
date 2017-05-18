<cp-dirselect>
    <div class="uk-form uk-form-icon uk-width-1-1">
	    <i class="uk-icon-folder"></i>
	    <input name="dir" bind="dir" type="text"  class="uk-form-width-large" onkeyup="{testdir}">

    </div>
    <script>
        var $this = this
        this.mixin(RiotBindMixin);

        this.dir = null;
        var selectenable =false;
        if(opts.path && opts.path == 'undefined')
            opts.path = null;

        console.log("OPTS",opts);
        if(opts.path){
            this.dir=opts.path;
            testDirApi(this.dir);
        }

        testdir(){

            //test if directory exists..?
            testDirApi(this.dir);
        }

        function testDirApi(path){
            App.callmodule('hugo:isDir',[path]).then(function(data){
                console.log("HUGO DIR",data.result);
                selectenable = data.result ? true : false;
                params={selectable: selectenable, path:path}
                App.$($this.root).trigger('selectionchange',  params );
            } );
        }

    </script>
</cp-dirselect>