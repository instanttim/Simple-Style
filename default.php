<?php print('<?xml version="1.0" encoding="UTF-8"?>'."\n"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

	<head>
		<title>Simple Style</title>
		<style>
			body { 
				font-size: 12px;
			}
			
			code {
				font-size: 14px;
			}
			
			table {
				font-size: 12px;
			}

			td {
				vertical-align: top;
			}
			
			td.label {
				text-align: right;
				vertical-align: top;
				font-weight: bold;
			}

			.preview {
				font-family: georgia;
				font-size: 16px;
			}
		</style>
	</head>
	
	<body>
		<p>
			"Simple Style" makes it easier to create HTML-ready text that include links, text styles, and HTML entities.<br/>
			See below for a key.<br/>
		</p>
		<p>
			<form id="myform3" method="post" accept-charset="utf-8">
				Enter text (or see a <a href="?sample">sample</a>):<br/>
<?php
	ini_set("display_errors", 1);
	error_reporting(E_ALL);

	if (isset($_GET['sample'])) {
		// get contents of a file into a string
		$filename = "sample.txt";
		$handle = fopen($filename, "r");
		$text = fread($handle, filesize($filename));
		fclose($handle);
	}
	if (isset($_POST['textfield'])) {
		$text = $_POST['textfield'];
	}
		
	if (isset($text)) {
		print('<textarea name="textfield" rows="10" cols="60">'."\n");
		print($text."\n");
		print('</textarea>'."\n");
	} else {
		print('<textarea name="textfield" rows="10" cols="60"></textarea>'."\n");
	}
?>
				<br/>
				<input type="submit" />
			</form>
		</p>
		<p>
			HTML-ready text:<br/>
			<textarea name="textfield2" rows="10" cols="60">
<?php 
	if (isset($text)) {
		require("SimpleStyle.class.php");

		$simpletext =& new SimpleStyle($text);
		//$simpletext->sanitize();
		//$simpletext->htmlize();
		//$simpletext->stylize();
		//$simpletext->specialize();
		$simpletext->simplify();
		
		// to print it "raw"
		print(htmlentities($simpletext->getResult(),ENT_QUOTES,"UTF-8")."\n");
	}
?>
			</textarea>
		</p>
		<p>
			Preview:
		</p>
		<div class="preview">
<?php
	if (isset($simpletext)) {
		print($simpletext->getResult());
	}
?>
		</div>
		<p>

			<table width="640" cellspacing="6" border="0">
				<th colspan="2" align="left" bgcolor="silver">Styling Syntax</th>
				<th colspan="2" align="left" bgcolor="silver">Automatic Coolness</th>
				<tr>
					<td class="label">Styling:</td>
					<td class="value">
						*italics*<br/>
						**bold**<br/>
						_underline_
					</td>
					<td class="label">Quotes:</td>
					<td width="300">Double and single quotations will be converted where appropriate (contractions, ommisions, abbreviations, etc.)</td>
				</tr>
				<tr>
					<td class="label">Lists:</td>
					<td>
						Start each list item with...<br/>
						- or * for an bullet list<br/>
						# for a numbered list<br/>
					</td>
					<td class="label">Dashes:</td>
					<td>
						-- for an em dash<br/>
						hyphens will be converted to en dashes where appropriate.
					</td>
				</tr>
				<tr>
					<td class="label">Acronyms:</td>
					<td>ABC(Absolutely Beautiful Code)</td>
					<td class="label">Fractions:</td>
					<td>Simple vulgar fractions such as 1/2, 1/3, 1/4, etc will be converted to the appropriate character. Non-typical fractions will be super- and sub-scripted to simulate the proper presentation.</td>
				</tr>
				<tr>
					<td class="label">Link:</td>
					<td>
						"link title":url<br/>
						title:url<br/>
						url<br/>
					</td>
					<td class="label">Ordinals:</td>
					<td>Dates and other ordinals with the typical english endings (st, nd, rd, th) will be superscripted.</td>
				</tr>
				<tr>
					<td class="label">Images:</td>
					<td>
						["image title":WxH:url]<br/>
						[title:WxH:url]<br/>
						[title:url]<br/>
						[WxH:url]<br/>
						[url]<br/>
					</td>
				</tr>
			</table>
		</p>
	</body>

</html>