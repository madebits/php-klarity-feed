<?php

/**
Klarity RSS1+2/ATOM PHP Script

(c) 2008 Vasian Cepa
*/
class Klarity_Class
{
	public $linkTarget = "_blank"; // "_self";
	public $showFeedHead = true;
	public $showFeedHeadTitle = true;
	public $showFeedHeadImage = true;
	public $showFeedHeadLinks = true;
	public $showFeedHeadDescription = true;
	public $showDates = true;
	public $showPowered = true;
	public $userAgent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 1.0.3705; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)";
	public $showFileMimeCheck = true;
	public $oldFix = false; // a bug in 5.0.4 requires this to true, in php 5.2.5 it seems to be fixed, I do not know the exact version this bug is fixed
	public $version = "0.0.1";
	public $debugMode = false;
	public $debugModePrintType = false;

	/////////////////////////////////////////////////////////////////

	private function isValid($node)
	{
		if(!isset($node)) return false;
		if(count($node) == 0) return false;
		if(empty($node)) return false;
		if($this->oldFix)
		{
			if(is_string($node)) return true;
			if((count($node->attributes) == 0) && (count($node->children) == 0)) return false;
		}
		return true;
	}

	private function isValidStr($node)
	{
		if(!$this->isValid((string)$node)) return false;
		if(strcmp((string)$node, "") == 0) return false;
		return true;
	}

	private function formatData($data)
	{
		if(!$this->isValidStr($data)) return "";
		return str_replace("<a ", "<a class='klarity_link' target='" . $this->linkTarget . "' ", trim($data));
	}

	private function formatLink($aclass, $href, $text, $asLink)
	{
		if(!$this->isValidStr($text)) return "";
		if(($asLink == false) || !$this->isValidStr($href))
		{
			return $text;
		}
		$data = "<a class='" . $aclass . "' target='" . $this->linkTarget . "' href='" . trim($href) . "'>" . trim($text) . "</a>";
		return $data;
	}

	private function getHeadImage($imgSrc, $link, $w, $h)
	{
		$imageData = "";
		if($this->isValidStr($imgSrc))
		{
			$imageData = "<img class='klarity_head_image_img' src='" . $imgSrc . "' border='0' " . $w . " " . $h . " >";
			if($this->showFeedHeadLinks && $this->isValidStr($link))
			{
				$imageData = $this->formatLink('klarity_head_image_link', $link, $imageData, $this->showFeedHeadLinks);
			}
			$imageData = "<span class='klarity_head_image_span'>" . $imageData . "</span>\n";
		}
		return $imageData;
	}

	private function rssGetImage($channel)
	{
		$imageData = "";
		if($this->isValid($channel->image) and $this->isValidStr($channel->image->url))
		{
			$w = "";
			$h = "";
			if($this->isValid($channel->image->width))
			{
				$w = "width='" . $channel->image->width . "'";
			}
			if($this->isValid($channel->image->height))
			{
				$w = "height='" . $channel->image->height . "'";
			}
			$imageData = $this->getHeadImage($channel->image->url, $channel->image->link, $w, $h);
		}
		return $imageData;
	}

	private function atomGetImage($atomData)
	{
		$imageData = "";
		if($this->isValid($atomData->logo))
		{
			$imageData = $this->getHeadImage($atomData->logo, "", "", "");

		}
		return $imageData;
	}

	private function getTitle($channel)
	{
		$title = "";
		if($this->showFeedHeadTitle == false) return $title;
		if($this->isValidStr($channel->title))
		{
			$title = $channel->title;
		}
		if($this->isValidStr($channel->link))
		{
			if(!$this->isValidStr($title))
			{
				$title = $channel->link;
			}
			$title = $this->formatLink('klarity_head_title_link', $channel->link, $title, $this->showFeedHeadLinks);
		}
		if($this->isValid($title))
		{
			$title = "<span class='klarity_head_title_span'>" . trim($title) . "</span>\n";
		}
		return $title;
	}

	private function showHead($title, $img, $subtitle)
	{
		if($this->showFeedHead == false) return;
		if($this->isValidStr($title) || $this->isValidStr($img) || $this->isValidStr($subtitle))
		{
			echo("<div class='klarity_head'>\n");
			echo($img . " " . $title);
			if($this->showFeedHeadDescription && $this->isValidStr($subtitle))
			{
				echo("<div class='klarity_head_subtitle'>\n");
				echo($subtitle);
				echo("</div> <!-- klarity_subtitle -->\n");
			}
			echo("</div><!-- klarity_head -->\n");
		}
	}

	private function rssShowItem($item)
	{
		if(!$this->isValid($item)) return;

		$description = "";
		if($this->isValidStr($item->description))
		{
			$description = $item->description;
		}
		else
		{
			$content = $item->children("http://purl.org/rss/1.0/modules/content/");
			if($this->isValidStr($content->encoded))
			{
				$description = $content->encoded;
			}
		}

		$this->showItem($item->title, $item->link, $description, $item->pubDate);
	}

	private function atomShowItem($item)
	{
		if(!$this->isValid($item)) return;
		$link = "";
		if($this->isValid($item->link))
		{
			// get first link only
			$attrib = $item->link->attributes();
			if($this->isValid($attrib))
			{
				$link = $attrib["href"];
			}
		}
		$description = "";
		if($this->isValid($item->content))
		{
			$description = $item->content;
		}
		else
		{
			$description = $item->summary;
		}
		$date = "";
		if($this->isValid($item->updated))
		{
			$date = $item->updated;
		}
		else if($this->isValid($item->published))
		{
			$date = $item->published;
		}
		$this->showItem($item->title, $link, $description, $date);
	}

	private function showItem($title, $link, $description, $date)
	{
		if(!$this->isValidStr($title))
		{
			$title = $link;
			if(!$this->isValidStr($title))
			{
				return;
			}
		}
		echo("<div class='klarity_item'>\n");
		echo("<div class='klarity_item_title'>" . $this->formatLink('klarity_item_link', $link, $title, true) . "</div><!-- klarity_item_title -->\n");
		if($this->isValidStr($description))
		{
			echo("<div class='klarity_item_description'>\n");
			echo($this->formatData($description));
			echo("</div><!-- klarity_item_description -->\n");
			if($this->showDates && $this->isValidStr($date))
			{
				echo("<div class='klarity_item_date'>" . $this->formatData($date) . "</div><!-- klarity_item_date -->\n");
			}
		}
		echo("</div><!-- klarity_item -->\n");
	}

	private function findFileInfo($meta)
	{
		foreach($meta as $val)
		{
			if(is_array($val))
			{
				$temp = $this->findFileInfo($val);
				if(!empty($temp))
				{
					return $temp;
				}
			}
			else
			{
				if(stripos($val, "Content-Type:")  !== false)
				{
					return $val;
				}
			}
		}
		return "";
	}

	private function isFileSafe($file)
	{
		$fp = fopen($file, "r");
		if(!$fp) return;
		$meta = stream_get_meta_data($fp);
		fclose($fp);
		$result = $this->findFileInfo($meta);
		if($this->debugMode)
		{
			echo($result);
		}
		if(
			(stripos($result, "/xml") !== false)
			||
			(stripos($result, "+xml") !== false)
			||
			(stripos($result, "text/html") !== false)
			)
		{
			return true;
		}
		return false;
	}

	public function showFile($file)
	{
		if($this->isValidStr($this->userAgent))
		{
			@ini_set('user_agent', $this->userAgent);
		}

		if($this->showFileMimeCheck)
		{
			if(!$this->isFileSafe($file))
			{
				if($this->debugMode)
				{
					echo("not-safe");
				}
				return;
			}
		}

		$data = file_get_contents($file);
		$this->showString($data);
	}

	public function showString($data)
	{
		if($this->debugMode)
		{
			echo("<xmp>"); echo($data); echo("</xmp>");
		}
		if(!$this->isValidStr($data) || !function_exists('simplexml_load_string'))
		{
			return;
		}

		// workaround for & bug, have to find some better solution for this
		$data = str_replace("& ", "&amp; ", $data);

		$sxml = false;
		if(phpversion() < "5.1.0")
		{
			$sxml = simplexml_load_string($data);
		}
		else
		{
			$sxml = simplexml_load_string($data, null, LIBXML_NOCDATA | LIBXML_NOENT );
		}

		if($this->isValid($sxml) && $sxml)
		{
			if($this->debugMode)
			{
				echo("<xmp>"); var_dump($sxml); echo("</xmp>");
			}
			echo("<!--  klarity start -->\n");
			echo("<div class='klarity_feed'>\n");

			// (rude) automatic detection
			// ATOM has entries, RSS not
			$isATOM = $this->isValid($sxml->entry);
			$isRSS = (!$isATOM && $this->isValid($sxml->channel));
			// in RSS1.0 items are outside of channel
			$isRSS10 = ($isRSS && $this->isValid($sxml->item));

			if($this->debugModePrintType)
			{
				echo("<br>Type: ");
				if($isRSS)
				{
					if($isRSS10) echo("RSS1");
					else echo("RSS2");
				}
				else if($isATOM)
				{
					echo("ATOM");
				}
				else
				{
					echo("?");
				}
			}

			if(!$isRSS && !$isATOM)
			{
				return;
			}

			if($isRSS)
			{
				foreach($sxml->channel as $channel)
				{
					if($this->showFeedHead == true)
					{
						$image = "";
						if($this->showFeedHeadImage == true)
						{
							if($isRSS10)
							{
								$image = $this->rssGetImage($sxml);
							}
							else
							{
								$image = $this->rssGetImage($channel);
							}
						}
						$this->showHead($this->getTitle($channel), $image, $channel->description);
					}
					echo("<div class='klarity_content'>\n");
					if($isRSS10)
					{
						foreach($sxml->item as $item)
						{
							$this->rssShowItem($item);
						}
					}
					else
					{
						foreach($sxml->channel->item as $item)
						{
							$this->rssShowItem($item);
						}
					}
					echo("</div><!-- klarity_content -->\n");
				}
			}

			if($isATOM)
			{
				if($this->showFeedHead == true)
				{
					$this->showHead($this->getTitle($sxml), $this->atomGetImage($sxml), $sxml->subtitle);
				}
				echo("<div class='klarity_content'>\n");
				foreach($sxml->entry as $item)
				{
					$this->atomShowItem($item);
				}
				echo("</div><!-- klarity_content -->\n");
			}
			if($this->showPowered)
			{
				echo("<div class='klarity_powered'>Powered by <a href='/klarity/index.php' target='" . $this->linkTarget . "'>Klarity</a> RSS1+2/ATOM</div>\n");
			}
			echo("</div><!-- klarity_feed -->\n");
			echo("<!--  klarity end -->\n");
		}
	}
}

?>