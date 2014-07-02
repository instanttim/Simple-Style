<?php

//
// Simple Style
// Copyright 2004-2005
// Timothy B. Martin
// Version 1.5.6 (2005/2/28)
//

// Make sure you define the link replacement!
// define("SS_LINK_REPLACEMENT", '(link)');

class SimpleStyle {
	var $text;
	
	function SimpleStyle ($input) {
		$this->text = $input;
	}
	
	function simplify () {
		$this->sanitize();
		$this->htmlize();
		$this->stylize();
		$this->specialize();
	}
	
	function sanitize () {
		
		// remove the extra crap
		$this->text = preg_replace("/\r/", "", $this->text); // remove carriage returns
		$this->text = preg_replace("/(?<=\n)\s*\n/", "\n", $this->text); // remove extra whitespace and duplicate empty lines
	
		// entify
		$this->text = htmlentities($this->text, ENT_QUOTES, "UTF-8");
	}
	
	function htmlize () {
		
		// break the paragraphs and lists apart into an array
		$exploded = explode("\n\n", $this->text);
		$htmltext = "";
		
		// lists and paragraphs
		for($i=0; $i < count($exploded); $i++) {
			if (preg_match("/^\s*[\*#-]\s(.*?)/", $exploded[$i])) {
				// detect a list
				if (preg_match("/^\s*[#]/", $exploded[$i])) {
					$listTag = "ol";
				} else {
					$listTag = "ul";
				}
				$exploded[$i] = preg_replace("/^[\*#-]\s*(.*?)$/m", "\t<li>$1</li>", $exploded[$i]); // add the tab and li tags on each line
				$exploded[$i] = preg_replace("/^(.*?)$/s", "<".$listTag.">\n$1\n</".$listTag.">\n", $exploded[$i]); // wrap with ul tags
			} else {
				// assume everything else is a paragraph
				$exploded[$i] = preg_replace("/^\s*(.*?)$/m", "\t$1<br/>", $exploded[$i]); // add the tab and break tag on each line
				$exploded[$i] = preg_replace("/^(.*?)$/s", "<p>\n$1\n</p>\n", $exploded[$i]); // wrap with p tags
			}
			$htmltext .= $exploded[$i];
		}
	
		// images
		// with dimensions and without
		$htmltext = preg_replace(
			"/\[(?:&quot;)?(.*?)(?:&quot;)?:?([0-9]+)x([0-9]+):?(http:\/\/.*?)\]/",
			"<img width=\"$2\" height=\"$3\" src=\"$4\" alt=\"$1\" />",
			$htmltext);
		$htmltext = preg_replace(
			"/\[(?:&quot;)?(.*?)(?:&quot;)?:?(http:\/\/.*?)\]/",
			"<img src=\"$2\" alt=\"$1\" />",
			$htmltext);
	
		// LINKS
		// quoted named link
		$LINK_END_CHARS = "\s|\.\s|,\s|;\s|:\s|\.<br\/>|<br\/>";
		
		$htmltext = preg_replace(
			"/&quot;(.*?)&quot;:(https?:\/\/[^\s\"]*?)(".$LINK_END_CHARS.")/",
			"<a href=\"$2\">$1</a>$3",
			$htmltext);

		// unquoted named link
		$htmltext = preg_replace(
			"/([^\s]*):(https?:\/\/[^\s\"]*?)(".$LINK_END_CHARS.")/",
			"<a href=\"$2\">$1</a>$3",
			$htmltext);

		

		// unnamed link
		if (defined("SS_LINK_REPLACEMENT")) {
			$htmltext = preg_replace(
				"/(https?:\/\/[^\s\"]*?)(".$LINK_END_CHARS.")/",
				"<a href=\"$1\">".SS_LINK_REPLACEMENT."</a>$2",
				$htmltext);
		} else {
			$htmltext = preg_replace(
				"/(https?:\/\/[^\s\"]*?)(".$LINK_END_CHARS.")/",
				"<a href=\"$1\">$1</a>$2",
				$htmltext);
		}
		$this->text = $htmltext;
	}
	
