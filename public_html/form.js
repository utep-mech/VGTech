
//------------FORM inside Editor------------------
var formurl = 'form.php', dataType = 'json', debug=0;
function form2obj(id) { var s = '', data = {}, o = $("#"+id).serializeArray();
   for(s in o){ data[o[s]['name']] = o[s]['value'] }
   return data;
}
function obj2form(id,o) { var s = '';
   for(s in o){ if(s !== 'action') $('#'+id+' [name='+s+']').val(o[s]); }
}

function InsideEditorForm(id,msgid) { var formdata = form2obj(id);
	var bodymaindata = $('#bodymain').data();
        $.ajax({
                url: formurl, type: 'post', dataType: dataType,
                data: {'id':id, 'dataType': dataType, 'form':formdata, 'bodymaindata':bodymaindata},
                success: function(d) { var msg = 'Saved'; 
                        if(formdata['action']==='Load') { msg='Loaded'; obj2form(id, d.form); }
			$('#'+msgid).html(msg); 
                        if(debug) $('#'+msgid).html(printstr(d));
                },
                error: function(data) { alert('Error'); }
        });
}

function obj2str(o) {return JSON.stringify(o); }
function printstr(o) { if(typeof o === 'object') return obj2str(o); else return o; }

