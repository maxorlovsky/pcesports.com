//Visual things
if (logged_in) {
	$(window).on('hashchange', function() {
		page = window.location.hash;
		TM.showPage(page);
	});
    
    $(document).on('click', 'body', function() {
        TM.runSessionTimeout();
    });
    
    $(document).on('click', '#cmsUpdate', function() {
        TM.updateCMS();
    });
    
    $(document).on('click', '.submitButton', function(){
        if (TM.formInProgress == 1) {
            return false;
        }
        
        TM.formInProgress = 1;
        var oldValue = $(this).html();
        $(this).html('Loading...');

        tinymce.triggerSave();
        
        var element = $(this).parents('table');
        
        form = {};

        //Gathering form data
        element.find('input').each(function() {
            form[$(this).attr('id')] = $(this).val();
        });
        element.find('textarea').each(function() {
            form[$(this).attr('id')] = $(this).val();
        });
        element.find('select').each(function() {
            form[$(this).attr('id')] = $(this).val();
        });
        
        param = window.location.hash.split('/');
        //param[0] = page name
        //param[1] = variable name
        //param[2] = variable value
        if (param[2]) {
            form.param_id = param[2];
        }
        
        TM.showMsg(2,strings.loading);
        var query = {
            type: 'POST',
            timeout: 10000,
            data: {
                control: 'submitForm',
                'module': element.attr('name'),
                'action': element.attr('id'),
                'form': form
            },
            success: function(data) {
                answer = data.split(';');
                TM.cleanMsg();
                TM.showMsg(answer[0],answer[1]);
                $('.submitButton').html(oldValue);
                TM.formInProgress = 0;
                TM.messageTimer = setTimeout(TM.cleanMsg,3000);
                
                if (answer[0] == 1) {
                    TM.showMsg(answer[0],answer[1] + (redirect==1?' ('+strings.will_redirect_auto+')':''));
                    if (redirect == 1) {
                        TM.goDelay('#'+element.attr('name'), 3200);
                    }
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                TM.formInProgress = 0;
                TM.showMsg(0,'Error timeout');
                TM.messageTimer = setTimeout(TM.cleanMsg,3000);
                $('.submitButton').html(oldValue);
            }
        };
        TM.ajax(query);
    });
}

//Setting load block in correct position
if ($('.content').length > 0) {
	var offset = $('.content').offset();
	$('#loading').offset({ top: offset.top, left: offset.left });
}

$(document).on('mousemove', '.hint', function(event) {
    msg = $(this).attr("name");
    $('#hint').offset({ top: event.pageY-30, left: event.pageX+10 });
    if ($('#hint').html != msg) {
        $('#hint').html(msg);
    }
    if (TM.blockHint != 1 && $('#hint').is(':hidden')) {
        $('#hint').show();
    }
}).on('mouseout', '.hint', function(){
	$('#hint').offset({ top: 0, left: 0 });
	$('#hint').hide();
});


$(document).on('click', 'a', function() {
    TM.blockHint = 1;
    $('.hint').trigger('mouseout');
	if ($('#submenu').is(':visible') && $(this).attr('id') != 'site_name_val') {
		$('#submenu').slideUp('fast');
	}
});

$('#menusub').click(function() {
	$(this).find('#arrows').removeClass('active');
	
	if ($(this).find('#submenu').is(':hidden')) {
		$(this).find('#arrows').addClass('active');
	}
	
	$('#submenu').slideToggle('fast');
});

$('#submenu').on('click', function() {
	$('#submenu a').removeClass('active');
	$(this).addClass('active');
});

$('.hint').mouseover(function(event) {
	msg = $(this).attr("name");
	$('#hint').offset({ top: event.pageY-20+window.pageYOffset, left: event.pageX+10 });
	$('#hint').html(msg);
	$('#hint').show();
});
$('.hint').mouseout(function(){
	$('#hint').offset({ top: 0, left: 0 });
	$('#hint').hide();
});

// --------------------------------------------------------------------------------------

var TM = {
    //Globals
    site: site,
    lang: lang,
    blockHint: 0,
    formInProgress: 0,
    messageTimer: 0,
    sessionTimeout: 0,
    editSettingProgress: 0,
    saveSettingId: '',
    saveSettingInput: '',
    
    //Functions
    changeOrder: function(module, ids) {
        TM.showMsg(2,strings.loading);
                
        var query = {
            type: 'POST',
            timeout: 10000,
            data: {
                control: 'saveOrder',
                page: module,
                ids: ids
            },
            success: function(data) {
                answer = data.split(';');
                TM.cleanMsg();
                TM.showMsg(answer[0],answer[1]);
                TM.messageTimer = setTimeout(TM.cleanMsg,3000);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                TM.showMsg(0,'Error timeout');
                TM.messageTimer = setTimeout(TM.cleanMsg,3000);
            }
        };
        TM.ajax(query);
    },
    cancelInput: function(id, t) {
        if (t === 0) {
            $('#'+id).html(this.saveSettingInput);
        }
        else {
            this.saveSettingInput = '';
            $('#'+id).html(t);
        }
        setTimeout(function() {
            TM.editSettingProgress = 0;
            TM.saveSettingId = '';
        },500);
    },
    addInput: function(id, t) {
        if (this.editSettingProgress == 1) {
            if (this.saveSettingId != id) {
                alert('Please cancel the previous action');
            }
            
            return false;
        }
        
        this.saveSettingInput = $('#'+id).html();
        this.saveSettingId = id;
        if (t === 0) {
            html = '<input id="input_setting_text" class="settings_input" type="text" value="'+this.saveSettingInput+'" /> ';
            html += '<span class="recycler" onclick="TM.cancelInput(\''+id+'\', 0);">';
            html += '</span>';
            $('#'+id).html(html);
        }
        else {
            html = '<select id="input_setting_select" class="chosen">';
            for(i=1;i<=4;i++) {
                html += '<option value="'+i+'" '+(TM.saveSettingInput==i?'selected':null)+'>'+i+'</option>';
            }
            html += '</select> ';
            html += '<span class="recycler for_select" onclick="TM.cancelInput(\''+id+'\', 0);">';
            html += '</span>';
            $('#'+id).html(html);
        }
        
        $('#input_setting_text').keypress(function(event) {
            if (event.which == 13) {
                TM.showMsg(2,strings.loading);
                
                var query = {
                    type: 'POST',
                    timeout: 10000,
                    data: {
                        control: 'saveSetting',
                        param: id,
                        value: $('#input_setting_text').val()
                    },
                    success: function(data) {
                        answer = data.split(';');
                        TM.cleanMsg();
                        TM.showMsg(answer[0],answer[1]);
                        TM.messageTimer = setTimeout(TM.cleanMsg,3000);
                        TM.cancelInput(id, $('#input_setting_text').val());
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        TM.showMsg(0,'Error timeout');
                        TM.messageTimer = setTimeout(TM.cleanMsg,3000);
                    }
                };
                TM.ajax(query);
            }
        });
        
        $('#input_setting_select').change(function(event) {
            TM.showMsg(2,strings.loading);
            
            var query = {
                type: 'POST',
                timeout: 10000,
                data: {
                    control: 'saveSetting',
                    param: id,
                    value: $('#input_setting_select').val()
                },
                success: function(data) {
                    answer = data.split(';');
                    TM.cleanMsg();
                    TM.showMsg(answer[0],answer[1]);
                    TM.messageTimer = setTimeout(TM.cleanMsg,3000);
                    TM.cancelInput(id, $('#input_setting_select').val());
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    TM.showMsg(0,'Error timeout');
                    TM.messageTimer = setTimeout(TM.cleanMsg,3000);
                }
            };
            TM.ajax(query);
        });
        
        if (t == 1) {
            $('.chosen').chosen({
                disable_search_threshold: 10,
                no_results_text: "Oops, nothing found!",
            });
        }
        
        this.editSettingProgress = 1;
    },
    cleanMsg: function() {
        clearInterval(this.messageTimer);
        $('#asucmsg').slideUp();
        $('#aerrmsg').slideUp();
        $('#amsg').slideUp();
    },
    showMsg: function(t, text) {
        $('#asucmsg, #aerrmsg, #amsg').stop().hide();
        $('#asucmsg, #aerrmsg, #amsg').html('');
        
        t = parseInt(t);
        
        var woffset = $('body')[0].clientWidth;
        
        if (t === 1) {
            $('#asucmsg').html(text);
            $('#asucmsg').css('left',(((woffset/2)-($('#asucmsg').width()/2))+'px'));
            $('#asucmsg').stop().slideDown();
        }
        else if (t === 0) {
            $('#aerrmsg').html(text);
            $('#aerrmsg').css('left',(((woffset/2)-($('#aerrmsg').width()/2))+'px'));
            $('#aerrmsg').stop().slideDown();
        }
        else {
            $('#amsg').html(text);
            $('#amsg').css('left',(((woffset/2)-($('#amsg').width()/2))+'px'));
            $('#amsg').stop().slideDown();
        }
    },
    runSessionTimeout: function() {
        clearInterval(this.sessionTimeout);
        
        this.sessionTimeout = setInterval(function() {
            go(TM.site+'/admin/#aexit');
        }, 1800000); //30 min
    },
    deletion: function(url) {
        if(confirm(strings.sure_to_delete)) {
           location.href = url;
        }
        
        return false;
    },
    showPage: function(page) {
        TM.loadImg();
        
        if (window.location.hash != page) {
            window.location.hash = page;
            return false;
        }
        
        $('textarea').each(function(){
            tinymce.EditorManager.execCommand('mceRemoveEditor', false, $(this).attr('id'));
        });
        
        param = page.split('/');
        //param[0] = page name
        //param[1] = variable name
        //param[2] = variable value
        
        activeLink = param[0].substr(1);

        dataParams = {
            control: 'showPage'
        };
        $.each(param, function(k, v) {
            if (k === 0) {
                dataParams.page = v;
            }
            else {
                dataParams['var'+k] = v;
            }
        });
        
        var query = {
            type: 'POST',
            timeout: 10000,
            data: dataParams,
            success: function(data) {
                if (param[0] == '#aexit') {
                    go(TM.site+'/admin');
                }
                TM.blockHint = 0;
                $('#loading').fadeOut('fast');
                $('nav').find('a').removeClass('active');
                $('.sublinks_menu').find('a').removeClass('active');
                $('#link_'+activeLink).addClass('active');
                $('.content').html(data);
                $('.content textarea:not(.noEditor)').each(function(){
                    tinymce.EditorManager.execCommand('mceAddEditor', false, $(this).attr('id'));
                });
                
                $('.chosen').chosen({
                    disable_search_threshold: 10,
                    no_results_text: "Oops, nothing found!",
                });
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('#loading').fadeOut('fast');
                TM.cleanMsg();
                TM.showMsg(2,'Error timeout');
                setTimeout(TM.cleanMsg,3000);
            }
        };
        TM.ajax(query);
    },
    goDelay: function(url, delay) {
        if (!url) {
            url = '';
        }
        url = this.site+'/admin/'+url;
        setTimeout(function(){ window.location = url; }, delay);
    },
    updateCMS: function() {
        if (!confirm('WARNING: Be sure to backup your files and database before doing any update, it will fully overwrite existing files!')) {
            return false;
        }
        
        if (this.formInProgress == 1) {
            return false;
        }
        
        TM.showMsg(2,'Initializing');
        this.formInProgress = 1;
        
        var query = {
            type: 'POST',
            timeout: 120000,
            data: {
                control: 'updateCMS'
            },
            success: function(data) {
                console.log(data);
                answer = data.split(';');
                TM.cleanMsg();
                TM.showMsg(answer[0],answer[1]);
                TM.formInProgress = 0;
                TM.messageTimer = setTimeout(TM.cleanMsg,15000);
                if (answer[0] == 1) {
                    TM.goDelay('', 15000);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                TM.formInProgress = 0;
                TM.showMsg(0,'Error timeout');
                TM.messageTimer = setTimeout(TM.cleanMsg,3000);
            }
        };
        TM.ajax(query);
    },
    checkCustomAccess: function() {
        if ($('#level').val() === 0) {
            $('.customAccess').show();
        }
        else {
            $('.customAccess').hide();
        }
    },
    loadImg: function() {
        $('#loading').hide();
        
        w = $('.content').width() + 55;
        h = $('.content').height() + 34;
        offset = $('.content').offset();
        loadingImage = $('#loading').find('img');
        
        $('#loading').width(w);
        $('#loading').height(h);
        $('#loading').fadeTo(0,0.7);
        
        if (h < 10) {
            loadingImage.offset({ top: ($('body').height()/2+loadingImage.height()/2), left: (w/2-loadingImage.width()/2) });
        }
        else {
            loadingImage.offset({ top: (h/2+loadingImage.height()/2), left: (w/2-loadingImage.width()/2) });
        }
    },
    go: function(url) {
        if (url) {
            document.location=url;
        }
        else {
            location.reload(true);
        }
    },
    fadeScr: function() {
        $('#fader').fadeTo(0,0.5);
        $('#fader').height($(document).height());
    },
    ajax: function(object) {
        if (!object.url) {
            object.url = this.site;
        }
        if (!object.async) {
            object.async = true;
        }
        if (!object.dataType) {
            object.dataType = '';
        }
        if (!object.success) {
            object.success = function(data) {
                alert(data);
            };
        }
        if (!object.data) {
            object.data = {};
        }
        if (!object.type) {
            object.type = 'GET';
        }
        if (!object.xhrFields) {
            object.xhrFields = { withCredentials: true };
        }
        if (!object.crossDomain) {
            object.crossDomain = true;
        }
        if (!object.cache) {
            object.cache = true;
        }
        if (!object.timeout) {
            object.timeout = 60000;
        }
        if (!object.error) {
            object.error = function(xhr, ajaxOptions, thrownError) {
                console.log(object.url);
                console.log(xhr);
                console.log(ajaxOptions);
                console.log(thrownError);
                //TM.ajax(object);
            };
        }
        
        //Adding language to object.data
        object.data.language = this.lang;
        
        return $.ajax({
            url: object.url,
            type: object.type,
            async: object.async,
            data: object.data,
            dataType: object.dataType,
            xhrFields: object.xhrFields,
            crossDomain: object.crossDomain,
            cache: object.cache,
            timeout: object.timeout,
            success: object.success,
            error: object.error
        });
    }
};

tinymce.init({
	// General options
    selector: 'textarea:not(.noEditor)',
    
    plugins: 'advlist autolink link image lists charmap print preview code textcolor '+(allowUpload?'jbimages':null),
    toolbar: 'code undo redo | styleselect fontsizeselect | forecolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image '+(allowUpload?'jbimages':null),
	
    width: '99%',
    height : 300,
    fontsize_formats: '8px 10px 12px 14px 18px 24px 36px',

	forced_root_block : false,
    resize: true,
    relative_urls: false,
    document_base_url: TM.site+'/web/',
    remove_script_host : false,
    
    external_plugins: {
        'jbimages': TM.site+'/cms/plugins/tinymce-jbimages/plugin.min.js'
    }
});