	function stylize () {		

        // BUG: only underline looks for wordbreaks the other patterns don't work with wordbreaks 
	
		// valid string to be styled matches this rule
		$S_PATTERN = "([^\s]|[^\s].*?[^\s])";
		$S_START = "(?<=\s)";
		$S_END = "(?=\s|\.|,|<)";
		
		// bold #example#
		//$this->text = preg_replace("/(?<!&)(#)".$S_PATTERN."(#)/", "<b>$2</b>", $this->text); // #
	
		// bold **example**
		$this->text = preg_replace("/\B(\*\*)".$S_PATTERN."(\*\*)\B/", "<b>$2</b>", $this->text); // **
		
		// italics *italics*
		$this->text = preg_replace("/\B(\*)".$S_PATTERN."(\*)\B/", "<i>$2</i>", $this->text);
	
		// underline _example_
		$this->text = preg_replace("/\b(_)".$S_PATTERN."(_)\b/", "<u>$2</u>", $this->text);
				
		// strike-through
		// this is from my custom config of dokuwiki:    \B(-)(?!\s)(.*)(?<!\s)(-)\B/
		$this->text = preg_replace("/".$S_START."(-)".$S_PATTERN."(-)".$S_END."/", "<strike>$2</strike>", $this->text);

		// acronyms EG(Exempli Gratia)
		// ACTS ON: Single all caps words followed by parenthetical expression of more than 3 non-")" chars
		$this->text = preg_replace("/([A-Z]+)\(([^\)]{3,}?)\)/", "<acronym title=\"$2\">$1</acronym>", $this->text);
	
	}
	
	function specialize () {
		
		// char replacements for copywrite, trademark and registered
		//
		// (c) (tm) or (r) case-insensitive
		$this->text = preg_replace("/\(c\)/i", "&copy;", $this->text);
		$this->text = preg_replace("/\(tm\)/i", "&trade;", $this->text);
		$this->text = preg_replace("/\(r\)/i", "&reg;", $this->text);
		
		// ordinal replacement
		//
		// look back assert: whitespace
		// 1 or more decimals
		// one of the ordinal strings
		$this->text = preg_replace("/(?<=\s)(\d+)(st|nd|rd|th)/", "$1<sup>$2</sup>", $this->text);
		
		// fractions
		//
		// starts with: whitespace
		$start = "(?<=\s)";
		// ends with: whitespace, comma, or period, <br/> tag
		$end = "(?=\s|,|\.|<br\/>)";
		//
		// 1/4, 1/2, 3/4, or any other numbers being divided.		
		$this->text = preg_replace("/".$start."1\/4".$end."/", "&#188;", $this->text);
		$this->text = preg_replace("/".$start."1\/2".$end."/", "&#189;", $this->text);
		$this->text = preg_replace("/".$start."3\/4".$end."/", "&#190;", $this->text);
		$this->text = preg_replace("/".$start."(\d+)\/(\d+)".$end."/", "<sup>$1</sup>&#8260;<sub>$2</sub>", $this->text);
		
		
		// en dash (number ranges)
		// BUG: need an assertion that works at the start of a line AND/OR whitespace.
		$this->text = preg_replace("/".$start."(\d+)(-)(\d*)".$end."/", "$1&ndash;$3", $this->text);
		
		// em dash
		$this->text = preg_replace("/--/", "&mdash;", $this->text);
	
		// elipsis
		$this->text = preg_replace("/\.\.\./", "&hellip;", $this->text);
		
		// interrobang
		$this->text = preg_replace("/\?!/", "&#8253;", $this->text);
		$this->text = preg_replace("/!\?/", "&#8253;", $this->text);
		
		// double quotes
		$this->text = preg_replace("/(&quot;)([^\d\s].*?[^\d\s])(&quot;)/", "&ldquo;$2&rdquo;", $this->text);
		
		// contractions
		$this->text = preg_replace("/([^\d\s])(&#039;)([^\d\s])/", "$1&rsquo;$3", $this->text);
	
		// single quotations 
		$this->text = preg_replace("/(&#039;)([^\d\s][^&#039;]*?[^\d\s])(&#039;)/", "&lsquo;$2&rsquo;", $this->text);
	
		// year abbreviations
		$this->text = preg_replace("/(\s)(&#039;)(\w)/", "$1&rsquo;$3", $this->text);
		
	}
	
	function getResult () {
		return $this->text;
	}
}

?>