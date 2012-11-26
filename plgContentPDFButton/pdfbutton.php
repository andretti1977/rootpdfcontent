<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.PDFButton
 *
 * @copyright   Copyright 2012 Evan Fillman
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Content Plugin to Insert a PDF Button
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.PDFButton
 */
class plgContentPDFButton extends JPlugin {

	/**
	 * Construct the plugin
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);	
	}
	
	/**
	 * Fires on content component trigger
	 * 
	 * @param $context from joomla!
	 * @param $article from joomla!
	 * @param $params from joomla!
	 * @param $page from joomla!
	 */
	function onContentPrepare($context, &$article, &$params, $page)
	{
		
		$print = JRequest::getInt('print');		
		$view = JRequest::getCmd('view');
		if($view =='article' && $print != 1){
			$position  = $this->params->get( 'buttonposition',  '1' );
			switch ($position){
				case 1:
					$article->text = $this->pdf_popup($article, $params) . $article->text;
					break;
				case 2:
					$article->text = $article->text . $this->pdf_popup($article, $params);
					break;
				case 3:
					$article->text =  $this->pdf_popup($article, $params) . $article->text . $this->pdf_popup($article, $params);
					break;
			}
		}
	}


	/**
	 * Creates PDF Icon and Link
	 * 
	 * All Parameters directly from trigger function 
	 */
	public function pdf_popup($article, $params, $attribs = array())
	{
		$url  = ContentHelperRoute::getArticleRoute($article->slug, $article->catid);
		$url .= '&tmpl=component&print=1&pdf=1&layout=default&page='.@ $request->limitstart;

		$status = 'width=100,height=100';

		$text = JHtml::_('image', 'system/pdf_button.png', JText::_('JGLOBAL_PRINT'), NULL, true);

		$attribs['title']	= JText::_('JGLOBAL_PRINT');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel']		= 'nofollow';

		return JHtml::_('link', JRoute::_($url), $text, $attribs);
	}
}