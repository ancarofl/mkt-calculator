<?php

namespace App\Service;

class MktCalculator
{
	private const R = 8.314; // gas constant (J/mol * K)
	private const DeltaH = 83.14472; // activation energy (kJ/mol)

	private float $sumExp = 0.0;
	private int $recordCount = 0;

	public function update(float $temperatureCelsius): void
	{
		$tempKelvin = $temperatureCelsius + 273.15;
		$this->sumExp += exp(- (self::DeltaH * 1000) / (self::R * $tempKelvin));
		$this->recordCount++;
	}

	public function calculate(): float
	{
		if ($this->recordCount === 0) {
			return 0.0;
		}

		$averageExp = $this->sumExp / $this->recordCount;
		$mktKelvin = (self::DeltaH * 1000) / (self::R * (-log($averageExp)));
		return $mktKelvin - 273.15;
	}
}
