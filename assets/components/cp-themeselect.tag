<cp-themeselect>
    <div class="uk-form uk-form-icon uk-width-1-1">
	    <i class="uk-icon-arrow"></i>
	    <select name="theme" bind="theme" class="uk-form-width-large" onchange="{settheme}">
	        <option value="">{opts.alert}</option>
	        <option value="{name}" each="{ name in themes}" selected={name==themeName}>{ name }</option>
	    </select>

    </div>
    <script>
        var $this = this;
        this.mixin(RiotBindMixin);
        this.theme = this.parent.themeName;
        this.on('mount', function() {

            App.callmodule('hugo:getHugoThemes', true).then(function(data) {

                this.themes = data.result;
                console.log("themes:",this.themes);

                this.ready  = true;
                this.themeName=this.parent.themeName;
                console.log('themename2: ',this.themeName);
                this.update();

            }.bind(this));
        });

        settheme(e){
            t = e.target.value;
            console.log("Set theme to "+t,e);
            if(t){
               this.parent.themeName=t;
               this.parent.update();
               App.callmodule('hugo:setHugoSetting',['hugo_theme',t]).then(function(data){
                 console.log("Called setHugoSetting, ",$this);
               });
            }
        }

    </script>
</cp-themeselect>