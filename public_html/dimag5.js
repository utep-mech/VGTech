var opt={}; //, url='dimag5.php';

$( document ).ready(function() {
    localStorage2body();
    TableSorter();
    if( $("#bodylayout").length == 0) $(document.body).append("<span id=bodylayout></span>"); 
    dimag({'LoadPHP':'Layout.php','outputid':'bodylayout'});
    console.log( "layout is loaded!" );
});

function dimag(send) { var outmsg; 
    //$('#top').html('<button onclick="' + "dimag({'Logout':'1'});" + '">Logout</button>'); 
    if(send.hasOwnProperty('GetValID'))  send.val = $("#"+send.GetValID).val();
    send.Edit= $('#AdminEdit').is(':checked')?1:0;
    send.PostedValue = $('#PostedValue').is(':checked')?1:0;
    opt.bodymaindata = $("#bodymain").data();
    //if(send.hasOwnProperty('url'))  url= send.url;
    if(send.hasOwnProperty('GetValClassID'))  {  var ValClass={}; 
	$('.'+send.GetValClassID).each(function(){
          ValClass[$(this).prop('id')] = $(this).prop('value');  
        }); 
        send.ValClass=ValClass; 
    } 
    $('#message').html('Loading');  $('#message').fadeIn('slow');
    $.ajax({url: url, type: 'post', dataType: 'html', cache: false, 
       data: {'send':send,'opt':opt},
       success: function(recv) { outmsg = 'Loaded'; 
         var outputid="top"; if(send.hasOwnProperty('outputid')) outputid=send.outputid; 
         OutputByIdVK({'str':recv, 'id':outputid}); 
       },
       error: function(recv) {outmsg = '<span style="background-color:yellow">Error</span>';   },
       complete: function(recv) { $('#message').html(outmsg); if(outmsg=='Loaded') $('#message').fadeOut('slow'); } 
    });
}
//----------------------------------
function dimag2(send) { var outmsg; 
    dataType = 'html'; 
    if(typeof send.outputid === 'object')  {dataType='json'; if(send.outputid.length<1) return false; }

    if(send.hasOwnProperty('GetValID'))  send.val = $("#"+send.GetValID).val();
    send.Edit= $('#AdminEdit').is(':checked')?1:0;
    send.debug = $('#AdminDebug').is(':checked')?1:0;
    send.PostedValue = $('#PostedValue').is(':checked')?1:0;
    opt.bodymaindata = $("#bodymain").data();
    if(send.hasOwnProperty('GetValClassID'))  {  var ValClass={}; 
	$('.'+send.GetValClassID).each(function(){
          ValClass[$(this).prop('id')] = $(this).prop('value');  
        }); 
        send.ValClass=ValClass; 
    } 
    $('#message').html('Loading');  $('#message').fadeIn('slow');
    $.ajax({url: url, type: 'post', dataType: dataType, cache: false, 
       data: {'send':send,'opt':opt,'attr':{dataType: dataType}},
       success: function(recv) { outmsg = 'Loaded'; 
	if(send.debug) {OutputByIdVK({'str':obj2str(recv), 'id':'middle'});  return; }
        if(typeof send.outputid === 'object')  {
	 for (var i in send.outputid) {
           OutputByIdVK({'str':recv[i], 'id':send.outputid[i]}); 
	 }
	} else { 
          var outputid="middle"; if(send.hasOwnProperty('outputid')) outputid=send.outputid; 
	  OutputByIdVK({'str':recv, 'id':outputid}); 
	}
       },
       error: function(recv) {outmsg = '<span style="background-color:yellow">Error</span>';   },
       complete: function(recv) { $('#message').html(outmsg); if(outmsg=='Loaded') $('#message').fadeOut('slow'); } 
    });
}
//----------------------------------
function OutputByIdVK(obj) { if(obj.id == 'None') return; 
	   if( $("#"+obj.id).length == 0) $(document.body).append("<span id="+obj.id+"></span>"); 
           $("#"+obj.id).html(obj.str); 
}


function localStorage2body() { 
   if(localStorage.getItem('logged')) {  
       var bd = $('#bodymain').data(); 
	bd.logged = localStorage.getItem('logged');
	bd.userid = localStorage.getItem('uid');
	bd.name = localStorage.getItem('name');
	bd.group = localStorage.getItem('group');
	bd.since = localStorage.getItem('since');
   }
}

function id2array(id) { var A = []; $(id).each(function(){ A.push($(this).attr('id')); }); return A;  }

//----------------------------------------
function TableSorter() {
  $('th.sortable').each(function(){
    if( !$(this).children(".arrow").length )
      $(this).append('<span class=arrow data-dirn=0>&diams;</span>');
  });

  $('th.sortable').click(function(){
    $('th.sortable').css('background-color','');  $(this).css('background-color','yellow');
    var table = $(this).parents('table').eq(0), d = $(this).children('.arrow').data();
    if( d.dirn == 1) { d.dirn=-1; $(this).children('.arrow').html('&#9660;'); }
    else { d.dirn = 1; $(this).children('.arrow').html('&#9650;');  }
    var rows = table.find('tr:gt(0)').toArray().sort(TableComparer($(this).index()))
    this.asc = !this.asc;
    if (!this.asc){ rows = rows.reverse(); }
    for (var i = 0; i < rows.length; i++){table.append(rows[i]); }
  });
}
function TableComparer(index) {
    return function(a, b) {
        var valA = $(a).children('td').eq(index).text(), valB = $(b).children('td').eq(index).text();
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    }
}
//----------------------------------------
