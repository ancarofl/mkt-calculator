<?php

namespace App\Service\FileParser;

use Generator;

interface FileParserInterface
{
	public function parse(string $filePath): Generator;
}
