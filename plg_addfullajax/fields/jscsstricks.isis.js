/**
 * @version	2013.11.23 (1.0)
 * @package Add FullAjax for Joomla!
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 */

(function($) {
 $(document).ready(function(){
	//display configuration depend of selected method
	var $selectMethod = $("#jform_params_positionupd");
	axChangeDependsMethod($selectMethod.val())
	$selectMethod.bind("change", function(){
		axChangeDependsMethod($(this).val())
	})

	//toggle the check active item configuration
	var $checkmenuit = $("#jform_params_checkmenuit");
	axToggleCheckActiv($checkmenuit.children('input:checked').val());
	$checkmenuit.bind("click", function(){
		axToggleCheckActiv($(this).children('input:checked').val());
	})
 })

 $(window).bind('load', function(){
	// fix codemiror display
	var codeMirors = $('.CodeMirror');
	$('a[href="#attrib-advanced"]').on('shown', function (e) {
		codeMirors.each(function(i, el){
		    el.CodeMirror.refresh();
		});
	});
 })

 function axChangeDependsMethod(id){
	 //hide all
	 var $contid = $("#jform_params_contid").parents("div.control-group").slideUp();
	 var $posParams = $("#jform_params_posParams").parents("div.control-group").slideUp();
	 var $allowed_positions = $("#jform_params_allowed_positions").parents("div.control-group").slideUp();

	 switch(id) {
	   case "3":
	    $allowed_positions.slideDown();
	    break;
	   case "1":
	    $contid.slideDown();
	    $posParams.slideDown();
	    break;
	   case "2":
	   case "0":
	   default:
	    $contid.slideDown();
	 }
 }

 function axToggleCheckActiv(checked){
	 //hide all
	 var $checkmenuitA = $("#jform_params_checkmenuit_active_for_a").parents("div.control-group");
	 var $menuClass = $("#jform_params_menuClass").parents("div.control-group");
	 if (checked == "1") {
	  $checkmenuitA.slideDown();
	  $menuClass.slideDown();
	 } else {
	  $checkmenuitA.slideUp();
      $menuClass.slideUp();
	 }
 }

}(jQuery))


