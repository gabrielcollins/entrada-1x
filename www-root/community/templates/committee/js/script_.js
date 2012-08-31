/* Drop down menu */
	$(document).ready(function () {	
	
	$('nav li').hover(
		function () {
			//show its submenu
			$('ul',this).slideDown(100);
		}, 
		function () {
			//hide its submenu
			$('ul',this).slideUp(100);			
		}
	);
	
	/* Collapse-Expand panels */
	/*$(".right-nav").css("display","none");*/
	$(".toggle-panel").click(function() {
		if ($(".right-nav").hasClass("collapsed")){
			/*$(".right-nav").css("display","block").addClass("expanded").removeClass("collapsed");  "slide",{direction:"left"},1000*/
			$(".content").removeClass("span-18").addClass("span-13");
			$(".right-nav").removeClass("collapsed").show("slide", { direction: "right" }, 200);
		}
		else{
			/*$(".right-nav").css("display","none").addClass("collapsed");
			$(".right-nav").hide("slide", { direction: "right" }, 0).addClass("collapsed");
			$(".right-nav").addClass("collapsed").hide("slide", { direction: "right" }, 400);*/
			$(".right-nav").addClass("collapsed").hide("slide", { direction: "right" }, 200,function(){
			    $(".content").addClass("span-18").removeClass("span-13");
			});
			
		}
		return false;
	});
});






















