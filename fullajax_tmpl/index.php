<?php
/**
 * @version	2015.04.05
 * @package	Add FullAjax Plugin
 * @author  Fedir Zinchuk
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

$contid = JFactory::getApplication()->input->get('plg_fullajax_contid', 'forajax');
?>
<html>
<head>
	<jdoc:include type="head" />
</head>
<body>
	<!-- :ax:<?php echo $contid; ?>:begin: //-->
	<jdoc:include type="message" />
	<jdoc:include type="component" />
	<!-- :ax:<?php echo $contid; ?>:end: //-->
</body>
</html>