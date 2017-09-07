$(document).ready(function() {
   $('body').removeClass('loading');

    $('.bloc:gt(7)').hide();
   

   $(".voir_plus").click(function() {
   		alert('zzzzz');
   		
	    var di="<div class=\'bloc bloc-contenu\' style=\'background: red;\'><img src=\'\'></img></div>";
	    newItems = $(di).appendTo('.grid');
	  $(".grid").isotope('insert', newItems );
	});
});