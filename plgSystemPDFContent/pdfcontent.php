<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.PDFContent
 *
 * @copyright   Copyright 2012 Root Progress
 * @copyright   Portions of code from Rob Clayburn
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * System plugin to generate PDFs
 *
 * @package     Joomla.Plugin
 * @subpackage  System.PDFContent
 */
class plgSystemPDFContent extends JPlugin
{
	/**
	 * Construct the plugin
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (!$this->iniDomPdf())
		{
			JError::raiseError(500, 'No PDF lib found: download from https://code.google.com/p/dompdf/downloads/list: extract to /libraries/');
		}
	}

	/**
	 * Set the file name for the output pdf
	 */
	public function setName($name = '')
	{
		$this->name = $name;
	}

	/**
	 * Get the name for the ouput pdf file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Run on the joomla onAfterRender trigger
	 */
	public function onAfterRender()
	{
		// Use this plugin only in site application
		if (JFactory::getApplication()->isSite())
		{
			//check if pdf is to be output
			$printpdf = JRequest::getInt('pdf');
			if ($printpdf==1) {

				//get document html from joomla cycle
				$data = JResponse::getBody();

				//convert paths in html to full paths
				$this->fullPaths($data);

				//setup dompdf objects
				$pdf = $this->engine;

				//create pdf using dompdf engine
				$pdf->load_html($data);
				$pdf->set_paper($this->params->get('size','letter'),$this->params->get('orientation','portrait'));
				$pdf->render();
					
				//get and set pdf title to page title
				$this->setName(JFactory::getDocument()->getTitle());
					
				//present pdf to user
				$pdf->stream($this->getName() . '.pdf');
				return '';
			}
		}
	}

	/**
	 * Check dompdf configuration
	 * @copyright Rob Clayburn http://docs.joomla.org/index.php?title=How_to_create_PDF_views&oldid=70693
	 */
	protected function iniDomPdf()
	{
		//check for dompdf configuration file 
		$file = JPATH_LIBRARIES .'/dompdf/dompdf_config.inc.php';
		if (!JFile::exists($file))
		{
			return false;
		}
		if (!defined('DOMPDF_ENABLE_REMOTE'))
		{
			define('DOMPDF_ENABLE_REMOTE', true);
		}
		//set the font cache directory to Joomla's tmp directory
		$config = JFactory::getConfig();
		if (!defined('DOMPDF_FONT_CACHE'))
		{
			define('DOMPDF_FONT_CACHE', $config->get('tmp_path'));
		}
		require_once($file);

		//create new dompdf engine
		$this->engine =new DOMPDF();
		return true;
	}

	/**
	 * Parse relative images a hrefs and style sheets to full paths
	 * @param	string	&$data the joomla generated page html
	 * @copyright Rob Clayburn http://docs.joomla.org/index.php?title=How_to_create_PDF_views&oldid=70693
	 */
	private function fullPaths(&$data)
	{
		$data = str_replace('xmlns=', 'ns=', $data);
		libxml_use_internal_errors(true);
		try
		{
			$ok = new SimpleXMLElement($data);
			if ($ok)
			{
				$uri = JUri::getInstance();
				$base = $uri->getScheme() . '://' . $uri->getHost();
				$imgs = $ok->xpath('//img');
				foreach ($imgs as &$img)
				{
					if (!strstr($img['src'], $base))
					{
						$img['src'] = $base . $img['src'];
					}
				}
				//links
				$as = $ok->xpath('//a');
				foreach ($as as &$a)
				{
					if (!strstr($a['href'], $base))
					{
						$a['href'] = $base . $a['href'];
					}
				}

				// css files.
				$links = $ok->xpath('//link');
				foreach ($links as &$link)
				{
					if ($link['rel'] == 'stylesheet' && !strstr($link['href'], $base))
					{
						$link['href'] = $base . $link['href'];
					}
				}
				$data = $ok->asXML();
			}
		} catch (Exception $err)
		{
			//oho malformed html - if we are debugging the site then show the errors
			// otherwise continue, but it may mean that images/css/links are incorrect
			$errors = libxml_get_errors();
			if (JDEBUG)
			{
				echo "<pre>";print_r($errors);echo "</pre>";
				exit;
			}
		}

	}
}