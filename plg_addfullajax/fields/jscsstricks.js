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

});

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