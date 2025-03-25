<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TemperatureDatasetUploadType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('name', TextType::class)
			->add('file', FileType::class, [
				'constraints' => [
					new File([
						'maxSize' => '300M', // An 8000000 entries file is ~204 MB. TODO: Think about this. Upload files in smaller parts? Risk of DDOS?
						'mimeTypes' => ['text/plain', 'text/csv'],
					])
				]
			])
			->add('submit', SubmitType::class, [
				'label' => 'Upload'
			]);
	}
}
