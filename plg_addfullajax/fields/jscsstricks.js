/**
 * @version	2013.11.23 (1.0)
 * @package Add FullAjax for Joomla!
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
 */
window.addEvent("domready", function() {
 //display configuration depend of selected method
 var selectMethod = document.id("jform_params_positionupd");
 axChangeDependsMethod(selectMethod.get("value"));
 selectMethod.addEvent("change", function(){
  axChangeDependsMethod(this.get("value"));
 });

 //clear selected menu items
 var ignorInput = document.id("jform_params_menu_items_ignor");
 var axNoInput = document.id("jform_params_menu_items_no_ax_load");
 if(ignorInput && axNoInput){
   var btClear = new Element("button",{
    html: "Clear",
    type: "button",
    events: { click : axDeselectSelectedItems }
   });
  ignorInput.getParent("li").adopt(btClear);
  axNoInput.getParent("li").adopt(btClear.clone().cloneEvents(btClear));
 }

 //toggle the check active item configuration
 var checkmenuit = document.id("jform_params_checkmenuit");
 axToggleCheckActiv(checkmenuit.getElement("input:checked").get("value"));
 checkmenuit.addEvent("click", function(){
  axToggleCheckActiv(this.getElement("input:checked").get("value"));
 });

});
function axDeselectSelectedItems(e){
 var sel = e.target.getSiblings("select")[0];
 sel.getElements("option[selected=\"selected\"]").removeProperty("selected");
}
function axToggleCheckActiv(checked){
 //hide all
 var checkmenuitA = document.id("jform_params_checkmenuit_active_for_a").getParent("li").slide("out");
 var menuClass = document.id("jform_params_menuClass").getParent("li").slide("out");
 if (checked == "1") {
  checkmenuitA.slide("in");
  menuClass.slide("in");
 }
}
function axChangeDependsMethod(id){
 //hide all
 var contid = document.id("jform_params_contid").getParent("li").slide("out");
 var posParams = document.id("jform_params_posParams").getParent("li").slide("out");
 var allowed_positions = document.id("jform_params_allowed_positions").getParent("li").slide("out");

 switch(id) {
   case "3":
    allowed_positions.slide("in");
    break;
   case "1":
    contid.slide("in");
    posParams.slide("in");
    break;
   case "2":
   case "0":
   default:
    contid.slide("in");
 }
}