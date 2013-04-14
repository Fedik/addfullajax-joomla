/**
 * @version	2012.10.14 (0.9)
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
	 var $checkmenuitA = $("#jform_params_checkmenuit_active_for_a").parents("div.control-group").slideUp();
	 var $menuClass = $("#jform_params_menuClass").parents("div.control-group").slideUp();
	 if (checked == "1") {
	  $checkmenuitA.slideDown();
	  $menuClass.slideDown();
	 }
 }

}(jQuery))


