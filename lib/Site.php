<?php

class Site
{

	private $content;

	private $templatePath;

	public function __construct($content = "")
	{
		$this->content = $content;
		$this->templatePath = $_SERVER['DOCUMENT_ROOT'] . "/lib/template/"; //Template folder path
	}

	public function template1($titolo, $subTitolo)
	{
		$template = file_get_contents($this->templatePath . "Basic/template.html");

		$layoutGrid = file_get_contents($this->templatePath . "Basic/layoutGrid.html");
		$template = str_replace("%layout%", $layoutGrid, $template);

		$template = str_replace("%titolo%", $titolo, $template);
		$template = str_replace("%subTitolo%", $subTitolo, $template);
		$template = str_replace("%content%", $this->content, $template);

		$this->content = $template;
		$this->show();
	}

	public function template2($titolo)
	{
		$template = file_get_contents($this->templatePath . "Basic/template.html");

		$layoutGrid = file_get_contents($this->templatePath . "Basic/layoutFluid.html");
		$template = str_replace("%layout%", $layoutGrid, $template);

		$template = str_replace("%titolo%", $titolo, $template);
		$template = str_replace("%content%", $this->content, $template);

		$this->content = $template;
		$this->show();
	}

	public function card(string $href, $color, $src, $text, $subText)
	{
		$template = file_get_contents($this->templatePath . "CardGrid/card.html");

		$template = str_replace("%href%", $href, $template);
		$template = str_replace("%color%", $color, $template);
		$template = str_replace("%src%", $src, $template);
		$template = str_replace("%text%", $text, $template);
		$template = str_replace("%subText%", $subText, $template);

		$this->content .= $template;
	}

	public function card2($href, $color, $text, $subText)
	{
		$template = file_get_contents($this->templatePath . "CardGrid/card2.html");

		$template = str_replace("%href%", $href, $template);
		$template = str_replace("%color%", $color, $template);
		$template = str_replace("%text%", $text, $template);
		$template = str_replace("%subText%", $subText, $template);

		$this->content .= $template;
	}

	public function line($text="")
	{
		$template = file_get_contents($this->templatePath . "Basic/line.html");
		$template = str_replace("%text%", $text, $template);

		$this->content .= $template;

	}

	public function loginCard()
	{
		$this->content .= file_get_contents($this->templatePath . "CardGrid/loginCard.html");
	}

	private function show()
	{
		echo $this->content;
	}
}
