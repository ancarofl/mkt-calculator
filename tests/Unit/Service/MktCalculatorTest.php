<?php

namespace Tests\Unit\Service;

use App\Service\MktCalculator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MktCalculatorTest extends TestCase
{
	public function testCalculateMktWithFixedValues(): void
	{
		$calculator = new MktCalculator();

		$calculator->update(15.5, 1);
		$calculator->update(26.1, 1);
		$calculator->update(24.8, 1);

		// https://tech-publish.com/Mean-Kinetic-Temperature-Calculator.html
		$expectedMkt = 23.17;

		$mkt = $calculator->calculate();

		$this->assertEqualsWithDelta($expectedMkt, $mkt, 0.01);
	}

	public function testMktConsistencyWithEqualIntervals(): void
	{
		$calculator1 = new MktCalculator();
		$calculator2 = new MktCalculator();

		// Same values, different intervals, but equal intervals, which means they get reduced
		$calculator1->update(25, 10);
		$calculator1->update(10, 10);
		$calculator1->update(35, 10);
		$calculator1->update(14, 10);
		$calculator1->update(17, 10);
		$calculator1->update(29.22, 10);

		$calculator2->update(25, 0.83);
		$calculator2->update(10, 0.83);
		$calculator2->update(35, 0.83);
		$calculator2->update(14, 0.83);
		$calculator2->update(17, 0.83);
		$calculator2->update(29.22, 0.83);

		$this->assertEqualsWithDelta($calculator1->calculate(), $calculator2->calculate(), 0.01);
	}

	/*
	TODO:
	1. Test with a single temperature value.
	- MKT = temp.

	2. Test with identical temperature values.
	- MKT = temp.

	3. Test with zero time intervals.
	- Exception.

	4. Test with randomized input order.
	- Will fail. Needs solution.

	5 Test invalid data.
	- Missing values
	- Non numeric values
	- Extra columns
	- Empty rows

	6. Test with varying time intervals.
	- I need to calculate the expcted result.
	*/
}
