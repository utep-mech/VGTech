<!DOCTYPE html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<html>
  <body>
    <!-- 1. The <iframe> (and video player) will replace this <div> tag. -->
  <table border=1 width=100%><tr>
  <td width=80%>
    <div id=idYT class=YTVideo><div id="player1"></div> <div id="player"></div>   </div>
    <div id=idQuiz style='display:none;'>
    <span id=YTdesc>Quiz: 1+2 = ?</span>
    <button onclick="$('.YTVideo').show(); $('#idQuiz').hide(); ">Submit</button>
   </div>
  </td><td> Create Questions
      <br><button onclick="LoadYT({'yid':'YTid', 't0id':'YTidSt'}); ">Load</button> 
      <input id=YTid value=jTS5ZmrrzMs></input><input id=YTidSt value=0 size=1></input>
      <div id=AddQ></div>
      <button onclick="AddQ({'id':'AddQ'});">Add Q</button><br/><textarea id=TAinfo></textarea>
  </td>
  </tr></table>


    <script>
      // 2. This code loads the IFrame Player API code asynchronously.
      var tag = document.createElement('script');

      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      // 3. This function creates an <iframe> (and YouTube player)
      //    after the API code downloads.
      var player, player1;
      function onYouTubeIframeAPIReady() {
        player = new YT.Player('player', {height: '390', width: '640', 
           playerVars: { 'autoplay': 0, 'showinfo':0, 'iv_load_policy':3, 'rel':0, 'frameborder':0, 'controls': 0 }   
        });
/*
        player1 = new YT.Player('player1', {height: '390', width: '640',videoId: 'M7lc1UVf-VE',
	  playerVars: { 'autoplay': 1, 'controls': 0 },
          events: {'onReady': onPlayerReady,'onStateChange': onPlayerStateChange}
        });
*/
      }

      // 4. The API will call this function when the video player is ready.
      function onPlayerReady(event) { event.target.playVideo(); }

      // 5. The API calls this function when the player's state changes.
      //    The function indicates that when playing a video (state=1),
      //    the player should play for six seconds and then stop.
      var done = false;
      function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING && !done) {
          setTimeout(stopVideo, 60e3); // 6e3 = 6 second
          done = true;
        }
      }
      function stopVideo() { player.stopVideo(); }
      function pauseVideo() { player.pauseVideo(); /* $('#idYT').hide(); $('#idQuiz').show(); */ }

      var ts=0, stat, myinterval;  
       
      function DONE() {       }
      function AdvanceVideo(dt) { 
	//player.loadVideoById({'videoId': 'bHQqvYy5KYo', 'startSeconds': ts, 'endSeconds': (ts+dt), 'suggestedQuality': 'large'});
	player.loadVideoById({'videoId': 'jTS5ZmrrzMs', 'startSeconds': ts, 'suggestedQuality': 'large', 
             'playerVars': {'showinfo':1, 'frameborder':1, 'controls': 1 }});
        ts = (ts + dt); 
        setTimeout(pauseVideo, dt*1e3); // 6e3 = 6 second
        //myinterval=setInterval(DONE(), 1*1e3);
    }
    var istart = 0, t1, t2, dt, desc; 
    function StartTheTest() { var O = JSON.parse($('#TAinfo').val()); var yid=O.id; 
        if(istart>0) {t1 = t2; if(istart<O.q.length) t2 = O.q[istart]['t']; else t2=player.getDuration(); } else {t1 = O.t0; t2 = O.q[istart]['t'];};  
        if(istart<O.q.length) desc=O.q[istart]['desc']; 
        istart++; dt=t2-t1;  //alert(t1+'_'+t2+'_'+dt);  
	player.loadVideoById({'videoId': yid, 'startSeconds': t1, 'suggestedQuality': 'large' });
        $('#YTPlayButton').hide(); $('#YTPlayButton').text('Continue');
        setTimeout(pauseVideo2, dt*1e3); // 6e3 = 6 second
    }
    function pauseVideo2() { $('#YTdesc').html(desc); $('.YTVideo').hide(); $('#idQuiz').show(); player.pauseVideo();    }

       var YTQ={'q':[]};
       function LoadYT(O) {
         var yid=$('#'+O.yid).val(), t0=$('#'+O.t0id).val(); YTQ.id=yid; YTQ.t0=t0; 
         player.loadVideoById({'videoId': yid, 'startSeconds': t0}); 
       }
       function AddQ(O) { var id=O.id, tc=Math.round(player.getCurrentTime()); 
           var nq={'t':tc, 'desc':'2+2=?'}; 
           YTQ.q.push(nq); 
           $('#'+id).append('<br/>t='+nq.t+'s, <input value='+nq.desc+'></input>'); 
           $('#TAinfo').val(JSON.stringify(YTQ)); 
           //alert(JSON.stringify(YTQ));  
       }
    </script>
  <br/>
  <button id=YTPlayButton class=YTVideo onclick="StartTheTest(); ">Play</button>
  <p/>
  <button onclick="alert(player.getDuration()/60); ">Duration</button>
  <button onclick="player.playVideo(); ">Play</button>
  <button onclick="player.pauseVideo(); ">Pause</button>
  <button onclick="player.stopVideo(); ">Stop</button>
  <button onclick="player.loadVideoById('bHQqvYy5KYo', 5, 'large'); ">Load</button>
  <button onclick="AdvanceVideo(10); ">Advance</button>
  <button onclick="alert(player.getCurrentTime()); ">CurrentTime</button>
  <button onclick="alert(player.getPlayerState()); ">Status</button>
  </body>
</html>
