<?php
namespace Alphapup\Component\Voltron\Type;

use Alphapup\Component\Voltron\Choices\MonthChoices;
use Alphapup\Component\Voltron\Choices\PaddedChoices;
use Alphapup\Component\Voltron\Type\BaseType;
use Alphapup\Component\Voltron\VoltronBuilder;
use Alphapup\Component\Voltron\VoltronViewInterface;

class Date extends BaseType
{	
	public function configureVoltron(VoltronBuilder $voltronBuilder,array $options=array())
	{
		// setup options
		$monthOptions = array(
			'choices' => new MonthChoices($options['months'],$options['monthFormat']),
			'expanded' => $options['expanded'],
			'multiple' => $options['multiple'],
			'placeholder' => $options['monthPlaceholder'],
		);
		$dayOptions = array(
			'choices' => new PaddedChoices(array_combine($options['days'],$options['days']),2,'0',STR_PAD_LEFT),
			'expanded' => $options['expanded'],
			'multiple' => $options['multiple'],
			'placeholder' => $options['dayPlaceholder'],
		);
		$yearOptions = array(
			'choices' => new PaddedChoices(array_combine($options['years'],$options['years']),4,'0',STR_PAD_LEFT),
			'expanded' => $options['expanded'],
			'multiple' => $options['multiple'],
			'placeholder' => $options['yearPlaceholder'],
		);
		
		$monthOptions['choices']->setSort($options['monthSort']);
		$dayOptions['choices']->setSort($options['daySort']);
		$yearOptions['choices']->setSort($options['yearSort']);
		
		$voltronBuilder
			->add('month','choice',$monthOptions)
			->add('day','choice',$dayOptions)
			->add('year','choice',$yearOptions);
	}

	public function defaultOptions()
	{
		return array(
			
			'expanded' => false,
			'multiple' => false,
			
			'days' => range(1,31),
			'dayPlaceholder' => 'day',
			'daySort' => 'ascendingByValue',
			'months' => range(1,12),
			'monthFormat' => 'M',
			'monthPlaceholder' => 'month',
			'monthSort' => 'ascendingByKey',
			'years' => range(date('Y')-20, date('Y')),
			'yearPlaceholder' => 'year',
			'yearSort' => 'descendingByValue',
		);
	}

	public function name()
	{
		return 'date';
	}
	
	public function parent()
	{
		return 'field';
	}
}