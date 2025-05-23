<?php

namespace App\Service;

class MktCalculator
{
	private const R = 8.314; // gas constant (J/mol * K)
	private const DeltaH = 83.14472; // activation energy (kJ/mol)

	private float $sumExp = 0.0;
	private float $sumTimeIntervals = 0.0;

	// Only need these to include the last temperature in the calculation (it won't have an interval since we don't know till it was that temp)
	private int $intervalCount = 0;
	private float $avgTimeInterval = 0.0;

	public function update(float $temperatureCelsius, float $hoursElapsed): void
	{
		$tempKelvin = $temperatureCelsius + 273.15;
		$expTerm = exp(- (self::DeltaH * 1000) / (self::R * $tempKelvin));
		$this->sumExp += $expTerm * $hoursElapsed;
		$this->sumTimeIntervals += $hoursElapsed;

		// Only need these to include the last temperature in the calculation
		$this->intervalCount++;
		$this->avgTimeInterval = $this->sumTimeIntervals / $this->intervalCount;
	}

	public function calculate(): float
	{
		/* TODO: I'm just avoiding 0 division, this case should never happen cus of validation before this service.
		Better way to handle? Better error message? Should I just drop it and trust the validation? 
		if ($this->sumTimeIntervals === 0) {
			throw new RuntimeException('No valid data processed.');
		} */

		$averageExp = $this->sumExp / $this->sumTimeIntervals;

		$mktKelvin = (self::DeltaH * 1000) / (self::R * (-log($averageExp)));

		return $mktKelvin - 273.15;
	}

	// Only need this to include the last temperature in the calculation
	public function getAverageTimeInterval(): float
	{
		return $this->avgTimeInterval;
	}
}
