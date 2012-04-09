<?php
/**
 * allowing for other modules/helpers to be created to 'extend' the functionality
 * of the available CalcXXXXXX options ;) (ie, Exponentiate, Log, NPower, etc...),
 * thus any/all CalcXXXXXX options/modules would have to implement this class -
 * with at least a formula
 * ------------------------------------------------------------------------------
 */
interface iCalc
{
	/** yes, yes, i know - overboard, but in lieu of using a framework / allowing a forced implementation */
	function applyformula();
}

/***
 * used only for quick validation of the 'first/second' values
 * within the CalcFactory [below]
 * ------------------------------------------------------------------------------
 */
class CalcValidation
{
	/**
	 * @param $val (required!)
	 * @return mixed|string
	 */
	static public function cleanNumbers($val)
	{
		/***
		 * wanted to control this a bit more...
		 * but could use the FILTER_VAR with 'options' in php
		 */
		$d = trim($val);

		/***
 		 * plus, this is forgiving, we could make it stricter, that the
		 * user MUST reinput again (even if they boofed and added a charater
		 * in there, but would rather at least try to do somethign with teh alphanumeric
		 * versus rekicking it back out to them again.
		 */
		$d = ( !is_numeric($d) ) ? preg_replace("/[^\-0-9., ]+/", "", $d) : $d ;

		/** again, offering forgiveness for that xtra space */
		$d = str_replace(array(","," "),"",$d."");

		/** trick to make it float */
		$d = $d + 0;
		
		return $d;
	}

}


/**
 * This will be used as a quick class of extending, any/all modules
 * (ie, Divide, Multiply, etc...) must extend this class.
 * ------------------------------------------------------------------------------
 */
class CalcSetup
{
	protected $first;
	protected $second;
	public $errors;

	function __construct($first=null,$second=null)
	{
		$this->setFirst($first);
		$this->setSecond($second);
	}

	/***
	 * @param $v - input value for first
	 * @return object $this (for chaining)
	 */
	public function setFirst($v)
	{
		$this->first = $v;
		return $this;
	}

	/***
	 * @param $v - input value for second
	 * @return object $this (for chaining)
	 */
	public function setSecond($v)
	{
		$this->second = $v;
		return $this;
	}

	/**
	 * @return float|integer
	 */
	public function getFirst()
	{
		return $this->first;
	}

	/**
	 * @return float|integer
	 */
	public function getSecond()
	{
		return $this->second;
	}

	/***
	 * @return array errors - hoping to be none! ;)
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/***
	 * calcAnswer - handles the calculation / errors together
	 * @return float|string(error)
	 */
	protected function calcAnswer()
	{
		if ( empty($this->errors) )
		{
			$result = $this->applyformula();
		}
		else
		{
			$result = "Sorry, pease try again!";
		}
		return $result;
	}

	/***
	 * @return array containing errors if any and answer (or text if that's the answer)
	 */
	public function getAnswer()
	{
		$arr['errors']=$this->getErrors(); // found in the operational-class
		$arr['answer']=$this->calcAnswer(); // found in the operational-class
		return $arr;
	}


}


class CalcAdd extends CalcSetup implements iCalc
{
	public function __construct($first,$second)
	{
		parent::__construct($first,$second);
	}

	public function applyformula(){
		$a = array( $this->getFirst(), $this->getSecond() );
		return array_sum( $a );
	}

}

/***
 * would've probably just extended the CalcAdd to include an extra parameter
 * to simply add the (-1)*$second value, but (shrug), for completeness, and
 * continuing on the duplicatable path of just modifying the formula, herego i ;)
 */
class CalcSubtract extends CalcSetup implements iCalc
{
	public function __construct($first,$second)
	{
		parent::__construct($first,$second);
	}

	/***
	 * where the 'magic' happens :)
	 * separatoin of the equation from the calcAnswer was just a way to
	 * separate the final calcAnswer()
	 * @return float
	 */
	public function applyformula(){
		return $this->getFirst() - $this->getSecond() ;
	}

}

