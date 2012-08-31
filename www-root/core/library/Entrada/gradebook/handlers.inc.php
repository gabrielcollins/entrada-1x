<?php

function assessment_handler($assessment) {
	if(isset($assessment["handler"])) {
		$klass = $assessment["handler"] . "GradeHandler";
		return new $klass($assessment);
	}
	return false;
}

function assessment_suffix($assessment) {
	$handler = assessment_handler($assessment);
	return $handler->presentationSuffix(); 
}
function format_input_grade($grade, $assessment) {
	$handler = assessment_handler($assessment);
	return $handler->getFormattedGradeFromInput($grade);
}
function get_storage_grade($grade, $assessment) {
	$handler = assessment_handler($assessment);
	return $handler->getDecimalGrade($grade);
}
function format_retrieved_grade($grade, $assessment) {
	$handler = assessment_handler($assessment);
	return $handler->getFormattedGradeFromDecimal($grade);	
}

abstract class MarkingSchemeHandlerAbstract {
	public $assessment;
	
	function __construct($assessment) {
		$this->assessment = $assessment;
	}
	
	public abstract function getDecimalGrade($input);
	public abstract function getFormattedGradeFromDecimal($decimal);
	
	public function getFormattedGradeFromInput($input) {
 		$input = $this->getDecimalGrade($input);
		return $this->getFormattedGradeFromDecimal($input);
	}
	
	public function stripNumericInput($input, $max = false) {
	 	$input = preg_replace("/(?![0-9\.])/", "", $input);
		if($max === false) {
			$max = 100;
		}
		if($input > $max) {
			$input = $max;
		}
		if($input < 0) {
			$input = 0;
		}
		if(!($input >= 0) && !($input <= $max)) {
			$input = "";
		}
		return $input;
	}
	
	public function presentationSuffix() {
		return "";
	}
}

class PercentageGradeHandler extends MarkingSchemeHandlerAbstract {
	public function getDecimalGrade($input) {
		$input = $this->stripNumericInput($input);
		return $input;
	}
	public function getFormattedGradeFromDecimal($decimal) {
		$decimal = $this->stripNumericInput($decimal);
		return $decimal;
	}
	public function presentationSuffix() {
		return "%";
	}
}

class NumericGradeHandler extends MarkingSchemeHandlerAbstract {	
	public function getDecimalGrade($input) {
		// Strip input according to the max points total (you can only get 20/20, not 21/20)
		if($this->getMaxPoints() != 100) {
			$max = $this->getMaxPoints();
		} else {
			$max = false;
		}
		$input = $this->stripNumericInput($input, $max);
		
		if($input >= 0) {
			return ($input / $this->getMaxPoints()) * 100;
		} else {
			return "";
		}
	}

	public function getFormattedGradeFromDecimal($decimal) {
		if($decimal >= 0) {
			return round(($decimal / 100) * $this->getMaxPoints() * 1000) / 1000;
		} else {
			return "";
		}
	}
	
	public function presentationSuffix() {
		return "|" . $this->getMaxPoints();
	}
	
	private function getMaxPoints() {
		if(isset($this->assessment["numeric_grade_points_total"])) {
			return $this->assessment["numeric_grade_points_total"];
		} else {
			return 100;
		}
	}
}

class BooleanGradeHandler extends MarkingSchemeHandlerAbstract {
	
	public $pass_values = array("p", "pass", "1", 100);
	public $pass_text = "P";
	public $fail_text = "F";
	
	public function getDecimalGrade($input) {
		$input = strtolower($input);
		if(in_array($input, $this->pass_values)) {
			return 100;
		} else {
			return 0;
		}
	} 
	
	public function getFormattedGradeFromDecimal($decimal) {
		if($decimal == 100) {
			return $this->pass_text;
		} else {
			return $this->fail_text;
		}
	}
}

class IncompleteCompleteGradeHandler extends BooleanGradeHandler {
	public $pass_values = array("p", "pass", "1", "c", "complete", 100);
	public $pass_text = "C";
	public $fail_text = "I";
}