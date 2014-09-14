<?php
class dynamo
{
	const REGEX_MARKDOWN_SUFFIX = '~.*\.md$~';

	protected $_srcdir;
	protected $_outdir;
	protected $_tpldir;
	protected $_markdown;

	function loadArticle($path)
	{
		$article = array();
		$text = file_get_contents($this->_srcdir . '/' . $path);
		$text = str_replace("\r\n", "\n", $text);
		$text = str_replace("\r", "\n", $text);
		$lines = explode("\n", $text);
		
		if (isset($lines[0]) && substr($lines[0],0,1)=='%') {
			$article['title'] = trim(substr($lines[0], 1));
			$article['header_title'] = $article['title'];
			array_shift($lines);
		}
		if (isset($lines[0]) && substr($lines[0],0,1)=='%') {
			$article['author'] = trim(substr($lines[0], 1));
			array_shift($lines);
		}

		if (!isset($article['title'])) {
			foreach ($lines as $line) {
				if (strpos($line, '#')===0) {
					$article['title'] = substr($line, 1);
				}
			}
		}
		
		$text = implode("\n", $lines);

		$article['html'] = $this->_markdown->text($text);

		return $article;
	}

	function build()
	{		
		$config = parse_ini_file($this->_srcdir . '/dynamo.ini', true);
		$rewriter = new dynamo_rewriter;
		
		$flist = new brick_filelist;
		$flist->scan($this->_srcdir);
		$flist->exclude('~/dynamo\.ini~');
		foreach ($flist->enumerate() as $path) {
			$outdir = $this->_outdir . '/' .  dirname($path);
			if (!file_exists($outdir)) {
				mkdir($outdir, 0777, true);
			}
			if (preg_match(self::REGEX_MARKDOWN_SUFFIX, $path)) {
				$article = $this->loadArticle($path);
				$article = array_merge($config['template'], $article);
				$html = $this->framework->getView()->show('page', $article);
				$html = $rewriter->rewrite($html, $path);
				$path = dirname($path) . '/' . basename($path, '.' .pathinfo($path, PATHINFO_EXTENSION)) . '.html';
				file_put_contents($this->_outdir . '/' . $path, $html);
			} else {
				copy($this->_srcdir . '/' . $path, $this->_outdir . '/' . $path);
			}
		}
	}
	
	function clean()
	{
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->_outdir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
			$path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
		}
	}

	function run($argv, $cd)
	{
		echo $cd . PHP_EOL;
		$cli = new \Garden\Cli\Cli();
		$cli->format = false;
		$cli->command('build')
			->description('Build all pages.')
			->opt('srcdir','Path to source dir.', false)
			->opt('outdir','Path to output dir.', false)
			->opt('tpldir','Path to template dir.', false);
		
		// TODO
		
		
		$args = $cli->parse($argv);
		var_dump($args);
		$this->_srcdir = $args->srcdir;
		$this->_outdir = $args->outdir;
		$this->_tpldir = $args->tpldir;
		$this->_markdown = new parsedownExtra;
		//$this->_markdown->setBreaksEnabled(true);
		$this->framework->getView()->tplPath= $this->_tpldir;
		switch ($args->act) {
			case 'build':
				$this->build();
				break;
			case 'clean':
				$this->clean();
				break;
			default:
				break;
		}
		echo 'OK';
	}
}