/***
 * would've probably just extended the CalcAdd to include an extra parameter
 * to simply add the (-1)*$second value, but (shrug), for completeness, and
 * continuing on the duplicatable path of just modifying the formula, herego i ;)
 */
class CalcMultiply extends CalcSetup implements iCalc
{
	public function __construct($first,$second)
	{
		parent::__construct($first,$second);
	}

	public function applyformula(){
		$a = array( $this->getFirst(), $this->getSecond() );
		return array_product( $a );
	}

}

class CalcDivide extends CalcSetup implements iCalc
{

	public function __construct($first,$second)
	{
		parent::__construct($first,$second);
	}

	/***
	 * slight override - handling the denominator for the divisor issue
	 * @param $v - input value for denominator
	 * @return object CalcDiv (for chaining)
	 */
	public function setSecond($v)
	{
		if ( abs($v) == 0 ) // abs to accomodate a slipped by -0 != 0 (???)
		{
			$this->errors[] = "Please use a non-zero (Second Value) denominator";
		}
		$this->second = $v;
		return $this;
	}

	public function applyformula(){
		return $this->getFirst() / $this->getSecond() ;
	}


}

/** do it! */
class CalcFactory
{
	public $first;
	public $second;
	public $operator;
	public $errors;

	function __construct($first=null,$operation=null,$second=null)
	{
		$this->errors = array();
	}

	/** used as numerator in division case */
	public function setFirst($v)
	{
		$vClean = CalcValidation::cleanNumbers($v);
		if ( !is_numeric($vClean) )
			$this->errors[] = "The First Value must be a valid number.";
		$this->first = $vClean;
		return $this;
	}
	public function getFirst()
	{
		return $this->first;
	}

	/** used as denominator in division case */
	public function setSecond($v)
	{
		$vClean = CalcValidation::cleanNumbers($v);
		if ( !is_numeric($vClean) )
			$this->errors[] = "The Second Value must be a valid number.";
		$this->second = $vClean;
		return $this;
	}
	public function getSecond()
	{
		return $this->second;
	}

	public function setOperator($o)
	{
		$o = trim($o);  // i'd love to make sure there's some xss checking (ie, exponentitate, but following will check for that)
		$ohidden = "Calc".ucwords($o);
		if ( !empty($o) && class_exists( $ohidden ) )
		{
			$this->operator = $ohidden;
		}
		else
		{
			$this->errors[] = "Please select an operation from below.";
		}

		return $this;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	/***
	 * getAnswer - public facing call/element while it coagulates getting errors/answer at the same time
	 * @param string $j - allowing for you to get possible ajax/RESTful/easier way of interpretting results
	 * @return array  array("errors"=>array(), "answer"=>string/number/float/etc..)
	 */
	public function getAnswer($j=null)
	{
		$arr = array();

		$arr['errors']=$this->getErrors();
		$arr['answer']="Hmm, something went wrong, please try again. <br/>(message: $e)";

		$err = $this->getErrors();
		if ( empty($err) ){

			/** @var $cl - coagulates the operator (from front end input) to a classified class call - hence the prefix "Calc" */
			$cl = $this->operator;
			try
			{
				// using try/catch to accomodate the entire class call/runtime error possibilities
				$oper = new $cl($this->getFirst(),$this->getSecond());
				$arr = $oper->getAnswer(); // found in the operational-class
			}
			catch (Exception $e)
			{
				$arr['errors']=$this->getErrors();
				$arr['answer']="Hmm, something went wrong, please try again. <br/>(message: ".$e->getMessage().")";
			}

		}

		$arr['input']=array();
		$arr['input']['first'] = $this->getFirst();
		$arr['input']['second'] = $this->getSecond();

		if ( $j=='json' )
		{
			return json_encode($arr);
			exit();
		}
		
		return $arr;
	}


}