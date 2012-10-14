<?php
/**
 * @version		index.php  2011-08-06
 * @package		Add FullAjax Plugin
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 */
defined('_JEXEC') or die;
$app = JFactory::getApplication();
?>
<html><head><jdoc:include type="head" /></head>
<body>
<!-- :ax:<?php echo $app->get('fullAjaxContId','forajax'); ?>:begin: //-->
<?php	if ($this->getBuffer('message')) : ?>
<div class="error"><jdoc:include type="message" /></div>
<?php endif; ?>
<jdoc:include type="component" />
<!-- :ax:<?php echo $app->get('fullAjaxContId','forajax'); ?>:end: //-->
</body></html>